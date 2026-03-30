<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'icon',
        'image',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function parent()
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ProductCategory::class, 'parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isParent(): bool
    {
        return is_null($this->parent_id);
    }

    public function getFullNameAttribute(): string
    {
        return $this->parent
            ? "{$this->parent->name} › {$this->name}"
            : $this->name;
    }

    /**
     * Flat list of all categories formatted for <select> dropdowns.
     * Parents shown as optgroup-style labels with em-dashes for children.
     */
    public static function forSelect(): \Illuminate\Support\Collection
    {
        return static::with('children')
            ->active()
            ->parents()
            ->get()
            ->flatMap(function ($parent) {
                $items = collect([['id' => $parent->id, 'label' => $parent->name, 'depth' => 0]]);
                foreach ($parent->children as $child) {
                    $items->push(['id' => $child->id, 'label' => '— ' . $child->name, 'depth' => 1]);
                }
                return $items;
            });
    }
}
