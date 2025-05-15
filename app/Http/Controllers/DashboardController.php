<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Employee;
use App\Models\AttendanceBatch;
use App\Models\Attendance;

class DashboardController extends Controller
{
    public function index()
    {
        // Log current time for debugging
        Log::debug('Current time: ' . Carbon::now()->toDateTimeString());

        // Get current date and week boundaries
        $today = Carbon::today();
        $weekStart = Carbon::today()->startOfWeek(); // Monday
        $weekEnd = Carbon::today()->endOfWeek(); // Sunday

        // 1. Attendance Report: Average hours per week (last 4 weeks)
        $attendanceReport = [];
        for ($week = 0; $week < 4; $week++) {
            $weekStartLoop = $weekStart->copy()->subWeeks($week);
            $weekEndLoop = $weekStartLoop->copy()->endOfWeek();
            $weekNumber = $weekStartLoop->weekOfYear;

            $query = AttendanceBatch::whereBetween('batch_date', [$weekStartLoop, $weekEndLoop])
                ->whereNotNull('check_in_time')
                ->whereNotNull('check_out_time')
                ->where('absent', false);

            Log::debug('Attendance Report Query for Week ' . $weekNumber, [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings(),
            ]);

            $records = $query->get();

            $totalHours = 0;
            $employeeCount = $records->groupBy('employee_id')->count();
            
            foreach ($records as $record) {
                $checkIn = Carbon::parse($record->batch_date . ' ' . $record->check_in_time);
                $checkOut = Carbon::parse($record->batch_date . ' ' . $record->check_out_time);
                if ($checkOut->greaterThan($checkIn)) {
                    $hours = $checkOut->diffInHours($checkIn);
                    $totalHours += $hours;
                }
            }

            $avgHours = $employeeCount > 0 ? round($totalHours / $employeeCount, 1) : 0;
            
            $attendanceReport[] = [
                'week' => "Week $weekNumber",
                'avg_hours' => $avgHours,
                'employee_count' => $employeeCount,
            ];
        }
        $attendanceReport = array_reverse($attendanceReport); // Most recent first

        // 2. Top Employee: Highest hours worked this week
        $topEmployeeQuery = AttendanceBatch::whereBetween('batch_date', [$weekStart, $weekEnd])
            ->whereNotNull('check_in_time')
            ->where('absent', false)
            ->join('employees', 'attendance_batches.employee_id', '=', 'employees.employee_id')
            ->select(
                'employees.employee_id',
                DB::raw('CONCAT(employees.fname, " ", employees.lname) as full_name'),
                DB::raw('SUM(
                    TIMESTAMPDIFF(
                        HOUR,
                        CONCAT(batch_date, " ", check_in_time),
                        IFNULL(
                            CONCAT(batch_date, " ", check_out_time),
                            NOW()
                        )
                    )
                ) as total_hours')
            )
            ->groupBy('employees.employee_id', 'employees.fname', 'employees.lname')
            ->orderByDesc('total_hours');

        Log::debug('Top Employee Query', [
            'sql' => $topEmployeeQuery->toSql(),
            'bindings' => $topEmployeeQuery->getBindings(),
        ]);

        $topEmployeeRecords = $topEmployeeQuery->first();

        $topEmployee = $topEmployeeRecords ? [
            'name' => $topEmployeeRecords->full_name,
            'hours' => round($topEmployeeRecords->total_hours, 1),
        ] : ['name' => 'N/A', 'hours' => 0];

        // 3. Employee Ranking: Top 3 employees with earliest check-in today
        $rankingQuery = Attendance::where('date', $today)
            ->whereNotNull('check_in_time')
            ->join('employees', 'attendances.employee_id', '=', 'employees.employee_id')
            ->select(
                'employees.employee_id',
                DB::raw('CONCAT(employees.fname, " ", employees.lname) as full_name'),
                'attendances.check_in_time'
            )
            ->orderBy('check_in_time', 'asc')
            ->take(3);

        Log::debug('Employee Ranking Query', [
            'sql' => $rankingQuery->toSql(),
            'bindings' => $rankingQuery->getBindings(),
        ]);

        $employeeRanking = $rankingQuery->get()
            ->map(function ($record) {
                return [
                    'name' => $record->full_name,
                    'check_in_time' => Carbon::parse($record->check_in_time)->format('h:i A'),
                ];
            })->toArray();

        // 4. Present Employees: Count employees checked in today
        $presentCount = Attendance::where('date', $today)
            ->whereNotNull('check_in_time')
            ->distinct('employee_id')
            ->count();

        return view('dashboard', [
            'username' => Auth::user()->name,
            'attendanceReport' => $attendanceReport,
            'topEmployee' => $topEmployee,
            'employeeRanking' => $employeeRanking,
            'presentCount' => $presentCount,
        ]);
    }
}