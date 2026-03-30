<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    private function vendor()
    {
        $user = auth()->user();
        return $user->isEmployee() ? $user->employee->vendor : $user->vendor;
    }

    // ── List ──────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $vendor = $this->vendor();

        $query = Product::with(['category', 'inventory', 'primaryImage'])
            ->forVendor($vendor->id);

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('product_type', $request->type);
        }

        $products   = $query->latest()->paginate(20)->withQueryString();
        $categories = ProductCategory::active()->get();

        $stats = [
            'total'        => Product::forVendor($vendor->id)->count(),
            'active'       => Product::forVendor($vendor->id)->where('status', 'active')->count(),
            'draft'        => Product::forVendor($vendor->id)->where('status', 'draft')->count(),
            'out_of_stock' => Product::forVendor($vendor->id)->where('status', 'out_of_stock')->count(),
        ];

        return view('vendor.products.index', compact('products', 'categories', 'stats'));
    }

    // ── Create ────────────────────────────────────────────────────────────

    public function create()
    {
        $categories = ProductCategory::active()->with('children')->parents()->get();
        return view('vendor.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'category_id'       => ['required', 'exists:product_categories,id'],
            'product_type'      => ['required', 'in:physical,service,digital'],
            'price'             => ['required', 'numeric', 'min:0'],
            'compare_price'     => ['nullable', 'numeric', 'min:0'],
            'cost_price'        => ['nullable', 'numeric', 'min:0'],
            'sku'               => ['nullable', 'string', 'max:100'],
            'barcode'           => ['nullable', 'string', 'max:100'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description'       => ['nullable', 'string'],
            'weight_kg'         => ['nullable', 'numeric', 'min:0'],
            'warranty_months'   => ['nullable', 'integer', 'min:0'],
            'warranty_terms'    => ['nullable', 'string'],
            'status'            => ['required', 'in:draft,active'],
            'is_featured'       => ['boolean'],
            // Inventory
            'opening_stock'     => ['nullable', 'integer', 'min:0'],
            'reorder_point'     => ['nullable', 'integer', 'min:0'],
            'reorder_quantity'  => ['nullable', 'integer', 'min:0'],
            'storage_location'  => ['nullable', 'string', 'max:100'],
            // Images
            'images'            => ['nullable', 'array', 'max:6'],
            'images.*'          => ['image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ]);

        $vendor  = $this->vendor();
        $slug    = $this->uniqueSlug($data['name'], $vendor->id);

        $product = Product::create([
            'vendor_id'         => $vendor->id,
            'category_id'       => $data['category_id'],
            'name'              => $data['name'],
            'slug'              => $slug,
            'sku'               => $data['sku'] ?? null,
            'barcode'           => $data['barcode'] ?? null,
            'short_description' => $data['short_description'] ?? null,
            'description'       => $data['description'] ?? null,
            'price'             => $data['price'],
            'compare_price'     => $data['compare_price'] ?? null,
            'cost_price'        => $data['cost_price'] ?? null,
            'currency'          => 'PHP',
            'product_type'      => $data['product_type'],
            'weight_kg'         => $data['weight_kg'] ?? null,
            'warranty_months'   => $data['warranty_months'] ?? null,
            'warranty_terms'    => $data['warranty_terms'] ?? null,
            'status'            => $data['status'],
            'is_featured'       => $request->boolean('is_featured'),
        ]);

        // Create inventory record
        $openingStock = (int) ($data['opening_stock'] ?? 0);

        $inventory = Inventory::create([
            'product_id'       => $product->id,
            'vendor_id'        => $vendor->id,
            'quantity_on_hand' => $data['product_type'] === 'service' ? 999 : $openingStock,
            'reorder_point'    => $data['reorder_point'] ?? 5,
            'reorder_quantity' => $data['reorder_quantity'] ?? 10,
            'storage_location' => $data['storage_location'] ?? null,
            'unit_of_measure'  => $data['product_type'] === 'service' ? 'service' : 'piece',
            'last_stock_update'=> now(),
        ]);

        // Log opening stock movement
        if ($openingStock > 0 && $data['product_type'] !== 'service') {
            \App\Models\StockMovement::create([
                'inventory_id'    => $inventory->id,
                'product_id'      => $product->id,
                'vendor_id'       => $vendor->id,
                'movement_type'   => 'opening_stock',
                'quantity_change' => $openingStock,
                'quantity_before' => 0,
                'quantity_after'  => $openingStock,
                'notes'           => 'Opening stock on product creation',
                'performed_by'    => auth()->id(),
            ]);
        }

        // Upload images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $i => $image) {
                $path = $image->store("products/{$vendor->id}", 'public');
                \App\Models\ProductImage::create([
                    'product_id'  => $product->id,
                    'file_path'   => $path,
                    'alt_text'    => $product->name,
                    'is_primary'  => $i === 0,
                    'sort_order'  => $i,
                ]);
            }
        }

        return redirect()
            ->route('vendor.products.index')
            ->with('success', "Product \"{$product->name}\" created successfully.");
    }

    // ── Edit ──────────────────────────────────────────────────────────────

    public function edit(Product $product)
    {
        $this->authorizeProduct($product);
        $product->load(['category', 'images', 'inventory']);
        $categories = ProductCategory::active()->with('children')->parents()->get();
        return view('vendor.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $this->authorizeProduct($product);

        $data = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'category_id'       => ['required', 'exists:product_categories,id'],
            'product_type'      => ['required', 'in:physical,service,digital'],
            'price'             => ['required', 'numeric', 'min:0'],
            'compare_price'     => ['nullable', 'numeric', 'min:0'],
            'cost_price'        => ['nullable', 'numeric', 'min:0'],
            'sku'               => ['nullable', 'string', 'max:100'],
            'barcode'           => ['nullable', 'string', 'max:100'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description'       => ['nullable', 'string'],
            'weight_kg'         => ['nullable', 'numeric', 'min:0'],
            'warranty_months'   => ['nullable', 'integer', 'min:0'],
            'warranty_terms'    => ['nullable', 'string'],
            'status'            => ['required', 'in:draft,active,inactive'],
            'is_featured'       => ['boolean'],
            'reorder_point'     => ['nullable', 'integer', 'min:0'],
            'reorder_quantity'  => ['nullable', 'integer', 'min:0'],
            'storage_location'  => ['nullable', 'string', 'max:100'],
            'images'            => ['nullable', 'array', 'max:6'],
            'images.*'          => ['image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ]);

        $product->update([
            'category_id'       => $data['category_id'],
            'name'              => $data['name'],
            'sku'               => $data['sku'] ?? null,
            'barcode'           => $data['barcode'] ?? null,
            'short_description' => $data['short_description'] ?? null,
            'description'       => $data['description'] ?? null,
            'price'             => $data['price'],
            'compare_price'     => $data['compare_price'] ?? null,
            'cost_price'        => $data['cost_price'] ?? null,
            'product_type'      => $data['product_type'],
            'weight_kg'         => $data['weight_kg'] ?? null,
            'warranty_months'   => $data['warranty_months'] ?? null,
            'warranty_terms'    => $data['warranty_terms'] ?? null,
            'status'            => $data['status'],
            'is_featured'       => $request->boolean('is_featured'),
        ]);

        // Update inventory settings
        if ($product->inventory) {
            $product->inventory->update([
                'reorder_point'    => $data['reorder_point'] ?? 5,
                'reorder_quantity' => $data['reorder_quantity'] ?? 10,
                'storage_location' => $data['storage_location'] ?? null,
            ]);
        }

        // New images
        if ($request->hasFile('images')) {
            $nextOrder = $product->images()->max('sort_order') + 1;
            foreach ($request->file('images') as $i => $image) {
                $path = $image->store("products/{$product->vendor_id}", 'public');
                \App\Models\ProductImage::create([
                    'product_id' => $product->id,
                    'file_path'  => $path,
                    'alt_text'   => $product->name,
                    'is_primary' => $product->images()->count() === 0 && $i === 0,
                    'sort_order' => $nextOrder + $i,
                ]);
            }
        }

        return redirect()
            ->route('vendor.products.edit', $product)
            ->with('success', 'Product updated successfully.');
    }

    // ── Delete ────────────────────────────────────────────────────────────

    public function destroy(Product $product)
    {
        $this->authorizeProduct($product);

        // Delete images from storage
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->file_path);
        }

        $product->delete(); // soft delete

        return redirect()
            ->route('vendor.products.index')
            ->with('success', "Product \"{$product->name}\" removed.");
    }

    // ── Image management ──────────────────────────────────────────────────

    public function deleteImage(Request $request, Product $product)
    {
        $this->authorizeProduct($product);

        $image = $product->images()->findOrFail($request->image_id);
        Storage::disk('public')->delete($image->file_path);

        $wasPrimary = $image->is_primary;
        $image->delete();

        // Reassign primary if needed
        if ($wasPrimary && $product->images()->count()) {
            $product->images()->oldest('sort_order')->first()->update(['is_primary' => true]);
        }

        return response()->json(['success' => true]);
    }

    public function setPrimaryImage(Request $request, Product $product)
    {
        $this->authorizeProduct($product);

        $product->images()->update(['is_primary' => false]);
        $product->images()->where('id', $request->image_id)->update(['is_primary' => true]);

        return response()->json(['success' => true]);
    }

    // ── Toggle publish ────────────────────────────────────────────────────

    public function toggleStatus(Product $product)
    {
        $this->authorizeProduct($product);

        $newStatus = $product->status === 'active' ? 'inactive' : 'active';
        $product->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'status'  => $newStatus,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function authorizeProduct(Product $product): void
    {
        if ($product->vendor_id !== $this->vendor()->id) {
            abort(403);
        }
    }

    private function uniqueSlug(string $name, int $vendorId): string
    {
        $base  = Str::slug($name);
        $slug  = $base;
        $count = 1;

        while (Product::where('vendor_id', $vendorId)->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $count++;
        }

        return $slug;
    }
}
