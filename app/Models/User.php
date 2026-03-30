<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }
    // ── Relationships ──────────────────────────────────────────────────────

    public function admin()
    {
        return $this->hasOne(Admin::class);
    }

    public function vendor()
    {
        return $this->hasOne(Vendor::class);
    }

    public function customer()
    {
        return $this->hasOne(Customer::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function supplier()
    {
        return $this->hasOne(Supplier::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->user_type === 'admin';
    }

    public function isVendor(): bool
    {
        return $this->user_type === 'vendor';
    }

    public function isCustomer(): bool
    {
        return $this->user_type === 'customer';
    }
    public function isSupplier(): bool
    {
        return $this->user_type === 'supplier';
    }

    public function isEmployee(): bool
    {
        return $this->user_type === 'employee';
    }

    public function getProfileUrl(): string
    {
        return match ($this->user_type) {
            'admin'    => route('admin.dashboard'),
            'vendor'   => route('vendor.dashboard'),
            'customer' => route('customer.dashboard'),
            'employee' => route('vendor.dashboard'),
            'supplier' => route('supplier.dashboard'),
            default    => '/',
        };
    }
}
