<?php

namespace App\Services;

use App\Models\ServiceRequest;
use App\Models\ServiceRequestProof;
use App\Models\Vendor;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ServiceManagementService
{
    // Valid forward transitions
    private const TRANSITIONS = [
        'submitted'   => ['reviewing', 'cancelled'],
        'reviewing'   => ['scheduled', 'quoted', 'rejected'],
        'quoted'      => ['quote_accepted', 'quote_rejected', 'cancelled'],
        'quote_accepted' => ['scheduled'],
        'quote_rejected' => ['cancelled'],
        'scheduled'   => ['in_progress', 'cancelled'],
        'in_progress' => ['completed', 'cancelled'],
        'completed'   => [],
        'rejected'    => [],
        'cancelled'   => [],
    ];

    // Statuses customer can act on
    private const CUSTOMER_ACTIONS = [
        'submitted'   => ['cancelled'],
        'quoted'      => ['quote_accepted', 'quote_rejected'],
        'scheduled'   => ['cancelled'],
    ];

    // ──────────────────────────────────────────────────────────────────────
    // BOOKING CREATION
    // ──────────────────────────────────────────────────────────────────────

    public function createBooking(
        Customer $customer,
        Vendor   $vendor,
        array    $data
    ): ServiceRequest {
        return DB::transaction(function () use ($customer, $vendor, $data) {

            $serviceRequest = ServiceRequest::create([
                'customer_id'       => $customer->id,
                'vendor_id'         => $vendor->id,
                'product_id'        => $data['product_id'] ?? null,
                'service_type'      => $data['service_type'],
                'service_title'     => $data['service_title'],
                'description'       => $data['description'],
                'preferred_date'    => $data['preferred_date']    ?? null,
                'preferred_time'    => $data['preferred_time']    ?? null,
                'service_address'   => $data['service_address'],
                'service_city'      => $data['service_city'],
                'service_province'  => $data['service_province'],
                'service_postal'    => $data['service_postal']    ?? null,
                'service_latitude'  => $data['service_latitude']  ?? null,
                'service_longitude' => $data['service_longitude'] ?? null,
                'customer_notes'    => $data['customer_notes']    ?? null,
                'status'            => 'submitted',
            ]);

            Log::info("Service request #{$serviceRequest->request_number} created", [
                'customer_id' => $customer->id,
                'vendor_id'   => $vendor->id,
                'type'        => $data['service_type'],
            ]);

            return $serviceRequest;
        });
    }

    // ──────────────────────────────────────────────────────────────────────
    // STATUS TRANSITIONS
    // ──────────────────────────────────────────────────────────────────────

    public function updateStatus(
        ServiceRequest $sr,
        string         $newStatus,
        array          $extra = [],
        bool           $byVendor = true
    ): ServiceRequest {
        $transitions = $byVendor
            ? self::TRANSITIONS
            : self::CUSTOMER_ACTIONS;

        $allowed = $transitions[$sr->status] ?? [];

        if (! in_array($newStatus, $allowed)) {
            throw new \RuntimeException(
                "Cannot move service request from \"{$sr->status}\" to \"{$newStatus}\"."
            );
        }

        return DB::transaction(function () use ($sr, $newStatus, $extra) {
            // Pull out employee IDs before building the updates array
            $employeeIds = $extra['employee_ids'] ?? [];
            unset($extra['employee_ids']);

            $updates = array_merge(['status' => $newStatus], $extra);

            if ($newStatus === 'scheduled' && ! isset($updates['scheduled_date'])) {
                throw new \RuntimeException('A scheduled date is required when scheduling a service.');
            }

            if ($newStatus === 'in_progress') {
                $updates['started_at'] = now();
            }

            if ($newStatus === 'completed') {
                $updates['completed_at'] = now();
            }

            if ($newStatus === 'cancelled') {
                $updates['cancelled_at'] = now();
            }

            $sr->update($updates);

            // Sync assigned employees when scheduling
            if ($newStatus === 'scheduled' && ! empty($employeeIds)) {
                $pivots = collect($employeeIds)->mapWithKeys(fn ($id) => [
                    $id => [
                        'assigned_at' => now(),
                        'assigned_by' => auth()->id(),
                    ],
                ])->toArray();
                $sr->assignees()->sync($pivots);
            }

            return $sr->fresh()->load('assignees');
        });
    }

    // ──────────────────────────────────────────────────────────────────────
    // QUOTE
    // ──────────────────────────────────────────────────────────────────────

    public function submitQuote(
        ServiceRequest $sr,
        float          $amount,
        string         $details,
        ?string        $validUntil = null
    ): ServiceRequest {
        if (! in_array($sr->status, ['reviewing', 'submitted'])) {
            throw new \RuntimeException('Quote can only be submitted for requests under review.');
        }

        $sr->update([
            'status'            => 'quoted',
            'quoted_amount'     => $amount,
            'quote_details'     => $details,
            'quote_valid_until' => $validUntil,
            'quoted_at'         => now(),
        ]);

        return $sr->fresh();
    }

    // ──────────────────────────────────────────────────────────────────────
    // PROOF OF COMPLETION UPLOAD
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Upload one or more proof images for a completed/in-progress service.
     *
     * @param  UploadedFile[]  $files
     */
    public function uploadProof(
        ServiceRequest $sr,
        array          $files,
        ?string        $caption = null,
        int            $uploadedBy
    ): array {
        if (! in_array($sr->status, ['in_progress', 'completed'])) {
            throw new \RuntimeException('Proof can only be uploaded when the service is in progress or completed.');
        }

        $proofs = [];

        foreach ($files as $file) {
            $path = $file->store("service-proofs/{$sr->vendor_id}/{$sr->id}", 'public');

            $proof = ServiceRequestProof::create([
                'service_request_id' => $sr->id,
                'file_path'          => $path,
                'file_original_name' => $file->getClientOriginalName(),
                'file_mime_type'     => $file->getMimeType(),
                'file_size'          => $file->getSize(),
                'caption'            => $caption,
                'uploaded_by'        => $uploadedBy,
            ]);

            $proofs[] = $proof;
        }

        // Auto-complete if still in_progress and at least one proof uploaded
        if ($sr->status === 'in_progress' && count($proofs) > 0) {
            $sr->update(['status' => 'completed', 'completed_at' => now()]);
        }

        return $proofs;
    }

    /**
     * Delete a proof image.
     */
    public function deleteProof(ServiceRequestProof $proof): void
    {
        Storage::disk('public')->delete($proof->file_path);
        $proof->delete();
    }

    // ──────────────────────────────────────────────────────────────────────
    // HELPERS
    // ──────────────────────────────────────────────────────────────────────

    public function allowedTransitions(string $status, bool $byVendor = true): array
    {
        $map = $byVendor ? self::TRANSITIONS : self::CUSTOMER_ACTIONS;
        return $map[$status] ?? [];
    }

    public function canTransition(string $from, string $to, bool $byVendor = true): bool
    {
        return in_array($to, $this->allowedTransitions($from, $byVendor));
    }

    /**
     * Sync assigned employees for a service request (standalone, outside status change).
     * Useful for updating assignments after scheduling.
     */
    public function syncAssignees(ServiceRequest $sr, array $employeeIds, ?string $role = null): void
    {
        $pivots = collect($employeeIds)->mapWithKeys(fn ($id) => [
            $id => [
                'role'        => $role,
                'assigned_at' => now(),
                'assigned_by' => auth()->id(),
            ],
        ])->toArray();

        $sr->assignees()->sync($pivots);
    }
}
