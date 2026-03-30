<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomerLocationController extends Controller
{
    /**
     * Update customer location — called from AJAX on the vendor browse page.
     *
     * Accepts either:
     *   (a) latitude + longitude from browser Geolocation API
     *   (b) city + province entered manually by the customer
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'latitude'  => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'city'      => ['nullable', 'string', 'max:100'],
            'province'  => ['nullable', 'string', 'max:100'],
        ]);

        $customer = auth()->user()->customer;

        $updates = [];

        // ── GPS coordinates ────────────────────────────────────────────────
        if (isset($data['latitude']) && isset($data['longitude'])) {
            $updates['latitude']  = $data['latitude'];
            $updates['longitude'] = $data['longitude'];
        }

        // ── Manual city/province ───────────────────────────────────────────
        if (! empty($data['city'])) {
            $updates['city'] = $data['city'];
        }

        if (! empty($data['province'])) {
            $updates['province_state'] = $data['province'];
        }

        if (empty($updates)) {
            return response()->json(['success' => false, 'message' => 'No location data provided.'], 422);
        }

        $customer->update($updates);

        return response()->json([
            'success'   => true,
            'message'   => 'Location updated.',
            'latitude'  => $customer->fresh()->latitude,
            'longitude' => $customer->fresh()->longitude,
            'city'      => $customer->fresh()->city,
            'province'  => $customer->fresh()->province_state,
        ]);
    }

    /**
     * Reverse-geocode via Nominatim (OpenStreetMap) — free, no API key needed.
     * Called after browser Geolocation gives us coordinates, to get the city name.
     */
    public function reverseGeocode(Request $request)
    {
        $request->validate([
            'latitude'  => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
        ]);

        try {
            $lat = $request->latitude;
            $lng = $request->longitude;

            $url      = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$lat}&lon={$lng}&zoom=10";
            $response = file_get_contents($url, false, stream_context_create([
                'http' => [
                    'header'  => "User-Agent: SolarHub/1.0\r\n",
                    'timeout' => 5,
                ],
            ]));

            if ($response === false) {
                return response()->json(['success' => false, 'message' => 'Geocoding failed.'], 500);
            }

            $data    = json_decode($response, true);
            $address = $data['address'] ?? [];

            $city     = $address['city']
                     ?? $address['town']
                     ?? $address['municipality']
                     ?? $address['county']
                     ?? '';

            $province = $address['state']
                     ?? $address['region']
                     ?? '';

            return response()->json([
                'success'  => true,
                'city'     => $city,
                'province' => $province,
                'display'  => $data['display_name'] ?? '',
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Could not resolve location name.'], 500);
        }
    }
}
