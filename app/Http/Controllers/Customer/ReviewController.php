<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use App\Models\ServiceRequest;
use App\Services\ReviewService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(private readonly ReviewService $reviews) {}

    private function customer() { return auth()->user()->customer; }

    // ── Create review for a completed ORDER ───────────────────────────────

    public function createForOrder(Order $order)
    {
        $customer = $this->customer();

        if (! $this->reviews->canReviewOrder($order, $customer)) {
            return back()->with('error', 'This order cannot be reviewed yet or has already been reviewed.');
        }

        $predefinedTags = ReviewService::predefinedTags();


        return view('customer.reviews.create', [
            'subject'        => $order,
            'subject_type'   => 'order',
            'subject_label'  => "Order #{$order->order_number}",
            'vendor'         => $order->vendor,
            'predefinedTags' => $predefinedTags,
            'backRoute'      => route('customer.orders.show', $order),
        ]);
    }

    public function storeForOrder(Request $request, Order $order)
    {
        $customer = $this->customer();
        $data     = $this->validateReviewRequest($request);

        try {
            $this->reviews->createOrderReview(
                $order, $customer,
                (int) $data['rating'],
                $data['title'] ?? null,
                $data['body'] ?? null,
                $data['tags'] ?? [],
                $request->file('images', []),
            );

            return redirect()
                ->route('customer.orders.show', $order)
                ->with('success', '✅ Thank you! Your review has been submitted.');

        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ── Create review for a completed SERVICE ─────────────────────────────

    public function createForService(ServiceRequest $serviceRequest)
    {
        $customer = $this->customer();

        if (! $this->reviews->canReviewService($serviceRequest, $customer)) {
            return back()->with('error', 'This service cannot be reviewed yet or has already been reviewed.');
        }

        $predefinedTags = ReviewService::predefinedTags();

        return view('customer.reviews.create', [
            'subject'        => $serviceRequest,
            'subject_type'   => 'service',
            'subject_label'  => "Service: {$serviceRequest->service_title}",
            'vendor'         => $serviceRequest->vendor,
            'predefinedTags' => $predefinedTags,
            'backRoute'      => route('customer.services.show', $serviceRequest),
        ]);
    }

    public function storeForService(Request $request, ServiceRequest $serviceRequest)
    {
        $customer = $this->customer();
        $data     = $this->validateReviewRequest($request);

        try {
            $this->reviews->createServiceReview(
                $serviceRequest, $customer,
                (int) $data['rating'],
                $data['title'] ?? null,
                $data['body'] ?? null,
                $data['tags'] ?? [],
                $request->file('images', []),
            );

            return redirect()
                ->route('customer.services.show', $serviceRequest)
                ->with('success', '✅ Thank you! Your review has been submitted.');

        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ── My reviews list ───────────────────────────────────────────────────

    public function index()
    {
        $customer = $this->customer();

        $reviews = Review::with(['vendor'])
            ->where('customer_id', $customer->id)
            ->where('is_visible', true)
            ->latest()
            ->paginate(15);

        return view('customer.reviews.index', compact('reviews'));
    }

    // ── Validation ────────────────────────────────────────────────────────

    private function validateReviewRequest(Request $request): array
    {
        return $request->validate([
            'rating'   => ['required', 'integer', 'min:1', 'max:5'],
            'title'    => ['nullable', 'string', 'max:150'],
            'body'     => ['nullable', 'string', 'max:2000'],
            'tags'     => ['nullable', 'array'],
            'tags.*'   => ['string', 'max:100'],
            'images'   => ['nullable', 'array', 'max:4'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ]);
    }
}
