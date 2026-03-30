<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'vendor_id',
        'name',
        'slug',
        'description',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission')
            ->withTimestamps();
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_role')
            ->withPivot(['assigned_by', 'assigned_at'])
            ->withTimestamps();
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    /** Global/system roles (not vendor-specific) */
    public function scopeSystem($query)
    {
        return $query->whereNull('vendor_id')->where('is_system', true);
    }

    /** Roles belonging to a specific vendor */
    public function scopeForVendor($query, int $vendorId)
    {
        return $query->where(function ($q) use ($vendorId) {
            $q->whereNull('vendor_id')
              ->orWhere('vendor_id', $vendorId);
        });
    }
}
