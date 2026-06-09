<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ─── Create Permissions ────────────────────────────────────────
        $permissions = [
            // User Management
            'manage-users',
            'create-users',
            'edit-users',
            'delete-users',
            'view-users',

            // Role Management
            'manage-roles',

            // Department Management
            'manage-departments',
            'view-departments',

            // Designation Management
            'manage-designations',
            'view-designations',

            // Task Type Management
            'manage-task-types',
            'view-task-types',

            // Task Management
            'manage-all-tasks',
            'create-tasks',
            'edit-tasks',
            'delete-tasks',
            'assign-tasks',
            'reassign-tasks',
            'view-team-tasks',
            'view-own-tasks',
            'update-task-status',
            'record-finance-entries',
            'approve-finance-closure',

            // Comments & Attachments
            'add-comments',
            'upload-files',

            // Reports
            'view-reports',
            'export-reports',

            // Settings
            'manage-settings',

            // Notifications
            'view-notifications',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // ─── Create Roles & Assign Permissions ─────────────────────────

        // Super Admin — full access
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin->givePermissionTo(Permission::all());

        // Manager — task management + team oversight
        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->givePermissionTo([
            'create-tasks',
            'edit-tasks',
            'assign-tasks',
            'reassign-tasks',
            'view-team-tasks',
            'view-own-tasks',
            'update-task-status',
            'add-comments',
            'upload-files',
            'view-departments',
            'view-designations',
            'view-reports',
            'export-reports',
            'view-notifications',
            'view-users',
        ]);

        // Employee — own tasks only
        $employee = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);
        $employee->givePermissionTo([
            'view-own-tasks',
            'update-task-status',
            'add-comments',
            'upload-files',
            'view-notifications',
        ]);

        $finance = Role::firstOrCreate(['name' => 'finance', 'guard_name' => 'web']);
        $finance->givePermissionTo([
            'view-team-tasks',
            'update-task-status',
            'record-finance-entries',
            'approve-finance-closure',
            'view-reports',
            'export-reports',
            'view-notifications',
        ]);

        $this->command->info('Roles and permissions seeded successfully.');
    }
}
