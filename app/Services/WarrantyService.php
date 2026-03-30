<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\WarrantyRequest;
use App\Models\Customer;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WarrantyService
{
    // ─────────────────────────────────────────────────────────────────
    // ELIGIBILITY
    // ─────────────────────────────────────────────────────────────────

    /**
     * Check if a customer can file a warranty claim for an order item.
     * Returns ['eligible' => bool, 'reason' => string]
     */
    public function checkEligibility(OrderItem $item, Customer $customer): array
    {
        // Must be the customer's item
        if ($item->order->customer_id !== $customer->id) {
            return ['eligible' => false, 'reason' => 'This item does not belong to your orders.'];
        }

        // Order must be completed/delivered
        if (! in_array($item->order->status, ['completed', 'delivered'])) {
            return ['eligible' => false, 'reason' => 'The order must be completed before filing a warranty claim.'];
        }

        // Item must have warranty
        if (! $item->warranty_months) {
            return ['eligible' => false, 'reason' => 'This product does not come with a warranty.'];
        }

        // Check warranty expiry
        if ($item->warranty_expires_at && $item->warranty_expires_at->isPast()) {
            return [
                'eligible' => false,
                'reason'   => "Warranty expired on {$item->warranty_expires_at->format('F d, Y')}.",
            ];
        }

        // No duplicate open claim for same item
        $existing = WarrantyRequest::where('order_item_id', $item->id)
            ->where('customer_id', $customer->id)
            ->whereNotIn('status', ['rejected', 'closed'])
            ->first();

        if ($existing) {
            return [
                'eligible' => false,
                'reason'   => "You already have an open warranty claim for this item (#{$existing->request_number}).",
            ];
        }

        return ['eligible' => true, 'reason' => ''];
    }

    // ─────────────────────────────────────────────────────────────────
    // CREATE CLAIM
    // ─────────────────────────────────────────────────────────────────

    public function createClaim(
        Customer    $customer,
        OrderItem   $item,
        string      $requestType,
        string      $issueDescription,
        ?string     $serialNumber,
        ?string     $customerNotes,
        array       $issueImages = [],
        array       $receiptImages = [],
        array       $productImages = []
    ): WarrantyRequest {
        $eligibility = $this->checkEligibility($item, $customer);

        if (! $eligibility['eligible']) {
            throw new \RuntimeException($eligibility['reason']);
        }

        return DB::transaction(function () use (
            $customer, $item, $requestType, $issueDescription,
            $serialNumber, $customerNotes, $issueImages, $receiptImages, $productImages
        ) {
            $wr = WarrantyRequest::create([
                'customer_id'          => $customer->id,
                'vendor_id'            => $item->order->vendor_id,
                'order_item_id'        => $item->id,
                'product_id'           => $item->product_id,
                'request_type'         => $requestType,
                'status'               => 'submitted',
                'issue_description'    => $issueDescription,
                'serial_number'        => $serialNumber,
                'customer_notes'       => $customerNotes,
                'issue_images'         => $this->storeImages($issueImages, $customer->id, 'issue'),
                'proof_of_purchase_images' => $this->storeImages($receiptImages, $customer->id, 'receipt'),
                'product_images'       => $this->storeImages($productImages, $customer->id, 'product'),
                'warranty_expires_at'  => $item->warranty_expires_at,
                'is_within_warranty'   => ! ($item->warranty_expires_at && $item->warranty_expires_at->isPast()),
            ]);

            $wr->addHistoryEntry('Claim Submitted', "Customer filed a {$wr->request_type_label} claim.");

            return $wr;
        });
    }

    // ─────────────────────────────────────────────────────────────────
    // STATUS TRANSITIONS (Vendor)
    // ─────────────────────────────────────────────────────────────────

    public function transition(
        WarrantyRequest $wr,
        string          $newStatus,
        array           $extra = []
    ): WarrantyRequest {
        if (! $wr->canTransitionTo($newStatus)) {
            throw new \RuntimeException(
                "Cannot move warranty claim from \"{$wr->status}\" to \"{$newStatus}\"."
            );
        }

        return DB::transaction(function () use ($wr, $newStatus, $extra) {
            $updates = array_merge(['status' => $newStatus], $extra);

            if ($newStatus === 'approved') {
                $updates['approved_by'] = auth()->id();
                $updates['approved_at'] = now();
            }

            if ($newStatus === 'completed') {
                $updates['completed_at'] = now();
            }

            $wr->update($updates);

            $actionLabel = WarrantyRequest::STATUS_LABELS[$newStatus] ?? ucfirst($newStatus);
            $notes       = $extra['technician_notes'] ?? $extra['resolution_notes'] ?? $extra['rejection_reason'] ?? '';
            $wr->addHistoryEntry("Status → {$actionLabel}", $notes);

            return $wr->fresh();
        });
    }

    // ─────────────────────────────────────────────────────────────────
    // UPLOAD COMPLETION PROOF
    // ─────────────────────────────────────────────────────────────────

    /**
     * @param  UploadedFile[]  $files
     */
    public function uploadCompletionProof(
        WarrantyRequest $wr,
        array           $files,
        string          $resolutionNotes,
        string          $resolutionType
    ): WarrantyRequest {
        $paths   = $this->storeImages($files, $wr->customer_id, 'completion');
        $existing= $wr->completion_images ?? [];

        $wr->update([
            'completion_images' => array_merge($existing, $paths),
            'resolution_notes'  => $resolutionNotes,
            'resolution_type'   => $resolutionType,
        ]);

        $wr->addHistoryEntry(
            'Completion Proof Uploaded',
            "Resolution: {$resolutionType}. {$resolutionNotes}"
        );

        return $wr->fresh();
    }

    // ─────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────

    private function storeImages(array $files, int $customerId, string $type): array
    {
        $paths = [];
        foreach ($files as $file) {
            if ($file && $file->isValid()) {
                $paths[] = $file->store(
                    "warranty/{$customerId}/{$type}",
                    'public'
                );
            }
        }
        return $paths;
    }

    /**
     * Get all eligible order items (with active warranty) for a customer.
     */
    public function eligibleItemsForCustomer(Customer $customer): \Illuminate\Support\Collection
    {
        return \App\Models\OrderItem::with(['order', 'product'])
            ->whereHas('order', fn ($q) =>
                $q->where('customer_id', $customer->id)
                  ->whereIn('status', ['completed', 'delivered'])
            )
            ->where('warranty_months', '>', 0)
            ->whereNotNull('warranty_starts_at')
            ->get()
            ->filter(fn ($item) =>
                // Include expired warranties — let the system flag them
                $item->warranty_months > 0
            );
    }
}
