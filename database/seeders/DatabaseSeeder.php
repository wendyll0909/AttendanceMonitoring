<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PositionSeeder::class,
            EmployeeSeeder::class,
            AttendanceDataSeeder::class,
            RequestSeeder::class,
        ]);
    }
}