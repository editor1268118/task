<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        $indexExists = collect(DB::select("SHOW INDEX FROM users WHERE Key_name = 'users_email_unique'"))->isNotEmpty();

        if ($indexExists) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_email_unique');
            });
        }
    }

    public function down()
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        $duplicateEmails = DB::table('users')
            ->select('email')
            ->whereNull('deleted_at')
            ->groupBy('email')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if (! $duplicateEmails) {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('email');
            });
        }
    }
};
