<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Admin;
use Illuminate\Http\Request;

class CustomerManagementController extends Controller
{
    /**
     * All customers with search and filter.
     */
    public function index(Request $request)
    {
        $query = Customer::with('user')->withCount(['orders', 'reviews', 'serviceRequests']);

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'like', "%{$term}%")
                  ->orWhere('last_name', 'like', "%{$term}%")
                  ->orWhere('phone', 'like', "%{$term}%")
                  ->orWhereHas('user', fn ($u) => $u->where('email', 'like', "%{$term}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('verification_status', $request->status);
        }

        $customers = $query->latest()->paginate(25)->withQueryString();

        $stats = [
            'total'      => Customer::count(),
            'verified'   => Customer::where('verification_status', 'verified')->count(),
            'unverified' => Customer::where('verification_status', 'unverified')->count(),
            'suspended'  => Customer::where('verification_status', 'suspended')->count(),
        ];

        return view('admin.customers.index', compact('customers', 'stats'));
    }

    /**
     * View a single customer's profile, order history, and activity.
     */
    public function show(Customer $customer)
    {
        $customer->load('user');

        $orders = $customer->orders()
            ->with('vendor', 'items')
            ->latest()
            ->paginate(10);

        $serviceRequests = $customer->serviceRequests()
            ->with('vendor')
            ->latest()
            ->limit(5)
            ->get();

        $reviews = $customer->reviews()
            ->with('vendor')
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.customers.show', compact(
            'customer',
            'orders',
            'serviceRequests',
            'reviews'
        ));
    }

    /**
     * Verify a customer's identity manually.
     */
    public function verify(Customer $customer)
    {
        /** @var Admin $admin */
        $admin = auth()->user()->admin;

        $customer->update([
            'verification_status' => 'verified',
            'verified_at'         => now(),
            'verified_by'         => $admin->id,
        ]);

        return redirect()
            ->route('admin.customers.show', $customer)
            ->with('success', "{$customer->full_name} has been verified.");
    }

    /**
     * Suspend a customer account.
     */
    public function suspend(Request $request, Customer $customer)
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $customer->update(['verification_status' => 'suspended']);
        $customer->user->update(['is_active' => false]);

        return redirect()
            ->route('admin.customers.show', $customer)
            ->with('success', "{$customer->full_name} has been suspended.");
    }

    /**
     * Restore a suspended customer.
     */
    public function restore(Customer $customer)
    {
        $customer->update(['verification_status' => 'verified']);
        $customer->user->update(['is_active' => true]);

        return redirect()
            ->route('admin.customers.show', $customer)
            ->with('success', "{$customer->full_name}'s account has been restored.");
    }

    /**
     * AJAX: DataTables server-side endpoint.
     */
    public function datatable(Request $request)
    {
        $query = Customer::with('user')
            ->withCount('orders')
            ->select('customers.*');

        if ($request->filled('search.value')) {
            $term = $request->input('search.value');
            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'like', "%{$term}%")
                  ->orWhere('last_name', 'like', "%{$term}%")
                  ->orWhere('phone', 'like', "%{$term}%")
                  ->orWhereHas('user', fn ($u) => $u->where('email', 'like', "%{$term}%"));
            });
        }

        $total    = Customer::count();
        $filtered = $query->count();

        $customers = $query
            ->offset($request->input('start', 0))
            ->limit($request->input('length', 25))
            ->latest()
            ->get();

        return response()->json([
            'draw'            => (int) $request->draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $customers->map(fn ($c) => [
                'id'          => $c->id,
                'name'        => $c->full_name,
                'email'       => $c->user->email,
                'phone'       => $c->phone,
                'city'        => $c->city ?? '—',
                'status'      => $c->verification_status,
                'orders'      => $c->orders_count,
                'registered'  => $c->created_at->format('M d, Y'),
                'actions'     => route('admin.customers.show', $c),
            ]),
        ]);
    }
}
