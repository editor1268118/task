<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('queries', function (Blueprint $table) {
            if (!Schema::hasColumn('queries', 'adult_count')) {
                $table->unsignedInteger('adult_count')->nullable()->after('number_of_pax');
            }

            if (!Schema::hasColumn('queries', 'child_count')) {
                $table->unsignedInteger('child_count')->nullable()->after('adult_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('queries', function (Blueprint $table) {
            if (Schema::hasColumn('queries', 'child_count')) {
                $table->dropColumn('child_count');
            }

            if (Schema::hasColumn('queries', 'adult_count')) {
                $table->dropColumn('adult_count');
            }
        });
    }
};
