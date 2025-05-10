<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        // Delete all positions (cascades to employees and attendances)
        Position::query()->delete();

        $positions = [
            [
                'position_name' => 'Janitor',
                'description' => 'Responsible for cleaning and maintaining facilities.',
                'base_salary' => 600.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'position_name' => 'Carpenter',
                'description' => 'Builds and repairs wooden structures.',
                'base_salary' => 700.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'position_name' => 'Dog Style',
                'description' => 'Specialized role (description unclear).',
                'base_salary' => 22.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'position_name' => 'HH',
                'description' => 'Role abbreviation (description unclear).',
                'base_salary' => 1.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('positions')->insert($positions);
    }
}