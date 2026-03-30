<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\ServiceRequest;
use App\Models\SolarContract;
use App\Models\SolarContractAdjustment;
use App\Models\SolarProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CustomerSolarContractController extends Controller
{
    private function customer()
    {
        return auth()->user()->customer;
    }

    private function authorizeContract(SolarContract $contract): void
    {
        if ($contract->customer_id !== $this->customer()->id) abort(403);
    }

    // ── View contract ─────────────────────────────────────────────────────

    public function show(SolarProject $solarProject, SolarContract $solarContract)
    {
        if ($solarProject->customer_id !== $this->customer()->id) abort(403);
        $this->authorizeContract($solarContract);

        $solarContract->load([
            'vendor', 'quotation.items',
            'adjustments', 'paymentSchedules.records',
        ]);

        // Mark as viewed
        if (!$solarContract->customer_viewed_at) {
            $solarContract->update(['customer_viewed_at' => now()]);
        }

        return view('customer.solar.contract', compact('solarProject', 'solarContract'));
    }

    // ── Request adjustment ────────────────────────────────────────────────

    public function requestAdjustment(Request $request, SolarProject $solarProject,
                                      SolarContract $solarContract)
    {
        if ($solarProject->customer_id !== $this->customer()->id) abort(403);
        $this->authorizeContract($solarContract);

        if (!in_array($solarContract->status, ['sent', 'adjustment_requested'])) {
            return back()->with('error', 'This contract cannot be adjusted at this stage.');
        }

        $request->validate([
            'section' => ['required', 'string', 'max:100'],
            'notes'   => ['required', 'string', 'max:2000'],
        ]);

        SolarContractAdjustment::create([
            'solar_contract_id' => $solarContract->id,
            'requested_by'      => auth()->id(),
            'section'           => $request->section,
            'notes'             => $request->notes,
            'status'            => 'pending',
        ]);

        $solarContract->update(['status' => 'adjustment_requested']);

        return back()->with('success', 'Your adjustment request has been sent to the vendor.');
    }

    // ── Approve contract ──────────────────────────────────────────────────

    public function approve(Request $request, SolarProject $solarProject,
                            SolarContract $solarContract)
    {
        if ($solarProject->customer_id !== $this->customer()->id) abort(403);
        $this->authorizeContract($solarContract);

        if (!in_array($solarContract->status, ['sent'])) {
            return back()->with('error', 'Contract cannot be approved at this stage.');
        }

        $solarContract->update([
            'status'               => 'approved',
            'customer_approved_at' => now(),
        ]);

        $solarProject->addHistoryEntry(
            $solarProject->status,
            'Contract ' . $solarContract->contract_number . ' approved by customer.'
        );

        return back()->with('success', 'Contract approved. The vendor will finalize and sign it.');
    }

    // ── Print invoice / receipt ───────────────────────────────────────────

    public function invoice(SolarProject $solarProject, SolarContract $solarContract)
    {
        if ($solarProject->customer_id !== $this->customer()->id) abort(403);
        $this->authorizeContract($solarContract);

        $solarContract->load([
            'vendor', 'quotation.items', 'paymentSchedules.records',
        ]);

        return view('customer.solar.invoice', compact('solarProject', 'solarContract'));
    }

    // ── Post-turnover review ──────────────────────────────────────────────

    public function reviewCreate(SolarProject $solarProject)
    {
        if ($solarProject->customer_id !== $this->customer()->id) abort(403);

        if (!in_array($solarProject->status, ['project_turnover', 'warranty_support_active'])) {
            return redirect()
                ->route('customer.solar.show', $solarProject)
                ->with('error', 'Reviews can only be submitted after project turnover.');
        }

        // Check if already reviewed
        $existing = Review::where('customer_id', $this->customer()->id)
            ->where('reviewable_type', SolarProject::class)
            ->where('reviewable_id', $solarProject->id)
            ->first();

        if ($existing) {
            return redirect()
                ->route('customer.solar.show', $solarProject)
                ->with('info', 'You have already submitted a review for this project.');
        }

        $vendor = $solarProject->vendor;
        return view('customer.solar.review', compact('solarProject', 'vendor'));
    }

    // ── Store review ──────────────────────────────────────────────────────

    public function reviewStore(Request $request, SolarProject $solarProject)
    {
        if ($solarProject->customer_id !== $this->customer()->id) abort(403);
        $customer = $this->customer();

        $data = $request->validate([
            'rating'          => ['required', 'integer', 'min:1', 'max:5'],
            'title'           => ['nullable', 'string', 'max:150'],
            'body'            => ['nullable', 'string', 'max:2000'],
            'tags'            => ['nullable', 'array'],
            'tags.*'          => ['string', 'max:100'],
            'images'          => ['nullable', 'array', 'max:4'],
            'images.*'        => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            // Service-specific ratings
            'quality_rating'       => ['nullable', 'integer', 'min:1', 'max:5'],
            'timeliness_rating'    => ['nullable', 'integer', 'min:1', 'max:5'],
            'professionalism_rating'=> ['nullable', 'integer', 'min:1', 'max:5'],
            'value_rating'         => ['nullable', 'integer', 'min:1', 'max:5'],
        ]);

        // Store images
        $imagePaths = [];
        foreach ($request->file('images', []) as $img) {
            $imagePaths[] = $img->store("reviews/{$customer->id}", 'public');
        }

        Review::create([
            'customer_id'          => $customer->id,
            'vendor_id'            => $solarProject->vendor_id,
            'reviewable_type'      => SolarProject::class,
            'reviewable_id'        => $solarProject->id,
            'review_type'          => 'service',
            'rating'               => $data['rating'],
            'title'                => $data['title'] ?? null,
            'body'                 => $data['body'] ?? null,
            'predefined_tags'      => $data['tags'] ?? null,
            'images'               => $imagePaths ?: null,
            'is_verified_purchase' => true,
            'is_visible'           => true,
        ]);

        $solarProject->addHistoryEntry(
            $solarProject->status,
            'Customer submitted a ' . $data['rating'] . '-star review.'
        );

        return redirect()
            ->route('customer.solar.show', $solarProject)
            ->with('success', 'Thank you for your review!');
    }
}
