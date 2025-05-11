<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\AttendanceController;
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
    Route::delete('attendance/{id}', [AttendanceController::class, 'destroy'])->name('attendance.destroy');
    Route::get('attendance/check/{employeeId}', [AttendanceController::class, 'check'])->name('attendance.check');
    Route::get('attendance/checkout', [AttendanceController::class, 'checkout'])->name('attendance.checkout');
    Route::post('attendance/checkout/store', [AttendanceController::class, 'checkoutStore'])->name('attendance.checkout.store');

// Requests routes
    Route::get('requests', [RequestController::class, 'index'])->name('requests.index');
    
    // Leave Requests routes
    Route::get('leave-requests/create', [RequestController::class, 'createLeaveRequest'])
        ->name('leave-requests.create');
    Route::post('leave-requests', [RequestController::class, 'storeLeaveRequest'])
        ->name('leave-requests.store');
    Route::post('leave-requests/{id}/approve', [RequestController::class, 'approveLeaveRequest'])
        ->name('leave-requests.approve');
    Route::post('leave-requests/{id}/reject', [RequestController::class, 'rejectLeaveRequest'])
        ->name('leave-requests.reject');
    
    // Overtime Requests routes
    Route::get('overtime-requests/create', [RequestController::class, 'createOvertimeRequest'])
        ->name('overtime-requests.create');
    Route::post('overtime-requests', [RequestController::class, 'storeOvertimeRequest'])
        ->name('overtime-requests.store');
    Route::post('overtime-requests/{id}/approve', [RequestController::class, 'approveOvertimeRequest'])
        ->name('overtime-requests.approve');
    Route::post('overtime-requests/{id}/reject', [RequestController::class, 'rejectOvertimeRequest'])
        ->name('overtime-requests.reject');
        Route::get('requests', [RequestController::class, 'index'])->name('requests.index');
Route::get('leave-requests/create', [RequestController::class, 'createLeaveRequest'])->name('leave-requests.create');
Route::get('overtime-requests/create', [RequestController::class, 'createOvertimeRequest'])->name('overtime-requests.create');
});

Route::get('/check-db', function() {
    try {
        return response()->json([
            'employees_exists' => Schema::hasTable('employees'),
            'positions_exists' => Schema::hasTable('positions'),
            'employees_columns' => Schema::getColumnListing('employees'),
            'db_connection' => DB::connection()->getPdo() ? 'OK' : 'Failed'
        ]);
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});

Route::get('/qr_codes/{code}.png', [EmployeeController::class, 'serveQrCode'])->name('qr.serve');

Route::get('/test-qr', function() {
    try {
        // Create QR code
        $qrCode = new QrCode('TEST');
        $qrCode->setEncoding(new Encoding('UTF-8'));
        $qrCode->setSize(300);
        $qrCode->setMargin(10);
        $qrCode->setForegroundColor(new Color(0, 0, 0));
        $qrCode->setBackgroundColor(new Color(255, 255, 255));

        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        
        // Save to file
        $filePath = public_path('qr_codes/test.png');
        $result->saveToFile($filePath);
        
        return response()->file($filePath);
    } catch (\Exception $e) {
        \Log::error('QR Test Failed: '.$e->getMessage());
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});
?>