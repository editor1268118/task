<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@amigostms.com'],
            [
                'employee_id'   => 'EMP0001',
                'name'          => 'Super Admin',
                'phone'         => '9999999999',
                'department_id' => Department::where('code', 'ENG')->first()?->id,
                'password'      => Hash::make('password'),
                'status'        => 'active',
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('super-admin');

        $this->command->info('Super Admin user created: admin@amigostms.com / password');
    }
}
