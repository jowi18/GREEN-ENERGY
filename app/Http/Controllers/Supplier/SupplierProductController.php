<?php

namespace App\Http\Controllers\Supplier;

// ═══════════════════════════════════════════════════════════════════════════
// app/Http/Controllers/Supplier/SupplierProductController.php
// Handles supplier's own product catalogue (CRUD + images + spec sheet)
// ═══════════════════════════════════════════════════════════════════════════

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Models\SupplierProduct;
use App\Models\SupplierProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SupplierProductController extends Controller
{
    private function supplier()
    {
        return auth()->user()->supplier;
    }

    public function index()
    {
        $products = $this->supplier()
            ->products()
            ->with(['category', 'primaryImage'])
            ->latest()
            ->paginate(20);

        return view('supplier.products.index', compact('products'));
    }

    public function create()
    {
        $categories = ProductCategory::forSelect();
        return view('supplier.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id'       => ['nullable', 'exists:product_categories,id'],
            'name'              => ['required', 'string', 'max:255'],
            'sku'               => ['nullable', 'string', 'max:100'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description'       => ['nullable', 'string'],
            'unit_price'        => ['required', 'numeric', 'min:0'],
            'unit_of_measure'   => ['nullable', 'string', 'max:50'],
            'minimum_order_qty' => ['nullable', 'integer', 'min:1'],
            'stock_available'   => ['nullable', 'integer', 'min:0'],
            'lead_time_days'    => ['nullable', 'integer', 'min:0'],
            'weight_kg'         => ['nullable', 'numeric', 'min:0'],
            'warranty_months'   => ['nullable', 'integer', 'min:0'],
            'status'            => ['nullable', 'in:active,inactive'],
            'is_featured'       => ['nullable', 'boolean'],
            'spec_sheet'        => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'images'            => ['nullable', 'array', 'max:8'],
            'images.*'          => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        DB::transaction(function () use ($data, $request) {
            // Handle spec sheet upload
            if ($request->hasFile('spec_sheet')) {
                $data['spec_sheet'] = $request->file('spec_sheet')
                    ->store("supplier/{$this->supplier()->id}/specs", 'public');
            }

            $data['supplier_id'] = $this->supplier()->id;
            $data['is_featured']  = $request->boolean('is_featured');
            $product = SupplierProduct::create($data);

            // Handle product images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store("supplier/{$this->supplier()->id}/products/{$product->id}", 'public');
                    SupplierProductImage::create([
                        'supplier_product_id' => $product->id,
                        'file_path'           => $path,
                        'alt_text'            => $product->name,
                        'is_primary'          => $index === 0,
                        'sort_order'          => $index,
                    ]);
                }
            }
        });

        return redirect()->route('supplier.products.index')
            ->with('success', 'Product listed successfully.');
    }

    public function edit(SupplierProduct $product)
    {
        abort_if($product->supplier_id !== $this->supplier()->id, 403);

        $categories = ProductCategory::forSelect();
        $product->load('images', 'category');

        return view('supplier.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, SupplierProduct $product)
    {
        abort_if($product->supplier_id !== $this->supplier()->id, 403);

        $data = $request->validate([
            'category_id'       => ['nullable', 'exists:product_categories,id'],
            'name'              => ['required', 'string', 'max:255'],
            'sku'               => ['nullable', 'string', 'max:100'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description'       => ['nullable', 'string'],
            'unit_price'        => ['required', 'numeric', 'min:0'],
            'unit_of_measure'   => ['nullable', 'string', 'max:50'],
            'minimum_order_qty' => ['nullable', 'integer', 'min:1'],
            'stock_available'   => ['nullable', 'integer', 'min:0'],
            'lead_time_days'    => ['nullable', 'integer', 'min:0'],
            'weight_kg'         => ['nullable', 'numeric', 'min:0'],
            'warranty_months'   => ['nullable', 'integer', 'min:0'],
            'status'            => ['nullable', 'in:active,inactive'],
            'is_featured'       => ['nullable', 'boolean'],
            'spec_sheet'        => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'images'            => ['nullable', 'array', 'max:8'],
            'images.*'          => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        DB::transaction(function () use ($data, $request, $product) {
            if ($request->hasFile('spec_sheet')) {
                if ($product->spec_sheet) Storage::disk('public')->delete($product->spec_sheet);
                $data['spec_sheet'] = $request->file('spec_sheet')
                    ->store("supplier/{$this->supplier()->id}/specs", 'public');
            }

            $data['is_featured'] = $request->boolean('is_featured');
            $product->update($data);

            if ($request->hasFile('images')) {
                $existingCount = $product->images()->count();
                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store("supplier/{$this->supplier()->id}/products/{$product->id}", 'public');
                    SupplierProductImage::create([
                        'supplier_product_id' => $product->id,
                        'file_path'           => $path,
                        'alt_text'            => $product->name,
                        'is_primary'          => $existingCount === 0 && $index === 0,
                        'sort_order'          => $existingCount + $index,
                    ]);
                }
            }
        });

        return redirect()->route('supplier.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(SupplierProduct $product)
    {
        abort_if($product->supplier_id !== $this->supplier()->id, 403);
        $product->delete();

        return back()->with('success', 'Product removed from catalogue.');
    }

    /** DELETE a single image */
    public function destroyImage(SupplierProductImage $image)
    {
        $product = $image->product;
        abort_if($product->supplier_id !== $this->supplier()->id, 403);

        Storage::disk('public')->delete($image->file_path);
        $image->delete();

        // Promote next image to primary if this was primary
        if ($image->is_primary) {
            $product->images()->oldest('sort_order')->first()?->update(['is_primary' => true]);
        }

        return response()->json(['success' => true]);
    }
}



