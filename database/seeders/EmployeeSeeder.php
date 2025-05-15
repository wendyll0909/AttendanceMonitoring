<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Position;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Faker\Factory as Faker;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        // Initialize Faker
        $faker = Faker::create();

        // Clear existing employees
        Employee::query()->forceDelete();

        // Ensure the qr_codes directory exists
        $qrCodeDir = public_path('qr_codes');
        if (!File::exists($qrCodeDir)) {
            File::makeDirectory($qrCodeDir, 0755, true);
        }

        // Get all positions
        $positions = Position::all()->pluck('position_id')->toArray();
        if (empty($positions)) {
            throw new \Exception('No positions found. Please seed positions first.');
        }

        for ($i = 0; $i < 500; $i++) {
            DB::beginTransaction();
            try {
                // Create employee
                $employee = Employee::create([
                    'fname' => $faker->firstName,
                    'mname' => $faker->optional(0.5)->lastName, // 50% chance of middle name
                    'lname' => $faker->lastName,
                    'address' => $faker->address,
                    'contact' => $faker->phoneNumber,
                    'hire_date' => $faker->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
                    'position_id' => $faker->randomElement($positions),
                    'status' => 'active', // Set all employees to active
                    'qr_code' => null, // Temporary null
                ]);

                // Generate QR code
                $qrCodeString = 'EMP-' . $employee->employee_id;
                $qrCodePath = 'qr_codes/' . $qrCodeString . '.png';

                $qrCode = new QrCode($qrCodeString);
                $qrCode->setEncoding(new Encoding('UTF-8'));
                $qrCode->setSize(300);
                $qrCode->setMargin(10);
                $qrCode->setForegroundColor(new Color(0, 0, 0));
                $qrCode->setBackgroundColor(new Color(255, 255, 255));

                $writer = new PngWriter();
                $result = $writer->write($qrCode);
                $result->saveToFile(public_path($qrCodePath));

                // Update employee with QR code
                $employee->update(['qr_code' => $qrCodeString]);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Employee Seeding Error: ' . $e->getMessage());
                throw $e; // Or handle as needed
            }
        }
    }
}