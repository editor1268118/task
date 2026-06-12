<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        $database = DB::getDatabaseName();

        $indexExists = DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', 'users')
            ->where('index_name', 'users_email_unique')
            ->exists();

        if ($indexExists) {
            DB::statement('ALTER TABLE users DROP INDEX users_email_unique');
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
            DB::statement('ALTER TABLE users ADD UNIQUE users_email_unique (email)');
        }
    }
};
