<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\VendorRegisterRequest;
use App\Services\VendorRegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorAuthController extends Controller
{
    public function __construct(
        private readonly VendorRegistrationService $registrationService
    ) {}

    // ── Registration ───────────────────────────────────────────────────────

    public function showRegister()
    {
        if (auth()->check() && auth()->user()->isVendor()) {
            return redirect()->route('vendor.dashboard');
        }

        return view('auth.vendor-register');
    }

    public function register(VendorRegisterRequest $request)
    {
        $vendor = $this->registrationService->register($request->validated());

        Auth::login($vendor->user);

        return redirect()->route('vendor.register.status')
            ->with('success', 'Registration submitted! Your application is under review.');
    }

    public function status()
    {
        $vendor = auth()->user()->vendor;

        if (!$vendor) {
            return redirect()->route('vendor.login');
        }

        if ($vendor->isActive()) {
            return redirect()->route('vendor.dashboard');
        }

        return view('auth.vendor-status', compact('vendor'));
    }

    // ── Login ──────────────────────────────────────────────────────────────

    public function showLogin()
    {
        if (auth()->check()) {
            return redirect($this->redirectAfterLogin(auth()->user()));
        }

        return view('auth.vendor-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Invalid email or password.']);
        }

        $user = auth()->user();

        // Gate: only vendor, employee, and supplier accounts use this login
        if (!in_array($user->user_type, ['vendor', 'employee', 'supplier'])) {
            Auth::logout();
            return back()->withErrors(['email' => 'This account is not authorised to log in here.']);
        }

        if (!$user->is_active) {
            Auth::logout();
            return back()->withErrors(['email' => 'Your account has been deactivated.']);
        }

        // Supplier-specific check: must have an active supplier profile
        if ($user->isSupplier()) {
            $supplier = $user->supplier;

            if (!$supplier) {
                Auth::logout();
                return back()->withErrors(['email' => 'No supplier profile found for this account.']);
            }

            if ($supplier->status === 'suspended') {
                Auth::logout();
                return back()->withErrors(['email' => 'Your supplier account has been suspended.']);
            }
        }

        $request->session()->regenerate();

        return redirect()->intended($this->redirectAfterLogin($user));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('vendor.login');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    /**
     * Resolve the correct post-login destination based on user type.
     * Suppliers go to their own portal dashboard; vendors/employees
     * go to the vendor dashboard.
     */
    private function redirectAfterLogin(\App\Models\User $user): string
    {
        return match ($user->user_type) {
            'supplier' => route('supplier.dashboard'),
            default    => route('vendor.dashboard'),
        };
    }
}
