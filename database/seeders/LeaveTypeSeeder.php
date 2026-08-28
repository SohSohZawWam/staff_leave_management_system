<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $leaveTypes = [
            [
                'name' => 'Annual Leave',
                'code' => 'ANNUAL',
                'description' => 'Annual vacation leave',
                'annual_allocation' => 30,
                'requires_attachment' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Medical Leave',
                'code' => 'MEDICAL',
                'description' => 'Leave for medical reasons',
                'annual_allocation' => 15,
                'requires_attachment' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Casual Leave',
                'code' => 'CASUAL',
                'description' => 'Casual leave for personal matters',
                'annual_allocation' => 10,
                'requires_attachment' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Study Leave',
                'code' => 'STUDY',
                'description' => 'Leave for academic pursuits',
                'annual_allocation' => 20,
                'requires_attachment' => true,
                'is_active' => true,
            ],
        ];

        foreach ($leaveTypes as $type) {
            LeaveType::create($type);
        }
    }
}
