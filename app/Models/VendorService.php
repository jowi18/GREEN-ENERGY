<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class VendorService extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'vendor_id', 'name', 'slug', 'service_type',
        'short_description', 'description',
        'pricing_type', 'price', 'price_max', 'currency',
        'duration_hours_min', 'duration_hours_max',
        'is_active', 'is_featured',
        'coverage_notes', 'inclusions', 'requirements',
        'sort_order',
    ];

    protected $casts = [
        'price'     => 'decimal:2',
        'price_max' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured'=> 'boolean',
    ];

    // ── Auto-slug ─────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (self $vs) {
            if (! $vs->slug) {
                $base  = Str::slug($vs->name);
                $slug  = $base;
                $count = 1;
                while (static::where('vendor_id', $vs->vendor_id)->where('slug', $slug)->exists()) {
                    $slug = $base . '-' . $count++;
                }
                $vs->slug = $slug;
            }
        });
    }

    // ── Relationships ─────────────────────────────────────────────────────

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeForVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // ── Accessors ─────────────────────────────────────────────────────────

    public function getPriceDisplayAttribute(): string
    {
        if (! $this->price) {
            return 'Contact for quote';
        }

        $formatted = '₱' . number_format($this->price, 2);

        return match($this->pricing_type) {
            'fixed'          => $formatted,
            'starting_from'  => 'From ' . $formatted,
            'per_hour'       => $formatted . '/hr',
            'custom_quote'   => 'Custom Quote',
            default          => $formatted,
        };
    }

    public function getDurationDisplayAttribute(): ?string
    {
        if (! $this->duration_hours_min) return null;

        if (! $this->duration_hours_max || $this->duration_hours_min === $this->duration_hours_max) {
            return $this->duration_hours_min . ' hr' . ($this->duration_hours_min > 1 ? 's' : '');
        }

        return $this->duration_hours_min . '–' . $this->duration_hours_max . ' hrs';
    }

    public function getServiceTypeLabelAttribute(): string
    {
        $types = [
            'installation'   => 'Installation',
            'maintenance'    => 'Maintenance',
            'repair'         => 'Repair',
            'inspection'     => 'Inspection',
            'consultation'   => 'Consultation',
            'cleaning'       => 'Cleaning',
            'upgrade'        => 'Upgrade',
            'warranty_claim' => 'Warranty Claim',
            'other'          => 'Other',
        ];

        return $types[$this->service_type] ?? ucfirst($this->service_type);
    }

    public function getInclusionsListAttribute(): array
    {
        if (! $this->inclusions) return [];
        return array_filter(array_map('trim', explode("\n", $this->inclusions)));
    }

    public function getRequirementsListAttribute(): array
    {
        if (! $this->requirements) return [];
        return array_filter(array_map('trim', explode("\n", $this->requirements)));
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public static function serviceTypes(): array
    {
        return [
            'installation'   => 'Installation',
            'maintenance'    => 'Preventive Maintenance',
            'repair'         => 'Repair & Troubleshooting',
            'inspection'     => 'System Inspection',
            'consultation'   => 'Energy Consultation',
            'cleaning'       => 'Panel Cleaning',
            'upgrade'        => 'System Upgrade',
            'warranty_claim' => 'Warranty Claim Service',
            'other'          => 'Other Service',
        ];
    }
}
