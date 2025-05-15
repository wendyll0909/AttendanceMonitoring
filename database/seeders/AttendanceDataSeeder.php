<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\AttendanceBatch;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Carbon\Carbon;

class AttendanceDataSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $checkInDeadline = '08:00:00';
        $methods = ['qr_camera', 'qr_upload', 'manual'];

        // Define 10 dates across April and May 2025
        $dates = [
            '2025-04-01',
            '2025-04-05',
            '2025-04-10',
            '2025-04-15',
            '2025-04-20',
            '2025-05-01',
            '2025-05-05',
            '2025-05-10',
            '2025-05-14',
            '2025-05-20',
             Carbon::today()->toDateString(),
        ];

        // Get active employees
        $activeEmployees = Employee::where('status', 'active')->get();
        if ($activeEmployees->count() < 100) {
            throw new \Exception('Not enough active employees to seed 100 attendance records. Found: ' . $activeEmployees->count());
        }

        DB::beginTransaction();
        try {
            foreach ($dates as $batchDate) {
                // Clear existing data for the date to avoid duplicates
                Attendance::where('date', $batchDate)->delete();
                AttendanceBatch::where('batch_date', $batchDate)->delete();

                // Select 100 random active employees for attendance
                $selectedEmployees = $activeEmployees->random(100);

                foreach ($selectedEmployees as $employee) {
                    // Generate random check-in time (6:00 AM to 12:00 PM)
                    $checkInHour = $faker->numberBetween(6, 12);
                    $checkInMinute = $faker->numberBetween(0, 59);
                    $checkInSecond = $faker->numberBetween(0, 59);
                    $checkInTime = sprintf('%02d:%02d:%02d', $checkInHour, $checkInMinute, $checkInSecond);
                    $checkInCarbon = Carbon::parse($batchDate)->setTime($checkInHour, $checkInMinute, $checkInSecond);

                    // Determine late status
                    $deadlineCarbon = Carbon::parse($batchDate)->setTimeFromTimeString($checkInDeadline);
                    $isLate = $checkInCarbon->greaterThan($deadlineCarbon);

                    // Randomly decide if employee has checked out (80% chance)
                    $checkOutTime = null;
                    $checkOutMethod = null;
                    if ($faker->boolean(80)) {
                        // Generate check-out time (3:00 PM to 8:00 PM)
                        $checkOutHour = $faker->numberBetween(15, 20);
                        $checkOutMinute = $faker->numberBetween(0, 59);
                        $checkOutSecond = $faker->numberBetween(0, 59);
                        $checkOutTime = sprintf('%02d:%02d:%02d', $checkOutHour, $checkOutMinute, $checkOutSecond);
                        $checkOutMethod = $faker->randomElement($methods);
                    }

                    // Create attendance record
                    $attendance = Attendance::create([
                        'employee_id' => $employee->employee_id,
                        'date' => $batchDate,
                        'check_in_time' => $checkInTime,
                        'check_out_time' => $checkOutTime,
                        'check_in_method' => $faker->randomElement($methods),
                        'check_out_method' => $checkOutMethod,
                        'check_in_deadline' => $checkInDeadline,
                        'late_status' => $isLate,
                    ]);

                    // Create corresponding attendance batch record
                    AttendanceBatch::create([
                        'batch_date' => $batchDate,
                        'employee_id' => $employee->employee_id,
                        'check_in_time' => $attendance->check_in_time,
                        'check_out_time' => $attendance->check_out_time,
                        'check_in_method' => $attendance->check_in_method,
                        'check_out_method' => $attendance->check_out_method,
                        'check_in_deadline' => $attendance->check_in_deadline,
                        'late_status' => $attendance->late_status,
                        'absent' => false,
                    ]);
                }

                // Create batch records for absent employees
                $presentEmployeeIds = $selectedEmployees->pluck('employee_id')->toArray();
                $absentEmployees = $activeEmployees->whereNotIn('employee_id', $presentEmployeeIds);

                foreach ($absentEmployees as $employee) {
                    AttendanceBatch::create([
                        'batch_date' => $batchDate,
                        'employee_id' => $employee->employee_id,
                        'absent' => true,
                    ]);
                }

                \Log::info("Seeded 100 attendance records and corresponding batch records for date: $batchDate");
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Attendance Data Seeding Error: ' . $e->getMessage());
            throw $e;
        }
    }
}