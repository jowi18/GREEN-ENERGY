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

    /** Show the multi-step vendor registration form */
    public function showRegister()
    {
        if (auth()->check() && auth()->user()->isVendor()) {
            return redirect()->route('vendor.dashboard');
        }

        return view('auth.vendor-register');
    }

    /** Process the completed registration form */
    public function register(VendorRegisterRequest $request)
    {
        $vendor = $this->registrationService->register($request->validated());

        // Log the new user in immediately
        Auth::login($vendor->user);

        return redirect()->route('vendor.register.status')
            ->with('success', 'Registration submitted! Your application is under review.');
    }

    /** Application status page — shown after registration and while pending */
    public function status()
    {
        $vendor = auth()->user()->vendor;

        if (!$vendor) {
            return redirect()->route('vendor.login');
        }

        // If active, go straight to dashboard
        if ($vendor->isActive()) {
            return redirect()->route('vendor.dashboard');
        }

        return view('auth.vendor-status', compact('vendor'));
    }

    // ── Login ──────────────────────────────────────────────────────────────

    public function showLogin()
    {
        if (auth()->check() && in_array(auth()->user()->user_type, ['vendor', 'employee'])) {
            return redirect()->route('vendor.dashboard');
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

        if (!in_array($user->user_type, ['vendor', 'employee', 'supplier'])) {
            Auth::logout();
            return back()->withErrors(['email' => 'This account is not a vendor or supplier account.']);
        }

        if (!$user->is_active) {
            Auth::logout();
            return back()->withErrors(['email' => 'Your account has been deactivated.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('vendor.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('vendor.login');
    }
}
