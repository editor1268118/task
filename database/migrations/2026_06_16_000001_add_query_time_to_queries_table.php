<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('queries', function (Blueprint $table) {
            if (!Schema::hasColumn('queries', 'query_time')) {
                $table->time('query_time')->nullable()->after('query_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('queries', function (Blueprint $table) {
            if (Schema::hasColumn('queries', 'query_time')) {
                $table->dropColumn('query_time');
            }
        });
    }
};
