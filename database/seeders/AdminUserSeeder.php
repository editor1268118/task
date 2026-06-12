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
        $email = env('SUPER_ADMIN_EMAIL', 'admin@amigostms.com');
        $password = env('SUPER_ADMIN_PASSWORD', 'password');

        $admin = User::where('email', $email)
            ->orWhere('employee_id', 'EMP0001')
            ->first();

        if (! $admin) {
            $admin = new User([
                'employee_id' => 'EMP0001',
            ]);
        }

        $admin->forceFill([
            'employee_id' => $admin->employee_id ?: 'EMP0001',
            'name' => $admin->name ?: 'Super Admin',
            'email' => $email,
            'phone' => $admin->phone ?: '9999999999',
            'department_id' => $admin->department_id ?: Department::where('code', 'ENG')->first()?->id,
            'password' => Hash::make($password),
            'status' => 'active',
            'email_verified_at' => $admin->email_verified_at ?: now(),
        ])->save();

        $admin->assignRole('super-admin');

        $this->command->info("Super Admin user ensured: {$email}");
    }
}
