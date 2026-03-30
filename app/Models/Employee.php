<?php

namespace App\Models;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'vendor_id',
        'first_name',
        'last_name',
        'phone',
        'avatar',
        'employee_code',
        'is_active',
        'hired_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'hired_at'  => 'datetime',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'employee_role')
            ->withPivot(['assigned_by', 'assigned_at'])
            ->withTimestamps();
    }

    public function hrProfile()
    {
        return $this->hasOne(\App\Models\HrEmployeeProfile::class, 'employee_id');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Return merged flat list of all permission slugs across all assigned roles.
     * Result is cached per request via a property.
     */
    public function getPermissions(): Collection
    {
        return $this->roles
            ->load('permissions')
            ->flatMap(fn ($role) => $role->permissions)
            ->unique('id')
            ->values();
    }

    public function hasPermission(string $slug): bool
    {
        return $this->getPermissionSlugs()->contains($slug);
    }

    public function hasRole(string $slug): bool
    {
        return $this->roles->contains('slug', $slug);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }


    public function getPermissionSlugs(): Collection
    {
        return $this->getPermissions()->pluck('slug');
    }

    /**
     * Get all unique MODULE names the employee has any permission for.
     * e.g. ['dashboard', 'pos', 'orders']
     */
    public function getAccessibleModules(){

        return $this->getPermissions()->pluck('module')->unique()->values();
    }

    /**
     * Does the employee have any permission in the given module?
     */
    public function canAccessModule(string $module): bool
    {
        return $this->getAccessibleModules()->contains($module);
    }


}
