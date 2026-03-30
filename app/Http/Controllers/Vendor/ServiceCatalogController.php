<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceCatalogController extends Controller
{
    private function vendor()
    {
        $u = auth()->user();
        return $u->isEmployee() ? $u->employee->vendor : $u->vendor;
    }

    private function authorize(VendorService $service): void
    {
        if ($service->vendor_id !== $this->vendor()->id) abort(403);
    }

    // ── Catalog list ──────────────────────────────────────────────────────

    public function index()
    {
        $vendor = $this->vendor();

        $services = VendorService::forVendor($vendor->id)
            ->withTrashed()
            ->orderBy('sort_order')
            ->orderBy('service_type')
            ->orderBy('name')
            ->get();

        $grouped = $services->groupBy('service_type');

        $stats = [
            'total'    => $services->count(),
            'active'   => $services->where('is_active', true)->count(),
            'inactive' => $services->where('is_active', false)->count(),
            'featured' => $services->where('is_featured', true)->count(),
        ];

        $serviceTypes = VendorService::serviceTypes();

        return view('vendor.service-catalog.index', compact(
            'services', 'grouped', 'stats', 'serviceTypes'
        ));
    }

    // ── Create (modal form) ───────────────────────────────────────────────

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'               => ['required', 'string', 'max:200'],
            'service_type'       => ['required', 'string', 'max:60'],
            'short_description'  => ['nullable', 'string', 'max:300'],
            'description'        => ['nullable', 'string'],
            'pricing_type'       => ['required', 'in:fixed,starting_from,per_hour,custom_quote'],
            'price'              => ['nullable', 'numeric', 'min:0'],
            'price_max'          => ['nullable', 'numeric', 'min:0'],
            'duration_hours_min' => ['nullable', 'integer', 'min:0'],
            'duration_hours_max' => ['nullable', 'integer', 'min:0'],
            'coverage_notes'     => ['nullable', 'string', 'max:500'],
            'inclusions'         => ['nullable', 'string'],
            'requirements'       => ['nullable', 'string'],
            'is_featured'        => ['boolean'],
            'is_active'          => ['boolean'],
        ]);

        $vendor = $this->vendor();

        VendorService::create(array_merge($data, [
            'vendor_id' => $vendor->id,
            'currency'  => 'PHP',
            'is_active' => $request->boolean('is_active', true),
            'is_featured'=> $request->boolean('is_featured', false),
        ]));

        return redirect()
            ->route('vendor.service-catalog.index')
            ->with('success', "Service \"{$data['name']}\" added to your catalog.");
    }

    // ── Edit ──────────────────────────────────────────────────────────────

    public function edit(VendorService $vendorService)
    {
        $this->authorize($vendorService);
        return response()->json($vendorService);
    }

    public function update(Request $request, VendorService $vendorService)
    {
        $this->authorize($vendorService);

        $data = $request->validate([
            'name'               => ['required', 'string', 'max:200'],
            'service_type'       => ['required', 'string', 'max:60'],
            'short_description'  => ['nullable', 'string', 'max:300'],
            'description'        => ['nullable', 'string'],
            'pricing_type'       => ['required', 'in:fixed,starting_from,per_hour,custom_quote'],
            'price'              => ['nullable', 'numeric', 'min:0'],
            'price_max'          => ['nullable', 'numeric', 'min:0'],
            'duration_hours_min' => ['nullable', 'integer', 'min:0'],
            'duration_hours_max' => ['nullable', 'integer', 'min:0'],
            'coverage_notes'     => ['nullable', 'string', 'max:500'],
            'inclusions'         => ['nullable', 'string'],
            'requirements'       => ['nullable', 'string'],
            'is_featured'        => ['boolean'],
            'is_active'          => ['boolean'],
        ]);

        $vendorService->update(array_merge($data, [
            'is_active'   => $request->boolean('is_active'),
            'is_featured' => $request->boolean('is_featured'),
        ]));

        return redirect()
            ->route('vendor.service-catalog.index')
            ->with('success', "Service \"{$vendorService->name}\" updated.");
    }

    // ── Toggle active / inactive (AJAX) ───────────────────────────────────

    public function toggle(VendorService $vendorService)
    {
        $this->authorize($vendorService);

        $vendorService->update(['is_active' => ! $vendorService->is_active]);

        return response()->json([
            'success'   => true,
            'is_active' => $vendorService->is_active,
            'message'   => "\"{$vendorService->name}\" is now " . ($vendorService->is_active ? 'active' : 'disabled') . '.',
        ]);
    }

    // ── Toggle featured (AJAX) ────────────────────────────────────────────

    public function toggleFeatured(VendorService $vendorService)
    {
        $this->authorize($vendorService);

        $vendorService->update(['is_featured' => ! $vendorService->is_featured]);

        return response()->json([
            'success'     => true,
            'is_featured' => $vendorService->is_featured,
        ]);
    }

    // ── Reorder (AJAX drag-and-drop) ──────────────────────────────────────

    public function reorder(Request $request)
    {
        $request->validate([
            'order'   => ['required', 'array'],
            'order.*' => ['integer'],
        ]);

        $vendor = $this->vendor();

        foreach ($request->order as $position => $id) {
            VendorService::where('id', $id)
                ->where('vendor_id', $vendor->id)
                ->update(['sort_order' => $position]);
        }

        return response()->json(['success' => true]);
    }

    // ── Delete ────────────────────────────────────────────────────────────

    public function destroy(VendorService $vendorService)
    {
        $this->authorize($vendorService);

        $name = $vendorService->name;
        $vendorService->delete(); // soft delete

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => "\"{$name}\" removed."]);
        }

        return redirect()
            ->route('vendor.service-catalog.index')
            ->with('success', "\"{$name}\" removed from your catalog.");
    }
}
