<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorDocument extends Model
{
    protected $fillable = [
        'vendor_id',
        'document_type',
        'document_label',
        'file_path',
        'file_original_name',
        'file_mime_type',
        'file_size',
        'review_status',
        'reviewer_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'file_size'   => 'integer',
        ];
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }

    public function getDocumentTypeLabelAttribute(): string
    {
        return match ($this->document_type) {
            'business_permit'  => 'Business Permit',
            'government_id'    => 'Government ID',
            'proof_of_address' => 'Proof of Address',
            'sme_certificate'  => 'SME Certificate',
            'dti_registration' => 'DTI Registration',
            'sec_registration' => 'SEC Registration',
            'bir_registration' => 'BIR Registration',
            default            => ucwords(str_replace('_', ' ', $this->document_type)),
        };
    }

    public function isPdf(): bool
    {
        return $this->file_mime_type === 'application/pdf';
    }

    public function isImage(): bool
    {
        return str_starts_with($this->file_mime_type, 'image/');
    }
}
