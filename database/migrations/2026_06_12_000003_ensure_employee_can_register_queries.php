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

        $viewQueries = Permission::firstOrCreate(['name' => 'view-queries', 'guard_name' => 'web']);
        $createQueries = Permission::firstOrCreate(['name' => 'create-queries', 'guard_name' => 'web']);

        Role::where('name', 'employee')->first()?->givePermissionTo([$viewQueries, $createQueries]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
