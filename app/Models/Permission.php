<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'module',
        'description',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission')
            ->withTimestamps();
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeForModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    /**
     * Return all permissions keyed by module for the permissions management UI.
     * e.g. ['inventory' => Collection, 'pos' => Collection, ...]
     */
    public static function groupedByModule(): \Illuminate\Support\Collection
    {
        return static::orderBy('module')->orderBy('name')->get()
            ->groupBy('module');
    }
}
