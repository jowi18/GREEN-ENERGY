<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StoreSettingsController extends Controller
{
    private function vendor()
    {
        $u = auth()->user();
        return $u->isEmployee() ? $u->employee->vendor : $u->vendor;
    }

    // ── Main settings page ────────────────────────────────────────────────

    public function index()
    {
        $vendor = $this->vendor();
        // $vendor->load('hrProfiles');
        return view('vendor.store_settings.index', compact('vendor'));
    }

    // ── Section: Branding (logo, cover, tagline, description) ────────────

    public function updateBranding(Request $request)
    {
        $vendor = $this->vendor();

        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:120'],
            'tagline'       => ['nullable', 'string', 'max:160'],
            'shop_description' => ['nullable', 'string', 'max:500'],
            'about'         => ['nullable', 'string', 'max:4000'],
            'year_established' => ['nullable', 'integer', 'min:1900', 'max:' . date('Y')],
            'shop_logo'     => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'cover_photo'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        // Logo upload
        if ($request->hasFile('shop_logo')) {
            if ($vendor->shop_logo) {
                Storage::disk('public')->delete($vendor->shop_logo);
            }
            $data['shop_logo'] = $request->file('shop_logo')
                ->store("vendors/{$vendor->id}/branding", 'public');
        }

        // Cover photo upload
        if ($request->hasFile('cover_photo')) {
            if ($vendor->cover_photo) {
                Storage::disk('public')->delete($vendor->cover_photo);
            }
            $data['cover_photo'] = $request->file('cover_photo')
                ->store("vendors/{$vendor->id}/branding", 'public');
        }

        // Remove logo
        if ($request->input('remove_logo') === '1' && $vendor->shop_logo) {
            Storage::disk('public')->delete($vendor->shop_logo);
            $data['shop_logo'] = null;
        }

        // Remove cover
        if ($request->input('remove_cover') === '1' && $vendor->cover_photo) {
            Storage::disk('public')->delete($vendor->cover_photo);
            $data['cover_photo'] = null;
        }

        unset($data['shop_logo'], $data['cover_photo']); // remove file objects from $data
        if ($request->hasFile('shop_logo'))  $data['shop_logo']   = $request->file('shop_logo')->store("vendors/{$vendor->id}/branding", 'public');
        if ($request->hasFile('cover_photo'))$data['cover_photo'] = $request->file('cover_photo')->store("vendors/{$vendor->id}/branding", 'public');
        if ($request->input('remove_logo')  === '1') { Storage::disk('public')->delete($vendor->shop_logo ?? ''); $data['shop_logo']   = null; }
        if ($request->input('remove_cover') === '1') { Storage::disk('public')->delete($vendor->cover_photo ?? ''); $data['cover_photo'] = null; }

        $vendor->update($data);

        return back()->with('success', 'Branding updated successfully.');
    }

    // ── Section: Highlights & Certifications ─────────────────────────────

    public function updateHighlights(Request $request)
    {
        $vendor = $this->vendor();

        $request->validate([
            'highlights'       => ['nullable', 'array', 'max:8'],
            'highlights.*'     => ['nullable', 'string', 'max:100'],
            'certifications'   => ['nullable', 'array', 'max:6'],
            'certifications.*.name'  => ['nullable', 'string', 'max:100'],
            'certifications.*.year'  => ['nullable', 'integer', 'min:1990', 'max:' . date('Y')],
            'certifications.*.issuer'=> ['nullable', 'string', 'max:100'],
        ]);

        $highlights = array_values(array_filter(
            $request->input('highlights', []),
            fn ($h) => trim($h) !== ''
        ));

        $certs = collect($request->input('certifications', []))
            ->filter(fn ($c) => ! empty($c['name']))
            ->values()
            ->toArray();

        $vendor->update([
            'highlights'     => $highlights ?: null,
            'certifications' => $certs ?: null,
        ]);

        return back()->with('success', 'Highlights updated.');
    }

    // ── Section: Contact & Social ─────────────────────────────────────────

    public function updateContact(Request $request)
    {
        $vendor = $this->vendor();

        $data = $request->validate([
            'business_phone'   => ['nullable', 'string', 'max:30'],
            'business_email'   => ['nullable', 'email', 'max:120'],
            'support_phone'    => ['nullable', 'string', 'max:30'],
            'support_email'    => ['nullable', 'email', 'max:120'],
            'whatsapp'         => ['nullable', 'string', 'max:30'],
            'viber'            => ['nullable', 'string', 'max:30'],
            'business_website' => ['nullable', 'url', 'max:255'],
            'social_facebook'  => ['nullable', 'url', 'max:255'],
            'social_instagram' => ['nullable', 'url', 'max:255'],
            'social_youtube'   => ['nullable', 'url', 'max:255'],
            'social_tiktok'    => ['nullable', 'url', 'max:255'],
        ]);

        $vendor->update($data);

        return back()->with('success', 'Contact & social links updated.');
    }

    // ── Section: Location & Service Area ─────────────────────────────────

    public function updateLocation(Request $request)
    {
        $vendor = $this->vendor();

        $data = $request->validate([
            'address_line1'  => ['required', 'string', 'max:255'],
            'address_line2'  => ['nullable', 'string', 'max:255'],
            'city'           => ['required', 'string', 'max:100'],
            'province_state' => ['required', 'string', 'max:100'],
            'postal_code'    => ['nullable', 'string', 'max:20'],
            'country'        => ['nullable', 'string', 'max:60'],
            'latitude'       => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'      => ['nullable', 'numeric', 'between:-180,180'],
            'service_area'   => ['nullable', 'string', 'max:255'],
        ]);

        $vendor->update($data);

        return back()->with('success', 'Location updated.');
    }

    // ── Section: Operating Hours ──────────────────────────────────────────

    public function updateHours(Request $request)
    {
        $vendor = $this->vendor();

        $request->validate([
            'hours'                    => ['nullable', 'array'],
            'is_open_now_override'     => ['boolean'],
            'temporary_closure_note'   => ['nullable', 'string', 'max:255'],
            'show_operating_hours'     => ['boolean'],
        ]);

        $days  = ['sun','mon','tue','wed','thu','fri','sat'];
        $hours = [];

        foreach ($days as $i => $day) {
            $open = $request->boolean("hours.{$day}.open");
            $hours[$i] = [
                'open'  => $open,
                'from'  => $request->input("hours.{$day}.from", '08:00'),
                'to'    => $request->input("hours.{$day}.to", '17:00'),
            ];
        }

        $vendor->update([
            'operating_hours'           => $hours,
            'is_open_now_override'      => $request->boolean('is_open_now_override'),
            'temporary_closure_note'    => $request->input('temporary_closure_note'),
            'show_operating_hours'      => $request->boolean('show_operating_hours', true),
        ]);

        return back()->with('success', 'Operating hours updated.');
    }

    // ── Section: Policies ────────────────────────────────────────────────

    public function updatePolicies(Request $request)
    {
        $vendor = $this->vendor();

        $data = $request->validate([
            'return_policy'   => ['nullable', 'string', 'max:4000'],
            'warranty_policy' => ['nullable', 'string', 'max:4000'],
            'payment_terms'   => ['nullable', 'string', 'max:4000'],
        ]);

        $vendor->update($data);

        return back()->with('success', 'Policies saved.');
    }

    // ── Section: Preferences ────────────────────────────────────────────

    public function updatePreferences(Request $request)
    {
        $vendor = $this->vendor();

        $data = $request->validate([
            'show_reviews_publicly'  => ['boolean'],
            'accept_online_orders'   => ['boolean'],
            'accept_service_bookings'=> ['boolean'],
            'seo_title'              => ['nullable', 'string', 'max:160'],
            'seo_description'        => ['nullable', 'string', 'max:320'],
        ]);

        // Booleans default to false if unchecked
        $data['show_reviews_publicly']   = $request->boolean('show_reviews_publicly');
        $data['accept_online_orders']    = $request->boolean('accept_online_orders');
        $data['accept_service_bookings'] = $request->boolean('accept_service_bookings');

        $vendor->update($data);

        return back()->with('success', 'Store preferences saved.');
    }
}
