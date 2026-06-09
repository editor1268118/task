<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Designation;

class DesignationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $designations = [
            ['name' => 'Software Engineer',    'description' => 'Develops and maintains software applications'],
            ['name' => 'Senior Developer',     'description' => 'Senior-level software development role'],
            ['name' => 'Team Lead',            'description' => 'Leads a team of developers or employees'],
            ['name' => 'Project Manager',      'description' => 'Manages project timelines and deliverables'],
            ['name' => 'HR Executive',         'description' => 'Human resources administration and support'],
        ];

        foreach ($designations as $designation) {
            Designation::firstOrCreate(['name' => $designation['name']], $designation);
        }

        $this->command->info('Designations seeded successfully.');
    }
}
