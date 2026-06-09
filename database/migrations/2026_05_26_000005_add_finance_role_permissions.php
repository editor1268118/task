<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('roles') || !Schema::hasTable('permissions') || !Schema::hasTable('role_has_permissions')) {
            return;
        }

        $permissionIds = [];
        foreach (['view-team-tasks', 'update-task-status', 'view-reports', 'export-reports', 'view-notifications', 'record-finance-entries', 'approve-finance-closure'] as $name) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $name, 'guard_name' => 'web'],
                ['updated_at' => now(), 'created_at' => now()]
            );
            $permissionIds[] = DB::table('permissions')->where(['name' => $name, 'guard_name' => 'web'])->value('id');
        }

        DB::table('roles')->updateOrInsert(
            ['name' => 'finance', 'guard_name' => 'web'],
            ['updated_at' => now(), 'created_at' => now()]
        );
        $financeRoleId = DB::table('roles')->where(['name' => 'finance', 'guard_name' => 'web'])->value('id');

        foreach ($permissionIds as $permissionId) {
            DB::table('role_has_permissions')->updateOrInsert([
                'permission_id' => $permissionId,
                'role_id' => $financeRoleId,
            ]);
        }
    }

    public function down()
    {
        // Roles may have been assigned to real users, so rollback does not remove them.
    }
};
