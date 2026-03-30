<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PlatformSettingsController extends Controller
{
    private array $keys = [
        'app_name', 'app_tagline', 'support_email', 'support_phone',
        'maintenance_mode', 'maintenance_message',
        'allow_new_registrations', 'require_email_verification',
        'paypal_sandbox', 'default_currency',
        'min_subscription_price', 'commission_rate',
    ];

    public function index()
    {
        $settings = [];
        foreach ($this->keys as $key) {
            $settings[$key] = Cache::get("platform_setting_{$key}")
                ?? config("platform.{$key}")
                ?? null;
        }

        // Platform-wide stats for the settings page
        $platformStats = [
            'total_vendors'        => \App\Models\Vendor::count(),
            'active_vendors'       => \App\Models\Vendor::where('status','active')->count(),
            'total_customers'      => \App\Models\Customer::count(),
            'total_orders'         => \App\Models\Order::count(),
            'total_revenue'        => \App\Models\Subscription::where('status','active')->sum('amount_paid'),
            'total_reviews'        => \App\Models\Review::count(),
        ];

        return view('admin.settings.index', compact('settings','platformStats'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'app_name'                  => ['required','string','max:80'],
            'app_tagline'               => ['nullable','string','max:160'],
            'support_email'             => ['nullable','email','max:120'],
            'support_phone'             => ['nullable','string','max:30'],
            'maintenance_message'       => ['nullable','string','max:500'],
            'default_currency'          => ['nullable','string','max:10'],
            'commission_rate'           => ['nullable','numeric','min:0','max:100'],
        ]);

        $booleans = ['maintenance_mode','allow_new_registrations','require_email_verification','paypal_sandbox'];
        foreach ($booleans as $b) {
            Cache::put("platform_setting_{$b}", $request->boolean($b), now()->addYears(10));
        }
        foreach ($data as $k => $v) {
            Cache::put("platform_setting_{$k}", $v, now()->addYears(10));
        }

        return back()->with('success', 'Platform settings saved.');
    }
}
