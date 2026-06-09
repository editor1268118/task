<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Support\Facades\Hash;

class SampleUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $engineering = Department::where('code', 'ENG')->first();
        $marketing   = Department::where('code', 'MKT')->first();
        $hr          = Department::where('code', 'HR')->first();

        $teamLead  = Designation::where('name', 'Team Lead')->first();
        $developer = Designation::where('name', 'Software Engineer')->first();
        $hrExec    = Designation::where('name', 'HR Executive')->first();

        // ─── Managers ──────────────────────────────────────────────────

        $manager1 = User::firstOrCreate(
            ['email' => 'manager1@amigostms.com'],
            [
                'employee_id'    => 'EMP0002',
                'name'           => 'Rajesh Kumar',
                'phone'          => '9876543210',
                'department_id'  => $engineering?->id,
                'designation_id' => $teamLead?->id,
                'password'       => Hash::make('password'),
                'status'         => 'active',
                'email_verified_at' => now(),
            ]
        );
        $manager1->assignRole('manager');

        $manager2 = User::firstOrCreate(
            ['email' => 'manager2@amigostms.com'],
            [
                'employee_id'    => 'EMP0003',
                'name'           => 'Priya Sharma',
                'phone'          => '9876543211',
                'department_id'  => $marketing?->id,
                'designation_id' => $teamLead?->id,
                'password'       => Hash::make('password'),
                'status'         => 'active',
                'email_verified_at' => now(),
            ]
        );
        $manager2->assignRole('manager');

        // ─── Employees ─────────────────────────────────────────────────

        $employees = [
            [
                'employee_id'    => 'EMP0004',
                'name'           => 'Amit Patel',
                'email'          => 'amit@amigostms.com',
                'phone'          => '9876543212',
                'department_id'  => $engineering?->id,
                'designation_id' => $developer?->id,
            ],
            [
                'employee_id'    => 'EMP0005',
                'name'           => 'Sneha Gupta',
                'email'          => 'sneha@amigostms.com',
                'phone'          => '9876543213',
                'department_id'  => $engineering?->id,
                'designation_id' => $developer?->id,
            ],
            [
                'employee_id'    => 'EMP0006',
                'name'           => 'Vikram Singh',
                'email'          => 'vikram@amigostms.com',
                'phone'          => '9876543214',
                'department_id'  => $marketing?->id,
                'designation_id' => $developer?->id,
            ],
            [
                'employee_id'    => 'EMP0007',
                'name'           => 'Deepa Nair',
                'email'          => 'deepa@amigostms.com',
                'phone'          => '9876543215',
                'department_id'  => $hr?->id,
                'designation_id' => $hrExec?->id,
            ],
            [
                'employee_id'    => 'EMP0008',
                'name'           => 'Arjun Mehta',
                'email'          => 'arjun@amigostms.com',
                'phone'          => '9876543216',
                'department_id'  => $engineering?->id,
                'designation_id' => $developer?->id,
            ],
        ];

        foreach ($employees as $empData) {
            $emp = User::firstOrCreate(
                ['email' => $empData['email']],
                array_merge($empData, [
                    'password'          => Hash::make('password'),
                    'status'            => 'active',
                    'email_verified_at' => now(),
                ])
            );
            $emp->assignRole('employee');
        }

        $this->command->info('Sample users seeded: 2 managers + 5 employees (all passwords: password)');
    }
}
