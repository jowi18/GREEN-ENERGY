<?php

// ════════════════════════════════════════════════════════════════
// VendorBrowseController
// ════════════════════════════════════════════════════════════════

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\VendorService;
use Illuminate\Http\Request;

class VendorBrowseController extends Controller
{
    public function index(Request $request)
    {
        $customer = auth()->user()->customer;

        $query = Vendor::active()->with(['products','activeSubscription']);

        // Location-based sort
        if ($customer->latitude && $customer->longitude) {
            $query->nearby($customer->latitude, $customer->longitude, 100);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('business_name', 'like', "%{$request->search}%")
                  ->orWhere('city', 'like', "%{$request->search}%");
            });
        }

        $vendors = $query->paginate(12)->withQueryString();

        // For map markers — all active vendors with coordinates
        $mapVendors = Vendor::active()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        return view('customer.vendors.index', compact(
            'vendors', 'mapVendors', 'customer'
        ));
    }

    public function show(Vendor $vendor)
    {
        abort_unless($vendor->isActive(), 404);

        $vendor->load([
            'products' => fn ($q) => $q->where('status', 'active')
                ->where('product_type', 'physical')
                ->with(['primaryImage', 'inventory']),
            'reviews',
        ]);

        $categories = ProductCategory::whereHas('products', fn ($q) =>
            $q->forVendor($vendor->id)
              ->where('status', 'active')
              ->where('product_type', 'physical')
        )->get();

        // Active service catalog for this vendor
        $vendorServices = VendorService::forVendor($vendor->id)
            ->active()
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order')
            ->get();

        return view('customer.vendors.show', compact('vendor', 'categories', 'vendorServices'));
    }
}



