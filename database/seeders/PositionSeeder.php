<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        // Initialize Faker
        $faker = Faker::create();

        // Delete all positions (cascades to employees and attendances)
        Position::query()->delete();

        $positions = [];
        $usedNames = []; // Track used position names to ensure uniqueness
        $positionNames = [
    'Construction Engineer', 'Site Engineer', 'Project Manager', 'Construction Supervisor', 
    'Quality Control Analyst', 'Construction Coordinator', 'Construction Administrator', 
    'Building Developer', 'Architect', 'Structural Designer', 'Construction Consultant', 
    'Construction Assistant', 'Field Specialist', 'Site Clerk', 'Construction Officer', 
    'Project Planner', 'Surveyor', 'Heavy Equipment Operator', 'Site Inspector', 'Safety Advisor', 
    'Cost Estimator', 'Construction Accountant', 'Auditor', 'Construction Salesperson', 
    'Construction Marketer', 'Construction Trainer', 'Construction Recruiter', 'Truck Driver', 
    'Mechanic', 'Carpenter', 'Electrician', 'Plumber', 'Welder', 'Painter', 'Mason', 
    'Laborer', 'Construction Laborer', 'Janitor', 'Security Guard', 'Receptionist', 
    'Site Secretary', 'Equipment Operator', 'Concrete Specialist', 'Steel Fabricator', 
    'Construction Labor Supervisor', 'Safety Officer', 'Construction Foreman', 'Landscaper', 
    'Environmental Health and Safety (EHS) Specialist', 'Geotechnical Engineer', 'Building Inspector', 
    'Construction Planner', 'BIM Coordinator', 'Land Surveyor', 'Estimator', 'Project Scheduler'
];


        $prefixes = ['Senior ', 'Junior ', 'Lead ', '', 'Associate ']; // Added more prefixes

        $targetCount = 100;
        $generatedCount = 0;

        while ($generatedCount < $targetCount) {
            $baseName = $faker->randomElement($positionNames);
            $prefix = $faker->randomElement($prefixes);
            $positionName = $prefix . $baseName;

            // Skip if position name already used
            if (in_array($positionName, $usedNames)) {
                continue;
            }

            $usedNames[] = $positionName;
            $positions[] = [
                'position_name' => $positionName,
                'description' => $faker->sentence(10),
                'base_salary' => $faker->numberBetween(300, 3000) + $faker->randomFloat(2, 0, 99),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $generatedCount++;
        }

        DB::table('positions')->insert($positions);
    }
}