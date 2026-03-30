<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HrAttendance;
use Carbon\Carbon;

class HrAttendanceSeeder extends Seeder
{
    private int $vendorId    = 8;
    private int $profileId   = 3;

    // Work schedule config
    private string $timeInBase  = '08:00:00';
    private string $timeOutBase = '17:00:00'; // 8 hrs + 1 hr lunch

    public function run(): void
    {
        $months = [
            Carbon::create(2026, 3, 1), // March
            Carbon::create(2026, 4, 1), // April
            Carbon::create(2026, 5, 1), // May
        ];

        $records = [];

        foreach ($months as $monthStart) {
            $monthEnd = $monthStart->copy()->endOfMonth();
            $current  = $monthStart->copy();

            while ($current->lte($monthEnd)) {
                // Monday–Friday only
                if ($current->isWeekday()) {
                    // Small random variation: ±0–10 mins late on time-in
                    $lateMinutes = rand(0, 10);
                    $earlyOutMinutes = rand(0, 5);

                    $timeIn  = Carbon::parse($current->toDateString() . ' ' . $this->timeInBase)
                                    ->addMinutes($lateMinutes);
                    $timeOut = Carbon::parse($current->toDateString() . ' ' . $this->timeOutBase)
                                    ->subMinutes($earlyOutMinutes);

                    $hoursWorked = round($timeIn->diffInMinutes($timeOut) / 60, 2);
                    $minutesLate   = $lateMinutes;
                    $minutesUnder  = $earlyOutMinutes;
                    $minutesOT     = 0; // no OT in this schedule

                    $records[] = [
                        'vendor_id'               => $this->vendorId,
                        'hr_profile_id'           => $this->profileId,
                        'attendance_date'         => $current->toDateString(),
                        'time_in'                 => $timeIn->toDateTimeString(),
                        'time_out'                => $timeOut->toDateTimeString(),
                        'time_in_lat'             => '14.4081',   // sample coords
                        'time_in_lng'             => '121.0415',
                        'time_out_lat'            => '14.4081',
                        'time_out_lng'            => '121.0415',
                        'time_in_valid_location'  => true,
                        'time_out_valid_location' => true,
                        'minutes_late'            => $minutesLate,
                        'minutes_undertime'       => $minutesUnder,
                        'minutes_overtime'        => $minutesOT,
                        'hours_worked'            => $hoursWorked,
                        'status'                  => 'present',
                        'remarks'                 => null,
                        'is_approved'             => true,
                        'approved_by'             => null,
                        'created_at'              => now(),
                        'updated_at'              => now(),
                    ];
                }

                $current->addDay();
            }
        }

        // Chunk insert to avoid hitting query size limits
        foreach (array_chunk($records, 50) as $chunk) {
            HrAttendance::upsert(
                $chunk,
                ['vendor_id', 'hr_profile_id', 'attendance_date'], // unique keys
                ['time_in', 'time_out', 'hours_worked', 'minutes_late',
                 'minutes_undertime', 'minutes_overtime', 'is_approved', 'updated_at']
            );
        }

        $this->command->info('✅ ' . count($records) . ' attendance records seeded (Mar–May 2025).');
    }
}
