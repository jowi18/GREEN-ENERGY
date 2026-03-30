<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\ProductCategory;
use App\Models\SubscriptionPlan;
use App\Models\Product;

// ─────────────────────────────────────────────────────────────────────────────
// LandingController
// ─────────────────────────────────────────────────────────────────────────────

class LandingController extends Controller
{
    public function index()
    {
        $subscriptionPlans = SubscriptionPlan::active()->get();

        $featuredVendors = Vendor::where('status', 'active')
            ->where('average_rating', '>=', 4.0)
            ->orderByDesc('average_rating')
            ->limit(6)
            ->get();

        $categories = ProductCategory::active()->parents()->with('children')->get();

        $featuredProducts = Product::with(['vendor', 'primaryImage'])
            ->where('status', 'active')
            ->where('is_featured', true)
            ->limit(8)
            ->get();
        $mapVendors = Vendor::active()
        ->whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->get()
        ->map(fn($v) => [
            'id'       => $v->id,
            'name'     => $v->business_name,
            'city'     => $v->city,
            'initials' => strtoupper(substr($v->business_name, 0, 2)),
            'logo'     => $v->shop_logo ? asset('storage/' . $v->shop_logo) : null,
            'tagline'  => $v->tagline ?? null,
            'rating'   => round($v->average_rating, 1),
            'reviews'  => $v->total_reviews,
            'lat'      => (float) $v->latitude,
            'lng'      => (float) $v->longitude,
            'url'      => route('customer.register'),
        ]);

        return view('landing.index', compact(
            'subscriptionPlans',
            'featuredVendors',
            'categories',
            'featuredProducts',
            'mapVendors'
        ));
    }
}



