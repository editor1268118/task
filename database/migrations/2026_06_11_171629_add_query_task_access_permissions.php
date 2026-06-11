<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['view-queries', 'create-queries', 'edit-queries', 'delete-queries', 'convert-queries'] as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        Role::where('name', 'super-admin')->first()?->givePermissionTo(Permission::all());
        Role::where('name', 'manager')->first()?->givePermissionTo([
            'view-queries',
            'create-queries',
            'edit-queries',
            'convert-queries',
        ]);
        Role::where('name', 'employee')->first()?->givePermissionTo([
            'view-queries',
            'create-queries',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['view-queries', 'create-queries', 'edit-queries', 'delete-queries', 'convert-queries'] as $permission) {
            Permission::where('name', $permission)->delete();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
