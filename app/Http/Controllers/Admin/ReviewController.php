<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Vendor;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with(['customer','vendor'])->latest();

        if ($request->filled('rating'))  $query->where('rating', $request->rating);
        if ($request->filled('vendor'))  $query->where('vendor_id', $request->vendor);
        if ($request->filled('flagged') && $request->flagged === '1') $query->flagged();
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('body', 'like', "%{$request->search}%")
                  ->orWhereHas('customer', fn ($cq) =>
                      $cq->where('first_name','like',"%{$request->search}%")
                         ->orWhere('last_name','like',"%{$request->search}%")
                  );
            });
        }

        $reviews = $query->paginate(25)->withQueryString();

        $stats = [
            'total'    => Review::count(),
            'flagged'  => Review::flagged()->count(),
            'average'  => round(Review::avg('rating') ?? 0, 1),
            'this_month' => Review::whereMonth('created_at',now()->month)->count(),
        ];

        $vendors = Vendor::where('status','active')->orderBy('business_name')->get(['id','business_name']);

        return view('admin.reviews.index', compact('reviews','stats','vendors'));
    }

    public function hide(Review $review)
    {
        $review->update(['is_visible' => ! $review->is_visible]);
        $label = $review->is_visible ? 'shown' : 'hidden';
        return back()->with('success', "Review {$label}.");
    }

    public function delete(Review $review)
    {
        $review->delete();
        return back()->with('success', 'Review deleted.');
    }
}
