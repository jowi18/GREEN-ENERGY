<?php
/**
 * HR Module Migrations
 * Run each Schema::create() in sequence.
 * File: database/migrations/2026_01_01_000032_create_hr_tables.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Departments ───────────────────────────────────────────────
        Schema::create('hr_departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('description', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // ── 2. Positions ─────────────────────────────────────────────────
        Schema::create('hr_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('hr_departments')->nullOnDelete();
            $table->string('title', 100);
            $table->text('description')->nullable();
            $table->decimal('base_salary_min', 10, 2)->nullable();
            $table->decimal('base_salary_max', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── 3. Extended employee profiles (links to existing employees) ───
        Schema::create('hr_employee_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('hr_departments')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('hr_positions')->nullOnDelete();

            // Personal details
            $table->string('employee_number', 30)->nullable();
            $table->string('first_name', 80);
            $table->string('middle_name', 80)->nullable();
            $table->string('last_name', 80);
            $table->string('suffix', 10)->nullable();
            $table->enum('sex', ['male', 'female'])->nullable();
            $table->date('birth_date')->nullable();
            $table->string('birth_place', 150)->nullable();
            $table->enum('civil_status', ['single','married','widowed','separated','divorced'])->nullable();
            $table->string('citizenship', 80)->nullable();
            $table->string('blood_type', 5)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('personal_email', 120)->nullable();

            // Address
            $table->text('address_permanent')->nullable();
            $table->text('address_present')->nullable();

            // Employment details
            $table->date('date_hired')->nullable();
            $table->date('date_regularized')->nullable();
            $table->date('date_separated')->nullable();
            $table->enum('employment_status', ['probationary','regular','contractual','part_time','resigned','terminated'])->default('probationary');
            $table->enum('employment_type', ['full_time','part_time','project_based','seasonal'])->default('full_time');
            $table->integer('work_hours_per_day')->default(8);
            $table->integer('work_days_per_week')->default(5);

            // Compensation
            $table->decimal('monthly_rate', 10, 2)->nullable();
            $table->decimal('daily_rate', 10, 2)->nullable();    // auto-computed
            $table->decimal('hourly_rate', 10, 2)->nullable();   // auto-computed
            $table->enum('pay_frequency', ['semi_monthly','monthly'])->default('semi_monthly');

            // Allowances (stored as JSON)
            $table->json('allowances')->nullable();

            // Government IDs
            $table->string('sss_number', 30)->nullable();
            $table->string('philhealth_number', 30)->nullable();
            $table->string('pagibig_number', 30)->nullable();
            $table->string('tin_number', 30)->nullable();

            // Misc
            $table->string('profile_photo_path', 500)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_archived')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['vendor_id', 'employee_id']);
            $table->index(['vendor_id', 'employment_status']);
        });

        // ── 4. Emergency contacts ────────────────────────────────────────
        Schema::create('hr_emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hr_profile_id')->constrained('hr_employee_profiles')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('relationship', 60)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('address', 255)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        // ── 5. Employment history ────────────────────────────────────────
        Schema::create('hr_employment_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hr_profile_id')->constrained('hr_employee_profiles')->cascadeOnDelete();
            $table->string('company', 150);
            $table->string('position', 100)->nullable();
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->text('reason_for_leaving')->nullable();
            $table->timestamps();
        });

        // ── 6. Attendance settings (per vendor) ──────────────────────────
        Schema::create('hr_attendance_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->time('work_start')->default('08:00');
            $table->time('work_end')->default('17:00');
            $table->integer('grace_period_minutes')->default(15);
            $table->integer('overtime_threshold_minutes')->default(30);
            $table->decimal('late_deduction_rate', 6, 4)->default(1.0);  // multiplier on hourly rate
            $table->decimal('overtime_rate_multiplier', 4, 2)->default(1.25);
            $table->decimal('holiday_rate_multiplier', 4, 2)->default(2.0);
            // Location fence
            $table->decimal('office_latitude',  10, 7)->nullable();
            $table->decimal('office_longitude', 10, 7)->nullable();
            $table->integer('location_radius_meters')->default(200);
            $table->boolean('require_location')->default(false);
            $table->timestamps();
        });

        // ── 7. Attendance records ────────────────────────────────────────
        Schema::create('hr_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('hr_profile_id')->constrained('hr_employee_profiles')->cascadeOnDelete();
            $table->date('attendance_date');
            $table->timestamp('time_in')->nullable();
            $table->timestamp('time_out')->nullable();
            $table->decimal('time_in_lat',  10, 7)->nullable();
            $table->decimal('time_in_lng',  10, 7)->nullable();
            $table->decimal('time_out_lat', 10, 7)->nullable();
            $table->decimal('time_out_lng', 10, 7)->nullable();
            $table->boolean('time_in_valid_location')->default(true);
            $table->boolean('time_out_valid_location')->default(true);
            $table->integer('minutes_late')->default(0);
            $table->integer('minutes_undertime')->default(0);
            $table->integer('minutes_overtime')->default(0);
            $table->decimal('hours_worked', 5, 2)->default(0);
            $table->enum('status', ['present','absent','half_day','holiday','leave','rest_day'])->default('present');
            $table->text('remarks')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['hr_profile_id', 'attendance_date']);
            $table->index(['vendor_id', 'attendance_date']);
        });

        // ── 8. Holidays ──────────────────────────────────────────────────
        Schema::create('hr_holidays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->string('name', 100);
            $table->date('holiday_date');
            $table->enum('type', ['regular','special_non_working','special_working'])->default('regular');
            $table->timestamps();
            $table->unique(['vendor_id', 'holiday_date']);
        });

        // ── 9. Leave types ───────────────────────────────────────────────
        Schema::create('hr_leave_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->string('name', 80);          // e.g. "Vacation Leave"
            $table->string('code', 20)->nullable(); // VL, SL, EL, ML, PL
            $table->integer('days_per_year')->default(0);
            $table->boolean('is_paid')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── 10. Leave requests ───────────────────────────────────────────
        Schema::create('hr_leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('hr_profile_id')->constrained('hr_employee_profiles')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('hr_leave_types')->cascadeOnDelete();
            $table->date('date_from');
            $table->date('date_to');
            $table->integer('days_requested');
            $table->text('reason')->nullable();
            $table->enum('status', ['pending','approved','rejected','cancelled'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approver_notes')->nullable();
            $table->timestamps();
            $table->index(['vendor_id', 'status']);
        });

        // ── 11. Overtime requests ────────────────────────────────────────
        Schema::create('hr_overtime_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('hr_profile_id')->constrained('hr_employee_profiles')->cascadeOnDelete();
            $table->date('overtime_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('hours_requested', 4, 2);
            $table->text('reason')->nullable();
            $table->enum('status', ['pending','approved','rejected','cancelled'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approver_notes')->nullable();
            $table->timestamps();
        });

        // ── 12. Payroll periods ──────────────────────────────────────────
        Schema::create('hr_payroll_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->string('period_name', 100);   // e.g. "January 1–15, 2026"
            $table->date('date_from');
            $table->date('date_to');
            $table->enum('frequency', ['semi_monthly','monthly'])->default('semi_monthly');
            $table->enum('status', ['draft','pending_approval','approved','rejected','paid'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['vendor_id', 'status']);
        });

        // ── 13. Payroll items (one row per employee per period) ──────────
        Schema::create('hr_payroll_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_period_id')->constrained('hr_payroll_periods')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('hr_profile_id')->constrained('hr_employee_profiles')->cascadeOnDelete();

            // Earnings
            $table->decimal('basic_pay', 10, 2)->default(0);
            $table->decimal('overtime_pay', 10, 2)->default(0);
            $table->decimal('holiday_pay', 10, 2)->default(0);
            $table->decimal('allowances', 10, 2)->default(0);
            $table->decimal('other_earnings', 10, 2)->default(0);
            $table->decimal('gross_pay', 10, 2)->default(0);

            // Deductions
            $table->decimal('late_deduction', 10, 2)->default(0);
            $table->decimal('undertime_deduction', 10, 2)->default(0);
            $table->decimal('absent_deduction', 10, 2)->default(0);
            $table->decimal('sss_contribution', 10, 2)->default(0);
            $table->decimal('philhealth_contribution', 10, 2)->default(0);
            $table->decimal('pagibig_contribution', 10, 2)->default(0);
            $table->decimal('withholding_tax', 10, 2)->default(0);
            $table->decimal('other_deductions', 10, 2)->default(0);
            $table->decimal('total_deductions', 10, 2)->default(0);

            // Net
            $table->decimal('net_pay', 10, 2)->default(0);

            // Attendance summary
            $table->integer('days_worked')->default(0);
            $table->integer('days_absent')->default(0);
            $table->integer('days_leave')->default(0);
            $table->decimal('hours_overtime', 5, 2)->default(0);
            $table->integer('minutes_late')->default(0);
            $table->integer('minutes_undertime')->default(0);

            // Status
            $table->enum('status', ['draft','final','paid'])->default('draft');
            $table->json('computation_details')->nullable(); // detailed breakdown

            $table->timestamps();
            $table->unique(['payroll_period_id', 'hr_profile_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_payroll_items');
        Schema::dropIfExists('hr_payroll_periods');
        Schema::dropIfExists('hr_overtime_requests');
        Schema::dropIfExists('hr_leave_requests');
        Schema::dropIfExists('hr_leave_types');
        Schema::dropIfExists('hr_holidays');
        Schema::dropIfExists('hr_attendance');
        Schema::dropIfExists('hr_attendance_settings');
        Schema::dropIfExists('hr_employment_history');
        Schema::dropIfExists('hr_emergency_contacts');
        Schema::dropIfExists('hr_employee_profiles');
        Schema::dropIfExists('hr_positions');
        Schema::dropIfExists('hr_departments');
    }
};
