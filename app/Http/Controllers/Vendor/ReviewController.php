<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(private readonly ReviewService $reviews) {}

    private function vendor()
    {
        $u = auth()->user();
        return $u->isEmployee() ? $u->employee->vendor : $u->vendor;
    }

    private function authorize(Review $review): void
    {
        if ($review->vendor_id !== $this->vendor()->id) abort(403);
    }

    // ── Review dashboard ──────────────────────────────────────────────────

    public function index(Request $request)
    {
        $vendor = $this->vendor();

        $query = Review::with(['customer.user', 'order', 'serviceRequest'])
            ->forVendor($vendor->id)
            ->visible();

        // Filters
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        if ($request->filled('type')) {
            $query->where('review_type', $request->type);
        }

        if ($request->filled('replied')) {
            if ($request->replied === 'yes') {
                $query->whereNotNull('vendor_reply');
            } else {
                $query->whereNull('vendor_reply');
            }
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('body', 'like', "%{$request->search}%")
                  ->orWhere('title', 'like', "%{$request->search}%")
                  ->orWhereHas('customer', fn ($cq) =>
                      $cq->where('first_name', 'like', "%{$request->search}%")
                         ->orWhere('last_name', 'like', "%{$request->search}%")
                  );
            });
        }

        $reviews = $query->latest()->paginate(15)->withQueryString();

        // Stats
        $stats = [
            'total'         => Review::forVendor($vendor->id)->visible()->count(),
            'average'       => round(Review::forVendor($vendor->id)->visible()->avg('rating') ?? 0, 1),
            'unreplied'     => Review::forVendor($vendor->id)->visible()->whereNull('vendor_reply')->count(),
            'flagged'       => Review::forVendor($vendor->id)->flagged()->count(),
            'this_month'    => Review::forVendor($vendor->id)->visible()
                                ->whereMonth('created_at', now()->month)->count(),
        ];

        $distribution = $this->reviews->ratingDistribution($vendor->id);

        return view('vendor.reviews.index', compact('reviews', 'stats', 'distribution'));
    }

    // ── Reply to a review ─────────────────────────────────────────────────

    public function reply(Request $request, Review $review)
    {
        $this->authorize($review);

        $data = $request->validate([
            'vendor_reply' => ['required', 'string', 'max:1000'],
        ]);

        try {
            $this->reviews->replyToReview($review, $data['vendor_reply'], $this->vendor()->id);

            if ($request->expectsJson()) {
                return response()->json([
                    'success'           => true,
                    'vendor_reply'      => $data['vendor_reply'],
                    'vendor_replied_at' => now()->format('M d, Y'),
                ]);
            }

            return back()->with('success', 'Reply posted.');

        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ── Delete reply ──────────────────────────────────────────────────────

    public function deleteReply(Review $review)
    {
        $this->authorize($review);
        $this->reviews->deleteReply($review, $this->vendor()->id);

        return back()->with('success', 'Reply removed.');
    }

    // ── Flag / unflag ─────────────────────────────────────────────────────

    public function flag(Review $review)
    {
        $this->authorize($review);
        $this->reviews->flagReview($review, $this->vendor()->id);

        $label = $review->fresh()->is_flagged ? 'flagged' : 'unflagged';
        return back()->with('success', "Review {$label}.");
    }
}
