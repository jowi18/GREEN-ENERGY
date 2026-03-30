<?php

namespace App\Services;

use App\Models\Vendor;
use App\Models\Admin;
use App\Notifications\VendorApprovedNotification;
use App\Notifications\VendorRejectedNotification;
use App\Notifications\VendorRevisionRequestedNotification;
use Illuminate\Support\Facades\DB;

class VendorApprovalService
{
    /**
     * Move vendor to under_review when admin opens their application.
     */
    public function markUnderReview(Vendor $vendor): void
    {
        if ($vendor->status === 'pending') {
            $vendor->update(['status' => 'under_review']);
        }
    }

    /**
     * Approve the vendor — they can now subscribe.
     */
    public function approve(Vendor $vendor, Admin $admin, ?string $notes = null): void
    {
        DB::transaction(function () use ($vendor, $admin, $notes) {
            $vendor->update([
                'status'      => 'subscription_required',
                'approved_at' => now(),
                'admin_notes' => $notes,
            ]);

            // Accept all pending documents
            $vendor->documents()
                ->where('review_status', 'pending')
                ->update([
                    'review_status' => 'accepted',
                    'reviewed_by'   => $admin->id,
                    'reviewed_at'   => now(),
                ]);

            $vendor->user->notify(new VendorApprovedNotification($vendor));
        });
    }

    /**
     * Reject the vendor application outright.
     */
    public function reject(Vendor $vendor, Admin $admin, string $reason): void
    {
        DB::transaction(function () use ($vendor, $admin, $reason) {
            $vendor->update([
                'status'           => 'rejected',
                'rejection_reason' => $reason,
                'admin_notes'      => $reason,
            ]);

            $vendor->documents()
                ->where('review_status', 'pending')
                ->update([
                    'review_status'  => 'rejected',
                    'reviewer_notes' => $reason,
                    'reviewed_by'    => $admin->id,
                    'reviewed_at'    => now(),
                ]);

            $vendor->user->notify(new VendorRejectedNotification($vendor, $reason));
        });
    }

    /**
     * Request the vendor to re-submit or fix specific documents.
     */
    public function requestRevision(Vendor $vendor, Admin $admin, string $notes, array $documentFeedback = []): void
    {
        DB::transaction(function () use ($vendor, $admin, $notes, $documentFeedback) {
            // Reset to pending so vendor can re-submit
            $vendor->update([
                'status'      => 'pending',
                'admin_notes' => $notes,
            ]);

            // Attach per-document reviewer notes if provided
            foreach ($documentFeedback as $docId => $feedback) {
                $vendor->documents()
                    ->where('id', $docId)
                    ->update([
                        'review_status'  => 'rejected',
                        'reviewer_notes' => $feedback,
                        'reviewed_by'    => $admin->id,
                        'reviewed_at'    => now(),
                    ]);
            }

            $vendor->user->notify(new VendorRevisionRequestedNotification($vendor, $notes));
        });
    }

    /**
     * Suspend an active vendor.
     */
    public function suspend(Vendor $vendor, string $reason): void
    {
        $vendor->update([
            'status'       => 'suspended',
            'suspended_at' => now(),
            'admin_notes'  => $reason,
        ]);
    }

    /**
     * Reactivate a suspended vendor.
     */
    public function reactivate(Vendor $vendor): void
    {
        $vendor->update([
            'status'       => 'active',
            'suspended_at' => null,
        ]);
    }
}
