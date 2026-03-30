<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SolarProject extends Model
{
    use SoftDeletes;

    protected $table = 'solar_projects';
    protected $fillable = [
        'project_number',
        'customer_id',
        'vendor_id',
        'assigned_engineer_id',
        'property_type',
        'system_type',
        'estimated_monthly_bill',
        'desired_system_size_kw',
        'roof_area_sqm',
        'roof_type',
        'installation_address',
        'installation_city',
        'installation_province',
        'latitude',
        'longitude',
        'customer_notes',
        'status',
        'survey_date',
        'survey_time_slot',
        'installation_date',
        'installation_time_slot',
        'activated_at',
        'turned_over_at',
        'completion_notes',
        'completion_images',
        'permit_number',
        'utility_application_number',
        'net_metering_approval_date',
        'status_history',
        'vendor_notes',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'completion_images'         => 'array',
            'status_history'            => 'array',
            'survey_date'               => 'date',
            'installation_date'         => 'date',
            'net_metering_approval_date'=> 'date',
            'activated_at'              => 'datetime',
            'turned_over_at'            => 'datetime',
            'approved_at'               => 'datetime',
            'estimated_monthly_bill'    => 'float',
            'desired_system_size_kw'    => 'float',
            'latitude'                  => 'float',
            'longitude'                 => 'float',
        ];
    }

    // ── Status pipeline ───────────────────────────────────────────────────

    public const STATUS_LABELS = [
        'inquiry_submitted'      => 'Inquiry Submitted',
        'consultation_completed' => 'Consultation Completed',
        'site_survey_scheduled'  => 'Site Survey Scheduled',
        'site_survey_completed'  => 'Site Survey Completed',
        'system_designed'        => 'System Designed',
        'quotation_generated'    => 'Quotation Generated',
        'proposal_approved'      => 'Proposal Approved',
        'proposal_rejected'      => 'Proposal Rejected',
        'contract_signed'        => 'Contract Signed',
        'permit_processing'      => 'Permit Processing',
        'equipment_prepared'     => 'Equipment Prepared',
        'installation_scheduled' => 'Installation Scheduled',
        'installation_completed' => 'Installation Completed',
        'testing_commissioning'  => 'Testing & Commissioning',
        'utility_inspection'     => 'Utility Inspection',
        'system_activated'       => 'System Activated',
        'project_turnover'       => 'Project Turnover',
        'warranty_support_active'=> 'Warranty Support Active',
        'cancelled'              => 'Cancelled',
    ];

    public const STATUS_PIPELINE = [
        'inquiry_submitted',
        'consultation_completed',
        'site_survey_scheduled',
        'site_survey_completed',
        'system_designed',
        'quotation_generated',
        'proposal_approved',
        'contract_signed',
        'permit_processing',
        'equipment_prepared',
        'installation_scheduled',
        'installation_completed',
        'testing_commissioning',
        'utility_inspection',
        'system_activated',
        'project_turnover',
        'warranty_support_active',
    ];

    public const ALLOWED_TRANSITIONS = [
        'inquiry_submitted'      => ['consultation_completed', 'cancelled'],
        'consultation_completed' => ['site_survey_scheduled', 'cancelled'],
        'site_survey_scheduled'  => ['site_survey_completed', 'cancelled'],
        'site_survey_completed'  => ['system_designed', 'cancelled'],
        'system_designed'        => ['quotation_generated', 'cancelled'],
        'quotation_generated'    => ['proposal_approved', 'proposal_rejected', 'cancelled'],
        'proposal_approved'      => ['contract_signed', 'cancelled'],
        'proposal_rejected'      => ['quotation_generated', 'cancelled'], // can re-quote
        'contract_signed'        => ['permit_processing', 'cancelled'],
        'permit_processing'      => ['equipment_prepared', 'cancelled'],
        'equipment_prepared'     => ['installation_scheduled', 'cancelled'],
        'installation_scheduled' => ['installation_completed', 'cancelled'],
        'installation_completed' => ['testing_commissioning'],
        'testing_commissioning'  => ['utility_inspection', 'system_activated'],
        'utility_inspection'     => ['system_activated'],
        'system_activated'       => ['project_turnover'],
        'project_turnover'       => ['warranty_support_active'],
        'warranty_support_active'=> [],
        'cancelled'              => [],
    ];

    // Customer-facing transitions (what customers can trigger)
    public const CUSTOMER_TRANSITIONS = [
        'quotation_generated' => ['proposal_approved', 'proposal_rejected'],
    ];

    // ── Relationships ─────────────────────────────────────────────────────

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function engineer()
    {
        return $this->belongsTo(Employee::class, 'assigned_engineer_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function quotations()
    {
        return $this->hasMany(SolarQuotation::class)->latest();
    }

    public function activeQuotation()
    {
        return $this->hasOne(SolarQuotation::class)
                    ->whereIn('status', ['sent', 'approved'])
                    ->latestOfMany();
    }

    public function documents()
    {
        return $this->hasMany(SolarProjectDocument::class);
    }

    public function customerDocuments()
    {
        return $this->hasMany(SolarProjectDocument::class)
                    ->where('uploaded_by_role', 'customer');
    }

    public function vendorDocuments()
    {
        return $this->hasMany(SolarProjectDocument::class)
                    ->where('uploaded_by_role', 'vendor');
    }

    public function contracts()
    {
        return $this->hasMany(SolarContract::class, 'solar_project_id')->latest();
    }

    public function activeContract()
    {
        return $this->hasOne(SolarContract::class, 'solar_project_id')
                    ->whereIn('status', ['sent','adjustment_requested','approved','signed'])
                    ->latestOfMany();
    }

    // ── Accessors ─────────────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match (true) {
            in_array($this->status, ['inquiry_submitted', 'consultation_completed'])
                => 'vd-badge--pending',
            in_array($this->status, ['site_survey_scheduled', 'site_survey_completed', 'system_designed'])
                => 'vd-badge--info',
            in_array($this->status, ['quotation_generated'])
                => 'vd-badge--warning',
            in_array($this->status, ['proposal_approved', 'contract_signed', 'permit_processing',
                                      'equipment_prepared', 'installation_scheduled'])
                => 'vd-badge--info',
            in_array($this->status, ['installation_completed', 'testing_commissioning',
                                      'utility_inspection', 'system_activated',
                                      'project_turnover', 'warranty_support_active'])
                => 'vd-badge--active',
            $this->status === 'proposal_rejected' => 'vd-badge--warning',
            $this->status === 'cancelled'          => 'vd-badge--danger',
            default                                => 'vd-badge--neutral',
        };
    }

    public function getPipelineIndexAttribute(): int
    {
        $idx = array_search($this->status, self::STATUS_PIPELINE);
        return $idx !== false ? $idx : 0;
    }

    public function isActive(): bool
    {
        return !in_array($this->status, ['cancelled', 'warranty_support_active']);
    }

    public function isCompleted(): bool
    {
        return in_array($this->status, ['warranty_support_active', 'project_turnover']);
    }

    // public function canTransitionTo(string $newStatus): bool
    // {
    //     return in_array($newStatus, self::ALLOWED_TRANSITIONS[$this->status] ?? []);
    // }

    public function canTransitionTo(string $newStatus): bool|string
    {
        // First check pipeline map
        if (!in_array($newStatus, self::ALLOWED_TRANSITIONS[$this->status] ?? [])) {
            return "Cannot move from \"{$this->status_label}\" to \""
                . (self::STATUS_LABELS[$newStatus] ?? $newStatus) . '".';
        }

        // ── Business rule guards ──────────────────────────────────────────────

        // Must have engineer assigned before survey or installation
        if (
            in_array($newStatus, ['site_survey_scheduled', 'installation_scheduled'])
            && !$this->assigned_engineer_id
        ) {
            return 'Please assign an engineer before scheduling.';
        }

        // Must have survey date set before marking survey completed
        if ($newStatus === 'site_survey_completed' && !$this->survey_date) {
            return 'Please schedule and complete the site survey first.';
        }

        // Must have installation date set before completing installation
        if ($newStatus === 'installation_completed' && !$this->installation_date) {
            return 'Please schedule the installation date first.';
        }

        // Must have an approved/signed contract before permit processing
        if (
            in_array($newStatus, ['permit_processing', 'equipment_prepared'])
            && !$this->contracts()->whereIn('status', ['approved', 'signed', 'active'])->exists()
        ) {
            return 'An approved contract is required before proceeding to this stage.';
        }

        // Cannot cancel once contract is signed or project is active
        if (
            $newStatus === 'cancelled'
            && in_array($this->status, [
                'contract_signed', 'permit_processing', 'equipment_prepared',
                'installation_scheduled', 'installation_completed',
                'testing_commissioning', 'utility_inspection',
                'system_activated', 'project_turnover',
            ])
        ) {
            return 'Project cannot be cancelled once a contract has been signed and work has begun. Contact support.';
        }

        return true;
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, [
            'inquiry_submitted',
            'consultation_completed',
            'site_survey_scheduled',
            'site_survey_completed',
            'system_designed',
            'quotation_generated',
            'proposal_approved',
            'proposal_rejected',
        ]);
    }

    public function transitionBlockReason(string $newStatus): ?string
    {
        $result = $this->canTransitionTo($newStatus);
        return $result === true ? null : $result;
    }

    public function customerCanTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, self::CUSTOMER_TRANSITIONS[$this->status] ?? []);
    }

    // ── Status history log ────────────────────────────────────────────────

    public function addHistoryEntry(string $status, string $notes = '', ?int $userId = null): void
    {
        $history   = $this->status_history ?? [];
        $history[] = [
            'timestamp' => now()->toISOString(),
            'status'    => $status,
            'label'     => self::STATUS_LABELS[$status] ?? $status,
            'notes'     => $notes,
            'user_id'   => $userId ?? auth()->id(),
            'user_name' => auth()->user()?->name ?? 'System',
        ];
        $this->update(['status_history' => $history]);
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeForVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['cancelled', 'warranty_support_active']);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // ── Boot ──────────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (SolarProject $sp) {
            if (empty($sp->project_number)) {
                $randomDigits = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $count = static::whereDate('created_at', today())->count() + 1;
                $sp->project_number = 'SP-' . now()->format('Ymd') . '-' .$randomDigits;
            }
        });
    }

    // protected static function booted(): void
    // {
    //     static::creating(function (Order $order) {

    //         if (empty($order->order_number)) {

    //             $randomDigits = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    //             $order->order_number = 'ORD-' . now()->format('Y') . '-' . $randomDigits;
    //         }

    //     });
    // }


}
