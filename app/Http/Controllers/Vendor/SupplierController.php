<?php

namespace App\Http\Controllers\Vendor;

// ═══════════════════════════════════════════════════════════════════════════
// app/Http/Controllers/Vendor/SupplierController.php
// ═══════════════════════════════════════════════════════════════════════════

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\VendorSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Notifications\SupplierAccountCreatedNotification;

class SupplierController extends Controller
{
    private function vendor()
    {
        return auth()->user()->vendor;
    }

    // ── Index ──────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $suppliers = VendorSupplier::forVendor($this->vendor()->id)
            ->with('portalSupplier')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('company_name', 'like', "%{$request->search}%")
                  ->orWhere('contact_person', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            }))
            ->when($request->has('preferred'), fn($q) => $q->preferred())
            ->orderByDesc('is_preferred')
            ->orderBy('company_name')
            ->paginate(20)
            ->withQueryString();

        // Stats for the header cards
        $stats = [
            'total'     => VendorSupplier::forVendor($this->vendor()->id)->count(),
            'active'    => VendorSupplier::forVendor($this->vendor()->id)->active()->count(),
            'preferred' => VendorSupplier::forVendor($this->vendor()->id)->preferred()->count(),
            'portal'    => VendorSupplier::forVendor($this->vendor()->id)->linkedToPortal()->count(),
        ];

        // Portal suppliers NOT yet in vendor's directory (for the link modal)
        $availablePortalSuppliers = Supplier::where('status', 'active')
            ->whereNotIn('id', VendorSupplier::forVendor($this->vendor()->id)
                ->whereNotNull('supplier_id')
                ->pluck('supplier_id'))
            ->orderBy('company_name')
            ->get(['id', 'company_name', 'contact_person', 'email', 'city']);

        return view('vendor.suppliers.index', compact('suppliers', 'stats', 'availablePortalSuppliers'));
    }

    // ── Create ─────────────────────────────────────────────────────────────

    public function create()
    {

        $availablePortalSuppliers = Supplier::where('status', 'active')
            ->whereNotIn('id', VendorSupplier::forVendor($this->vendor()->id)
                ->whereNotNull('supplier_id')
                ->pluck('supplier_id'))
            ->orderBy('company_name')
            ->get(['id', 'company_name', 'contact_person', 'email', 'city']);

        return view('vendor.suppliers.create', compact('availablePortalSuppliers'));
    }

    // ── Store ──────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id'          => ['nullable', 'exists:suppliers,id'],
            'company_name'         => ['required', 'string', 'max:255'],
            'contact_person'       => ['nullable', 'string', 'max:255'],
            'email'                => ['nullable', 'email', 'max:255'],
            'phone'                => ['nullable', 'string', 'max:50'],
            'address_line1'        => ['nullable', 'string', 'max:500'],
            'city'                 => ['nullable', 'string', 'max:100'],
            'province_state'       => ['nullable', 'string', 'max:100'],
            'postal_code'          => ['nullable', 'string', 'max:20'],
            'country'              => ['nullable', 'string', 'max:100'],
            'account_number'       => ['nullable', 'string', 'max:100'],
            'payment_terms'        => ['nullable', 'in:cod,net7,net15,net30,net60,custom'],
            'payment_terms_custom' => ['nullable', 'string', 'max:255'],
            'credit_limit'         => ['nullable', 'numeric', 'min:0'],
            'is_preferred'         => ['nullable', 'boolean'],
            'status'               => ['nullable', 'in:active,inactive,blocked'],
            'notes'                => ['nullable', 'string', 'max:2000'],
        ]);

        // ── Duplicate email guard ────────────────────────────────────────────
        if (!empty($data['email'])) {
            $exists = VendorSupplier::forVendor($this->vendor()->id)
                ->where('email', $data['email'])
                ->exists();

            if ($exists) {
                return back()->withInput()
                    ->withErrors(['email' => 'A supplier with this email is already in your directory.']);
            }
        }

        $vendorSupplier = DB::transaction(function () use ($data, $request) {

            $plainPassword = null;

            // ── PATH A: Linking an existing portal supplier ──────────────────
            if (!empty($data['supplier_id'])) {
                $portal = Supplier::findOrFail($data['supplier_id']);

                // Auto-fill blank fields from portal record
                $data['company_name']   = $data['company_name']   ?: $portal->company_name;
                $data['contact_person'] = $data['contact_person'] ?: $portal->contact_person;
                $data['email']          = $data['email']          ?: $portal->email;
                $data['phone']          = $data['phone']          ?: $portal->phone;
                $data['city']           = $data['city']           ?: $portal->city;
                $data['province_state'] = $data['province_state'] ?: $portal->province_state;

                // No new User or Supplier record needed — already exists
            }

            // ── PATH B: Manual entry with email → create portal account ─────
            elseif (!empty($data['email'])) {

                // Bail if a User with this email already exists
                $existingUser = User::where('email', $data['email'])->first();

                if ($existingUser) {
                    // If they already have a Supplier profile, just link it
                    if ($existingUser->supplier) {
                        $data['supplier_id'] = $existingUser->supplier->id;
                    }
                    // Otherwise create the Supplier profile under the existing user
                    else {
                        $supplierRecord = Supplier::create([
                            'user_id'        => $existingUser->id,
                            'company_name'   => $data['company_name'],
                            'contact_person' => $data['contact_person'] ?? '',
                            'email'          => $data['email'],
                            'phone'          => $data['phone']          ?? null,
                            'city'           => $data['city']           ?? null,
                            'province_state' => $data['province_state'] ?? null,
                            'address_line1'  => $data['address_line1']  ?? null,
                            'postal_code'    => $data['postal_code']    ?? null,
                            'status'         => 'active',
                        ]);
                        $data['supplier_id'] = $supplierRecord->id;
                    }
                } else {
                    // Brand-new supplier — create User + Supplier + send credentials
                    $plainPassword = Str::password(12); // e.g. "aB3#xK9!mP2@"

                    $user = User::create([
                        'name'      => $data['contact_person'] ?? $data['company_name'],
                        'email'     => $data['email'],
                        'password'  => Hash::make($plainPassword),
                        'user_type' => 'supplier',
                        'is_active' => true,
                    ]);

                    $supplierRecord = Supplier::create([
                        'user_id'        => $user->id,
                        'company_name'   => $data['company_name'],
                        'contact_person' => $data['contact_person'] ?? '',
                        'email'          => $data['email'],
                        'phone'          => $data['phone']          ?? null,
                        'city'           => $data['city']           ?? null,
                        'province_state' => $data['province_state'] ?? null,
                        'address_line1'  => $data['address_line1']  ?? null,
                        'postal_code'    => $data['postal_code']    ?? null,
                        'status'         => 'active',
                    ]);

                    $data['supplier_id'] = $supplierRecord->id;
                }
            }

            // ── Create VendorSupplier directory entry ────────────────────────
            $data['vendor_id']    = $this->vendor()->id;
            $data['is_preferred'] = $request->boolean('is_preferred');
            $data['status']       = $data['status'] ?? 'active';

            $vendorSupplier = VendorSupplier::create($data);

            // ── Send credentials email (only for brand-new users) ────────────
            if ($plainPassword !== null) {
                $supplierRecord = Supplier::find($data['supplier_id']);
                $supplierRecord->user->notify(
                    new SupplierAccountCreatedNotification(
                        supplier:      $supplierRecord,
                        plainPassword: $plainPassword,
                        vendorName:    $this->vendor()->business_name,
                    )
                );
            }

            return $vendorSupplier;
        });

        return redirect()
            ->route('vendor.suppliers.show', $vendorSupplier)
            ->with('success', 'Supplier "' . $vendorSupplier->company_name . '" added to your directory.');
    }

    // ── Show ───────────────────────────────────────────────────────────────

    public function show(VendorSupplier $supplier)
    {
        abort_if($supplier->vendor_id !== $this->vendor()->id, 403);

        $supplier->load('portalSupplier.products');

        // Recent PRs and POs with this supplier (only if portal-linked)
        $recentPRs = $recentPOs = collect();

        if ($supplier->hasPortalAccount()) {
            $recentPRs = \App\Models\PurchaseRequest::where('vendor_id', $this->vendor()->id)
                ->where('supplier_id', $supplier->supplier_id)
                ->with('items')
                ->latest()
                ->limit(5)
                ->get();

            $recentPOs = \App\Models\PurchaseOrder::where('vendor_id', $this->vendor()->id)
                ->where('supplier_id', $supplier->supplier_id)
                ->latest()
                ->limit(5)
                ->get();

            // Order summary stats
            $orderStats = [
                'total_pos'   => \App\Models\PurchaseOrder::where('vendor_id', $this->vendor()->id)
                    ->where('supplier_id', $supplier->supplier_id)->count(),
                'total_spend' => \App\Models\PurchaseOrder::where('vendor_id', $this->vendor()->id)
                    ->where('supplier_id', $supplier->supplier_id)
                    ->whereIn('status', ['confirmed'])->sum('total_amount'),
                'pending_pos' => \App\Models\PurchaseOrder::where('vendor_id', $this->vendor()->id)
                    ->where('supplier_id', $supplier->supplier_id)
                    ->whereIn('status', ['submitted','approved','processing','shipped','delivered'])->count(),
            ];
        } else {
            $orderStats = ['total_pos' => 0, 'total_spend' => 0, 'pending_pos' => 0];
        }

        return view('vendor.suppliers.show', compact('supplier', 'recentPRs', 'recentPOs', 'orderStats'));
    }

    // ── Edit ───────────────────────────────────────────────────────────────

    public function edit(VendorSupplier $supplier)
    {
        abort_if($supplier->vendor_id !== $this->vendor()->id, 403);

        // Portal suppliers that could be linked (excluding already-linked ones, but keeping current)
        $availablePortalSuppliers = Supplier::where('status', 'active')
            ->where(function ($q) use ($supplier) {
                $q->whereNotIn('id', VendorSupplier::forVendor($this->vendor()->id)
                    ->whereNotNull('supplier_id')
                    ->where('id', '!=', $supplier->id)
                    ->pluck('supplier_id'))
                  ->orWhere('id', $supplier->supplier_id);
            })
            ->orderBy('company_name')
            ->get(['id', 'company_name', 'contact_person', 'email', 'city']);

        return view('vendor.suppliers.edit', compact('supplier', 'availablePortalSuppliers'));
    }

    // ── Update ─────────────────────────────────────────────────────────────

    public function update(Request $request, VendorSupplier $supplier)
    {
        abort_if($supplier->vendor_id !== $this->vendor()->id, 403);

        $data = $request->validate([
            'supplier_id'          => ['nullable', 'exists:suppliers,id'],
            'company_name'         => ['required', 'string', 'max:255'],
            'contact_person'       => ['nullable', 'string', 'max:255'],
            'email'                => ['nullable', 'email', 'max:255'],
            'phone'                => ['nullable', 'string', 'max:50'],
            'address_line1'        => ['nullable', 'string', 'max:500'],
            'city'                 => ['nullable', 'string', 'max:100'],
            'province_state'       => ['nullable', 'string', 'max:100'],
            'postal_code'          => ['nullable', 'string', 'max:20'],
            'country'              => ['nullable', 'string', 'max:100'],
            'account_number'       => ['nullable', 'string', 'max:100'],
            'payment_terms'        => ['nullable', 'in:cod,net7,net15,net30,net60,custom'],
            'payment_terms_custom' => ['nullable', 'string', 'max:255'],
            'credit_limit'         => ['nullable', 'numeric', 'min:0'],
            'is_preferred'         => ['nullable', 'boolean'],
            'status'               => ['nullable', 'in:active,inactive,blocked'],
            'notes'                => ['nullable', 'string', 'max:2000'],
        ]);

        // ── Duplicate email check (exclude self) ─────────────────────────────
        if (!empty($data['email'])) {
            $exists = VendorSupplier::forVendor($this->vendor()->id)
                ->where('email', $data['email'])
                ->where('id', '!=', $supplier->id)
                ->exists();

            if ($exists) {
                return back()->withInput()
                    ->withErrors(['email' => 'A supplier with this email is already in your directory.']);
            }
        }

        DB::transaction(function () use ($data, $request, $supplier) {

            $plainPassword = null;

            // ── Vendor is now linking / changing a portal account ────────────
            // Only run creation logic if supplier_id changed from what it was
            $newSupplierId = $data['supplier_id'] ?? null;
            $isNewLink     = $newSupplierId && $newSupplierId !== $supplier->supplier_id;

            if ($isNewLink) {
                // Portal supplier already exists — just link, nothing to create
                // (supplier_id is already set in $data)

            } elseif (empty($data['supplier_id']) && !$supplier->hasPortalAccount() && !empty($data['email'])) {
                // Still no portal link but email is present — create portal account
                // if the email doesn't already belong to a user
                $existingUser = User::where('email', $data['email'])->first();

                if ($existingUser) {
                    $data['supplier_id'] = $existingUser->supplier?->id;
                } else {
                    $plainPassword = Str::password(12);

                    $user = User::create([
                        'name'      => $data['contact_person'] ?? $data['company_name'],
                        'email'     => $data['email'],
                        'password'  => Hash::make($plainPassword),
                        'user_type' => 'supplier',
                        'is_active' => true,
                    ]);

                    $supplierRecord = Supplier::create([
                        'user_id'        => $user->id,
                        'company_name'   => $data['company_name'],
                        'contact_person' => $data['contact_person'] ?? '',
                        'email'          => $data['email'],
                        'phone'          => $data['phone']          ?? null,
                        'city'           => $data['city']           ?? null,
                        'province_state' => $data['province_state'] ?? null,
                        'address_line1'  => $data['address_line1']  ?? null,
                        'postal_code'    => $data['postal_code']    ?? null,
                        'status'         => 'active',
                    ]);

                    $data['supplier_id'] = $supplierRecord->id;
                }
            }

            $data['is_preferred'] = $request->boolean('is_preferred');
            $supplier->update($data);

            // ── Send credentials only if a brand-new user was created ────────
            if ($plainPassword !== null) {
                $supplierRecord = Supplier::find($data['supplier_id']);
                $supplierRecord->user->notify(
                    new SupplierAccountCreatedNotification(
                        supplier:      $supplierRecord,
                        plainPassword: $plainPassword,
                        vendorName:    $this->vendor()->business_name,
                    )
                );
            }
        });

        return redirect()
            ->route('vendor.suppliers.show', $supplier)
            ->with('success', 'Supplier details updated.');
    }

    // ── Destroy ────────────────────────────────────────────────────────────

    public function destroy(VendorSupplier $supplier)
    {
        abort_if($supplier->vendor_id !== $this->vendor()->id, 403);

        // Safety: block if there are active/pending orders with this supplier
        if ($supplier->hasPortalAccount()) {
            $activeOrders = \App\Models\PurchaseOrder::where('vendor_id', $this->vendor()->id)
                ->where('supplier_id', $supplier->supplier_id)
                ->whereNotIn('status', ['confirmed', 'cancelled', 'rejected'])
                ->count();

            if ($activeOrders > 0) {
                return back()->withErrors([
                    'delete' => "Cannot remove the supplier — there are active order(s). Resolve them first.",
                ]);
            }
        }

        $name = $supplier->company_name;
        $supplier->delete();

        return redirect()->route('vendor.suppliers.index')
            ->with('success', " removed from your supplier directory.");
    }

    // ── Toggle preferred ───────────────────────────────────────────────────

    public function togglePreferred(VendorSupplier $supplier)
    {
        abort_if($supplier->vendor_id !== $this->vendor()->id, 403);

        $supplier->update(['is_preferred' => !$supplier->is_preferred]);

        $label = $supplier->is_preferred ? 'marked as preferred' : 'removed from preferred';

        return back()->with('success', "");
    }

    // ── Quick status change ────────────────────────────────────────────────

    public function updateStatus(Request $request, VendorSupplier $supplier)
    {
        abort_if($supplier->vendor_id !== $this->vendor()->id, 403);

        $request->validate([
            'status' => ['required', 'in:active,inactive,blocked'],
        ]);

        $supplier->update(['status' => $request->status]);

        return back()->with('success', "Supplier status updated to ");
    }
}
