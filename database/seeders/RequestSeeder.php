<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Carbon\Carbon;

class RequestSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Define dates to align with AttendanceDataSeeder
        $dates = [
            '2025-04-01', '2025-04-05', '2025-04-10', '2025-04-15', '2025-04-20',
            '2025-05-01', '2025-05-05', '2025-05-10', '2025-05-14', '2025-05-20',
        ];

        // Get active employees
        $activeEmployees = Employee::with('position')->where('status', 'active')->get();
        if ($activeEmployees->count() < 100) {
            throw new \Exception('Not enough active employees to seed 100 requests. Found: ' . $activeEmployees->count());
        }

        DB::beginTransaction();
        try {
            // Seed 100 Leave Requests
            for ($i = 0; $i < 100; $i++) {
                $employee = $activeEmployees->random();
                $startDate = $faker->randomElement($dates);
                $startCarbon = Carbon::parse($startDate);
                $endCarbon = $startCarbon->copy()->addDays($faker->numberBetween(1, 5));

                LeaveRequest::create([
                    'employee_id' => $employee->employee_id,
                    'start_date' => $startCarbon->toDateString(),
                    'end_date' => $endCarbon->toDateString(),
                    'reason' => $faker->sentence(10),
                    'status' => $faker->randomElement([
                        LeaveRequest::STATUS_PENDING,
                        LeaveRequest::STATUS_APPROVED,
                        LeaveRequest::STATUS_REJECTED
                    ]),
                ]);
            }

            // Seed 100 Overtime Requests
            for ($i = 0; $i < 100; $i++) {
                $employee = $activeEmployees->random();
                $baseSalary = $employee->position ? $employee->position->base_salary : 500; // Fallback salary
                $overtimeRate = ($baseSalary / 8) * 1.25; // Same logic as RequestController

                $date = $faker->randomElement($dates);
                $startHour = $faker->numberBetween(17, 19); // Start between 5 PM and 7 PM
                $endHour = $faker->numberBetween($startHour + 1, 22); // End between start time and 10 PM
                $startTime = Carbon::parse($date)->setTime($startHour, 0);
                $endTime = Carbon::parse($date)->setTime($endHour, 0);

                OvertimeRequest::create([
                    'employee_id' => $employee->employee_id,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'reason' => $faker->sentence(10),
                    'status' => $faker->randomElement([
                        OvertimeRequest::STATUS_PENDING,
                        OvertimeRequest::STATUS_APPROVED,
                        OvertimeRequest::STATUS_REJECTED
                    ]),
                    'overtime_rate' => $overtimeRate,
                ]);
            }

            \Log::info('Seeded 100 leave requests and 100 overtime requests.');
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Request Seeding Error: ' . $e->getMessage());
            throw $e;
        }
    }
}