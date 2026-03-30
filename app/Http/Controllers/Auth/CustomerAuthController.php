<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRegisterRequest;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;


class CustomerAuthController extends Controller
{
    public function showRegister()
    {
        if (auth()->check() && auth()->user()->isCustomer()) {
            return redirect()->route('customer.dashboard');
        }
        return view('auth.customer-register');
    }

    public function register(CustomerRegisterRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $request) {
            $user = User::create([
                'name'      => $data['first_name'] . ' ' . $data['last_name'],
                'email'     => $data['email'],
                'password'  => Hash::make($data['password']),
                'user_type' => 'customer',
                'is_active' => true,
            ]);

            $idPath = $request->file('government_id')
                ->store('customer-ids', 'public');

            Customer::create([
                'user_id'            => $user->id,
                'first_name'         => $data['first_name'],
                'last_name'          => $data['last_name'],
                'phone'              => $data['phone'],
                'address_line1'      => $data['address_line1'] ?? null,
                'city'               => $data['city'] ?? null,
                'province_state'     => $data['province_state'] ?? null,
                'postal_code'        => $data['postal_code'] ?? null,
                'government_id_type' => $data['government_id_type'],
                'government_id_path' => $idPath,
                'verification_status'=> 'unverified',
            ]);

            Auth::login($user);
        });

        return redirect()->route('customer.dashboard')
            ->with('success', 'Welcome! Your account has been created.');
    }

    public function showLogin()
    {
        if (auth()->check() && auth()->user()->isCustomer()) {
            return redirect()->route('customer.dashboard');
        }
        return view('auth.customer-login');
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

        if (!$user->isCustomer()) {
            Auth::logout();
            return back()->withErrors(['email' => 'This account is not a customer account.']);
        }

        if (!$user->is_active) {
            Auth::logout();
            return back()->withErrors(['email' => 'Your account has been suspended.']);
        }

        $request->session()->regenerate();
        return redirect()->intended(route('customer.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('customer.login');
    }
}

