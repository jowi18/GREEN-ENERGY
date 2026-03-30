<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class SolarProjectDocument extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'solar_project_id',
        'uploaded_by',
        'document_type',
        'uploaded_by_role',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'description',
    ];

    // ── Relationships ─────────────────────────────────────────────────────

    public function project()
    {
        return $this->belongsTo(SolarProject::class, 'solar_project_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // ── Accessors ─────────────────────────────────────────────────────────

    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    public function getFileSizeFormattedAttribute(): string
    {
        if (!$this->file_size) return '—';
        $kb = $this->file_size / 1024;
        if ($kb < 1024) return round($kb, 1) . ' KB';
        return round($kb / 1024, 2) . ' MB';
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    public function getDocumentTypeLabelAttribute(): string
    {
        return match ($this->document_type) {
            'site_photo'              => '📷 Site Photo',
            'electric_bill'           => '💡 Electric Bill',
            'government_id'           => '🪪 Government ID',
            'lot_plan'                => '🗺️ Lot Plan',
            'barangay_clearance'      => '📋 Barangay Clearance',
            'site_survey_report'      => '📊 Site Survey Report',
            'system_design'           => '📐 System Design',
            'contract'                => '📝 Contract',
            'permit'                  => '📄 Permit',
            'completion_photo'        => '✅ Completion Photo',
            'commissioning_report'    => '⚡ Commissioning Report',
            'net_metering_certificate'=> '🔌 Net Metering Certificate',
            default                   => '📎 Document',
        };
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeByType($query, string $type)
    {
        return $query->where('document_type', $type);
    }

    public function scopeByRole($query, string $role)
    {
        return $query->where('uploaded_by_role', $role);
    }
}
