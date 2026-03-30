<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\Subscription;
use App\Services\VendorApprovalService;
use Illuminate\Http\Request;

class VendorManagementController extends Controller
{
    public function __construct(
        private readonly VendorApprovalService $approvalService
    ) {}

    /**
     * All vendors except pending approval queue.
     */
    public function index(Request $request)
    {
        $query = Vendor::with(['user', 'activeSubscription.plan'])
            ->whereNotIn('status', ['pending', 'under_review']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by name / registration number
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('business_name', 'like', "%{$term}%")
                  ->orWhere('business_registration_number', 'like', "%{$term}%")
                  ->orWhere('owner_first_name', 'like', "%{$term}%")
                  ->orWhere('owner_last_name', 'like', "%{$term}%");
            });
        }

        // Filter by city
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        $vendors = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'active'                => Vendor::where('status', 'active')->count(),
            'suspended'             => Vendor::where('status', 'suspended')->count(),
            'subscription_required' => Vendor::where('status', 'subscription_required')->count(),
            'rejected'              => Vendor::where('status', 'rejected')->count(),
        ];

        return view('admin.vendors.index', compact('vendors', 'stats'));
    }

    /**
     * Show a single vendor's full profile, subscription history, and orders.
     */
    public function show(Vendor $vendor)
    {
        $vendor->load([
            'user',
            'documents',
            'subscriptions.plan',
            'activeSubscription.plan',
            'employees.roles',
        ]);

        $recentOrders = $vendor->orders()
            ->with('customer')
            ->latest()
            ->limit(10)
            ->get();

        $subscriptionHistory = $vendor->subscriptions()
            ->with('plan')
            ->latest()
            ->get();

        return view('admin.vendors.show', compact(
            'vendor',
            'recentOrders',
            'subscriptionHistory'
        ));
    }

    /**
     * Show edit form for vendor profile corrections.
     */
    public function edit(Vendor $vendor)
    {
        return view('admin.vendors.edit', compact('vendor'));
    }

    /**
     * Admin can correct vendor profile fields if needed.
     */
    public function update(Request $request, Vendor $vendor)
    {
        $data = $request->validate([
            'business_name'  => ['required', 'string', 'max:255'],
            'business_phone' => ['required', 'string', 'max:30'],
            'business_email' => ['nullable', 'email', 'max:255'],
            'city'           => ['required', 'string', 'max:100'],
            'province_state' => ['required', 'string', 'max:100'],
            'admin_notes'    => ['nullable', 'string', 'max:2000'],
        ]);

        $vendor->update($data);

        return redirect()
            ->route('admin.vendors.show', $vendor)
            ->with('success', 'Vendor profile updated.');
    }

    /**
     * Soft-delete a vendor (retains data for audit).
     */
    public function destroy(Vendor $vendor)
    {
        // Cancel any active subscription first
        $vendor->subscriptions()
            ->where('status', 'active')
            ->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        $vendor->user->update(['is_active' => false]);
        $vendor->delete();

        return redirect()
            ->route('admin.vendors.index')
            ->with('success', "Vendor '{$vendor->business_name}' has been removed.");
    }

    /**
     * Suspend an active vendor.
     */
    public function suspend(Request $request, Vendor $vendor)
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $this->approvalService->suspend($vendor, $request->reason);

        // Also suspend the user account
        $vendor->user->update(['is_active' => false]);

        return redirect()
            ->route('admin.vendors.show', $vendor)
            ->with('success', "'{$vendor->business_name}' has been suspended.");
    }

    /**
     * Reactivate a suspended vendor.
     */
    public function reactivate(Vendor $vendor)
    {
        // Only reactivate if they still have a valid subscription
        if ($vendor->activeSubscription) {
            $this->approvalService->reactivate($vendor);
            $vendor->user->update(['is_active' => true]);
            $message = "'{$vendor->business_name}' has been reactivated.";
        } else {
            // Approved but needs to re-subscribe
            $vendor->update(['status' => 'subscription_required']);
            $vendor->user->update(['is_active' => true]);
            $message = "'{$vendor->business_name}' account restored — subscription required.";
        }

        return redirect()
            ->route('admin.vendors.show', $vendor)
            ->with('success', $message);
    }

    /**
     * AJAX: DataTables server-side endpoint.
     */
    public function datatable(Request $request)
    {
        $query = Vendor::with('activeSubscription.plan')
            ->whereNotIn('status', ['pending', 'under_review'])
            ->select('vendors.*');

        // Search
        if ($request->filled('search.value')) {
            $term = $request->input('search.value');
            $query->where(function ($q) use ($term) {
                $q->where('business_name', 'like', "%{$term}%")
                  ->orWhere('business_registration_number', 'like', "%{$term}%")
                  ->orWhere('city', 'like', "%{$term}%");
            });
        }

        $total    = Vendor::whereNotIn('status', ['pending', 'under_review'])->count();
        $filtered = $query->count();

        $vendors = $query
            ->offset($request->input('start', 0))
            ->limit($request->input('length', 20))
            ->latest()
            ->get();

        return response()->json([
            'draw'            => (int) $request->draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $vendors->map(fn ($v) => [
                'id'              => $v->id,
                'business_name'   => $v->business_name,
                'city'            => $v->city,
                'status'          => $v->status,
                'status_badge'    => "<span class=\"badge {$v->status_badge_class}\">{$v->status}</span>",
                'subscription'    => $v->activeSubscription?->plan->name ?? '—',
                'registered'      => $v->created_at->format('M d, Y'),
                'actions'         => route('admin.vendors.show', $v),
            ]),
        ]);
    }
}
