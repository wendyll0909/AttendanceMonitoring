<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\RequestController;
use Illuminate\Support\Facades\Route;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;

Route::get('/', fn() => redirect('/dashboard'));

Route::prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Employee routes
    Route::get('employees/inactive', [EmployeeController::class, 'inactive'])->name('employees.inactive');
    Route::post('employees/{id}/archive', [EmployeeController::class, 'archive'])->name('employees.archive');
    Route::post('employees/{id}/restore', [EmployeeController::class, 'restore'])->name('employees.restore');
    Route::resource('employees', EmployeeController::class);
    
    // Position routes
    Route::get('positions/list', [PositionController::class, 'list'])->name('positions.list');
    Route::resource('positions', PositionController::class);

    // Attendance routes
    Route::get('attendance/checkin', [AttendanceController::class, 'checkin'])->name('attendance.checkin');
    Route::post('attendance/store', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::post('attendance/deadline', [AttendanceController::class, 'updateDeadline'])->name('attendance.deadline.update');
    Route::delete('attendance/{id}', [AttendanceController::class, 'destroy'])->name('attendance.destroy');
    Route::get('attendance/check/{employeeId}', [AttendanceController::class, 'check'])->name('attendance.check');
    Route::get('attendance/checkout', [AttendanceController::class, 'checkout'])->name('attendance.checkout');
    Route::post('attendance/checkout/store', [AttendanceController::class, 'checkoutStore'])->name('attendance.checkout.store');
    Route::get('attendance/record', [AttendanceController::class, 'record'])->name('attendance.record');
    Route::post('attendance/clear', [AttendanceController::class, 'clear'])->name('attendance.clear');
    Route::get('attendance/report', [AttendanceController::class, 'report'])->name('attendance.report');
    Route::get('attendance/report/pdf/{date}', [AttendanceController::class, 'exportPdf'])->name('attendance.report.pdf');
    
    // Payroll routes
    Route::get('payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::get('payroll/export/{month}', [PayrollController::class, 'exportPdf'])->name('payroll.export');
    
    // Request routes
    Route::get('requests', [RequestController::class, 'index'])->name('requests.index');
    Route::post('requests/leave', [RequestController::class, 'storeLeave'])->name('requests.leave.store');
    Route::post('requests/overtime', [RequestController::class, 'storeOvertime'])->name('requests.overtime.store');
    Route::get('requests/leave/search', [RequestController::class, 'searchLeave'])->name('requests.leave.search');
    Route::get('requests/overtime/search', [RequestController::class, 'searchOvertime'])->name('requests.overtime.search');
    Route::post('requests/leave/{id}/approve', [RequestController::class, 'approveLeave'])->name('requests.leave.approve');
    Route::post('requests/leave/{id}/reject', [RequestController::class, 'rejectLeave'])->name('requests.leave.reject');
    Route::post('requests/overtime/{id}/approve', [RequestController::class, 'approveOvertime'])->name('requests.overtime.approve');
    Route::post('requests/overtime/{id}/reject', [RequestController::class, 'rejectOvertime'])->name('requests.overtime.reject');
});



