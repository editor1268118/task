<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $departments = [
            ['name' => 'Engineering',       'code' => 'ENG',  'description' => 'Software development and engineering team'],
            ['name' => 'Human Resources',   'code' => 'HR',   'description' => 'Employee relations and hiring'],
            ['name' => 'Marketing',         'code' => 'MKT',  'description' => 'Marketing and brand management'],
            ['name' => 'Finance',           'code' => 'FIN',  'description' => 'Financial planning and accounting'],
            ['name' => 'Operations',        'code' => 'OPS',  'description' => 'Business operations and logistics'],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(['code' => $dept['code']], $dept);
        }

        $this->command->info('Departments seeded successfully.');
    }
}
