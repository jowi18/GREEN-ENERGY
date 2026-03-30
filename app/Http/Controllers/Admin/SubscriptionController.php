<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Vendor;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    // ── All subscriptions ─────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = Subscription::with(['vendor', 'plan'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('plan')) {
            $query->where('subscription_plan_id', $request->plan);
        }

        if ($request->filled('search')) {
            $query->whereHas('vendor', fn ($q) =>
                $q->where('business_name', 'like', "%{$request->search}%")
            );
        }

        $subscriptions = $query->paginate(25)->withQueryString();

        $stats = [
            'active'   => Subscription::where('status','active')->where('expires_at','>',now())->count(),
            'expiring' => Subscription::where('status','active')->whereBetween('expires_at',[now(),now()->addDays(7)])->count(),
            'expired'  => Subscription::where('status','expired')->count(),
            'cancelled'=> Subscription::where('status','cancelled')->count(),
            'revenue'  => Subscription::where('status','active')->sum('amount_paid'),
            'mrr'      => Subscription::where('status','active')->where('expires_at','>',now())
                            ->whereMonth('created_at',now()->month)->sum('amount_paid'),
        ];

        $plans = SubscriptionPlan::orderBy('price')->get();

        return view('admin.subscriptions.index', compact('subscriptions','stats','plans'));
    }

    // ── Subscription plan management ──────────────────────────────────────

    public function plans()
    {
        $plans = SubscriptionPlan::withCount('subscriptions')->orderBy('sort_order')->get();
        return view('admin.subscriptions.plans', compact('plans'));
    }

    public function createPlan()
    {
        return view('admin.subscriptions.plan_form', ['plan' => new SubscriptionPlan()]);
    }

    public function storePlan(Request $request)
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:80'],
            'slug'           => ['required', 'string', 'max:80', 'unique:subscription_plans,slug'],
            'description'    => ['nullable', 'string', 'max:500'],
            'price'          => ['required', 'numeric', 'min:0'],
            'billing_cycle'  => ['required', 'in:monthly,quarterly,annually'],
            'duration_days'  => ['required', 'integer', 'min:1'],
            'max_products'   => ['nullable', 'integer', 'min:-1'],
            'max_employees'  => ['nullable', 'integer', 'min:-1'],
            'features'       => ['nullable', 'array'],
            'features.*'     => ['nullable', 'string', 'max:120'],
            'is_featured'    => ['boolean'],
            'is_active'      => ['boolean'],
            'sort_order'     => ['integer', 'min:0'],
        ]);

        $data['features']   = array_values(array_filter($request->input('features', []), fn ($f) => trim($f) !== ''));
        $data['is_featured']= $request->boolean('is_featured');
        $data['is_active']  = $request->boolean('is_active', true);

        SubscriptionPlan::create($data);

        return redirect()->route('admin.subscriptions.plans')->with('success', 'Plan created.');
    }

    public function editPlan(SubscriptionPlan $plan)
    {
        return view('admin.subscriptions.plan_form', compact('plan'));
    }

    public function updatePlan(Request $request, SubscriptionPlan $plan)
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:80'],
            'description'   => ['nullable', 'string', 'max:500'],
            'price'         => ['required', 'numeric', 'min:0'],
            'billing_cycle' => ['required', 'in:monthly,quarterly,annually'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'max_products'  => ['nullable', 'integer', 'min:-1'],
            'max_employees' => ['nullable', 'integer', 'min:-1'],
            'features'      => ['nullable', 'array'],
            'features.*'    => ['nullable', 'string', 'max:120'],
            'is_featured'   => ['boolean'],
            'is_active'     => ['boolean'],
            'sort_order'    => ['integer', 'min:0'],
        ]);

        $data['features']   = array_values(array_filter($request->input('features', []), fn ($f) => trim($f) !== ''));
        $data['is_featured']= $request->boolean('is_featured');
        $data['is_active']  = $request->boolean('is_active', true);

        $plan->update($data);

        return redirect()->route('admin.subscriptions.plans')->with('success', 'Plan updated.');
    }

    public function togglePlan(SubscriptionPlan $plan)
    {
        $plan->update(['is_active' => ! $plan->is_active]);
        return back()->with('success', "Plan \"{$plan->name}\" " . ($plan->is_active ? 'activated' : 'deactivated') . '.');
    }

    // ── Manual extend / cancel ────────────────────────────────────────────

    public function extendSubscription(Request $request, Subscription $subscription)
    {
        $data = $request->validate(['days' => ['required','integer','min:1','max:365']]);
        $subscription->update([
            'expires_at' => ($subscription->expires_at ?? now())->addDays($data['days']),
            'status'     => 'active',
        ]);
        return back()->with('success', "Subscription extended by {$data['days']} days.");
    }

    public function cancelSubscription(Subscription $subscription)
    {
        $subscription->update(['status' => 'cancelled', 'cancelled_at' => now()]);
        Vendor::where('id', $subscription->vendor_id)->update(['status' => 'suspended']);
        return back()->with('success', 'Subscription cancelled and vendor suspended.');
    }
}







