<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Review;
use App\Models\ServiceRequest;
use App\Models\Customer;
use Illuminate\Support\Facades\Storage;

class ReviewService
{
    // ─────────────────────────────────────────────────────────────────────
    // PREDEFINED TAGS PER STAR RATING
    // ─────────────────────────────────────────────────────────────────────

    public static function predefinedTags(): array
    {
        return [
            5 => [
                'Excellent service!',
                'Very professional',
                'On-time delivery',
                'High quality products',
                'Great value for money',
                'Would recommend',
                'Exceeded expectations',
                'Friendly staff',
                'Clean installation',
                'Fast response',
            ],
            4 => [
                'Good service',
                'Professional team',
                'Quality products',
                'Good communication',
                'Delivered on time',
                'Satisfied customer',
                'Minor improvements needed',
                'Good value',
                'Responsive support',
                'Neat workmanship',
            ],
            3 => [
                'Average experience',
                'Met expectations',
                'Room for improvement',
                'Acceptable quality',
                'Okay service',
                'Could be faster',
                'Average communication',
                'Fair pricing',
                'Needs follow-up',
                'Partially satisfied',
            ],
            2 => [
                'Below expectations',
                'Delayed service',
                'Poor communication',
                'Quality issues',
                'Needs improvement',
                'Not satisfied',
                'Overpriced',
                'Unprofessional',
                'Follow-up required',
                'Incomplete work',
            ],
            1 => [
                'Very poor service',
                'Unresponsive team',
                'Damaged product',
                'Did not deliver',
                'Worst experience',
                'Not recommended',
                'Wasted money',
                'Unprofessional staff',
                'No-show technician',
                'Total disappointment',
            ],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────
    // ELIGIBILITY CHECKS
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Check if a customer can review a completed order.
     */
    public function canReviewOrder(Order $order, Customer $customer): bool
    {
        if ($order->customer_id !== $customer->id) return false;
        if ($order->status !== 'completed') return false;

        return ! Review::where('customer_id', $customer->id)
            ->where('reviewable_type', Order::class)
            ->where('reviewable_id', $order->id)
            ->exists();
    }

    /**
     * Check if a customer can review a completed service request.
     */
    public function canReviewService(ServiceRequest $sr, Customer $customer): bool
    {
        if ($sr->customer_id !== $customer->id) return false;
        if ($sr->status !== 'completed') return false;

        return ! Review::where('customer_id', $customer->id)
            ->where('reviewable_type', ServiceRequest::class)
            ->where('reviewable_id', $sr->id)
            ->exists();
    }

    // ─────────────────────────────────────────────────────────────────────
    // CREATE REVIEW
    // ─────────────────────────────────────────────────────────────────────

    public function createOrderReview(
        Order    $order,
        Customer $customer,
        int      $rating,
        ?string  $title,
        ?string  $body,
        array    $tags = [],
        array    $images = []
    ): Review {
        if (! $this->canReviewOrder($order, $customer)) {
            throw new \RuntimeException('You cannot review this order.');
        }

        $imagePaths = $this->storeImages($images, $customer->id);

        $review = Review::create([
            'customer_id'          => $customer->id,
            'vendor_id'            => $order->vendor_id,
            'reviewable_type'      => Order::class,
            'reviewable_id'        => $order->id,
            'order_id'             => $order->id,
            'review_type'          => 'order',
            'rating'               => $rating,
            'title'                => $title,
            'body'                 => $body,
            'predefined_tags'      => $tags ?: null,
            'images'               => $imagePaths ?: null,
            'is_verified_purchase' => true,
            'is_visible'           => true,
        ]);

        return $review;
    }

    public function createServiceReview(
        ServiceRequest $sr,
        Customer       $customer,
        int            $rating,
        ?string        $title,
        ?string        $body,
        array          $tags = [],
        array          $images = []
    ): Review {
        if (! $this->canReviewService($sr, $customer)) {
            throw new \RuntimeException('You cannot review this service request.');
        }

        $imagePaths = $this->storeImages($images, $customer->id);

        $review = Review::create([
            'customer_id'          => $customer->id,
            'vendor_id'            => $sr->vendor_id,
            'reviewable_type'      => ServiceRequest::class,
            'reviewable_id'        => $sr->id,
            'service_request_id'   => $sr->id,
            'review_type'          => 'service',
            'rating'               => $rating,
            'title'                => $title,
            'body'                 => $body,
            'predefined_tags'      => $tags ?: null,
            'images'               => $imagePaths ?: null,
            'is_verified_purchase' => true,
            'is_visible'           => true,
        ]);

        return $review;
    }

    // ─────────────────────────────────────────────────────────────────────
    // VENDOR REPLY
    // ─────────────────────────────────────────────────────────────────────

    public function replyToReview(Review $review, string $replyText, int $vendorId): Review
    {
        if ($review->vendor_id !== $vendorId) {
            throw new \RuntimeException('Unauthorized.');
        }

        $review->update([
            'vendor_reply'      => $replyText,
            'vendor_replied_at' => now(),
        ]);

        return $review->fresh();
    }

    public function deleteReply(Review $review, int $vendorId): void
    {
        if ($review->vendor_id !== $vendorId) {
            throw new \RuntimeException('Unauthorized.');
        }

        $review->update([
            'vendor_reply'      => null,
            'vendor_replied_at' => null,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // VENDOR MODERATION
    // ─────────────────────────────────────────────────────────────────────

    public function flagReview(Review $review, int $vendorId): void
    {
        if ($review->vendor_id !== $vendorId) return;
        $review->update(['is_flagged' => ! $review->is_flagged]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────

    private function storeImages(array $files, int $customerId): array
    {
        $paths = [];
        foreach ($files as $file) {
            if ($file && $file->isValid()) {
                $paths[] = $file->store("reviews/{$customerId}", 'public');
            }
        }
        return $paths;
    }

    /**
     * Rating distribution for a vendor's reviews.
     */
    public function ratingDistribution(int $vendorId): array
    {
        $total = Review::forVendor($vendorId)->visible()->count();

        $dist = [];
        for ($i = 5; $i >= 1; $i--) {
            $count    = Review::forVendor($vendorId)->visible()->where('rating', $i)->count();
            $dist[$i] = [
                'count'   => $count,
                'percent' => $total > 0 ? round(($count / $total) * 100) : 0,
            ];
        }

        return $dist;
    }
}
