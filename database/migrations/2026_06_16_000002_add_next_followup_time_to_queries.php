<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('queries', function (Blueprint $table) {
            if (!Schema::hasColumn('queries', 'next_followup_time')) {
                $table->time('next_followup_time')->nullable()->after('next_followup_date');
            }
        });

        if (DB::getDriverName() !== 'sqlite' && Schema::hasColumn('queries', 'query_time')) {
            Schema::table('queries', function (Blueprint $table) {
                $table->dropColumn('query_time');
            });
        }

        Schema::table('query_followups', function (Blueprint $table) {
            if (!Schema::hasColumn('query_followups', 'next_followup_time')) {
                $table->time('next_followup_time')->nullable()->after('next_followup_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('query_followups', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite' && Schema::hasColumn('query_followups', 'next_followup_time')) {
                $table->dropColumn('next_followup_time');
            }
        });

        Schema::table('queries', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite' && Schema::hasColumn('queries', 'next_followup_time')) {
                $table->dropColumn('next_followup_time');
            }

            if (!Schema::hasColumn('queries', 'query_time')) {
                $table->time('query_time')->nullable()->after('query_date');
            }
        });
    }
};
