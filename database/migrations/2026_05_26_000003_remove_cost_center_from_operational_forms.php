<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        foreach ([
            'payment_purchase_forms' => 'cost_center',
            'receipt_forms' => 'cost_center',
            'hotel_tour_forms' => 'cost_centre',
        ] as $table => $column) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                Schema::table($table, function (Blueprint $blueprint) use ($column) {
                    $blueprint->dropColumn($column);
                });
            }
        }
    }

    public function down()
    {
        foreach ([
            'payment_purchase_forms' => 'cost_center',
            'receipt_forms' => 'cost_center',
            'hotel_tour_forms' => 'cost_centre',
        ] as $table => $column) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, $column)) {
                Schema::table($table, function (Blueprint $blueprint) use ($column) {
                    $blueprint->string($column)->nullable();
                });
            }
        }
    }
};
