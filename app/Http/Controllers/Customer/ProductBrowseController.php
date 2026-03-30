<?php

// ════════════════════════════════════════════════════════════════
// ProductBrowseController (in same file for brevity — split if preferred)
// ════════════════════════════════════════════════════════════════

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductBrowseController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['vendor', 'primaryImage', 'inventory'])
            ->where('status', 'active')
            ->whereHas('vendor', fn ($q) => $q->active());

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('sku',  'like', "%{$request->search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('vendor')) {
            $query->where('vendor_id', $request->vendor);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $sortMap = [
            'newest'     => ['created_at', 'desc'],
            'price_asc'  => ['price', 'asc'],
            'price_desc' => ['price', 'desc'],
            'popular'    => ['total_sold', 'desc'],
        ];

        [$sortCol, $sortDir] = $sortMap[$request->input('sort', 'newest')] ?? ['created_at', 'desc'];
        $query->orderBy($sortCol, $sortDir);

        $products   = $query->paginate(20)->withQueryString();
        $categories = ProductCategory::active()->parents()->get();

        return view('customer.products.index', compact('products', 'categories'));
    }

    public function show(Product $product)
    {
        abort_unless($product->status === 'active' && $product->vendor?->isActive(), 404);

        $product->load(['vendor', 'images', 'inventory', 'category']);

        $related = Product::with(['primaryImage', 'vendor'])
            ->where('status', 'active')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->limit(4)->get();

        return view('customer.products.show', compact('product', 'related'));
    }
}
