<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_batches', function (Blueprint $table) {
            $table->id('batch_id');
            $table->date('batch_date');
            $table->unsignedBigInteger('employee_id');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->string('check_in_method')->nullable();
            $table->string('check_out_method')->nullable();
            $table->time('check_in_deadline')->nullable();
            $table->boolean('late_status')->default(false);
            $table->boolean('absent')->default(false);
            $table->timestamps();
            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_batches');
    }
};