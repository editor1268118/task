<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('task_type_forms') || !Schema::hasTable('completion_forms') || !Schema::hasTable('task_types')) {
            return;
        }

        $hotelTourFormId = DB::table('completion_forms')->where('slug', 'hotel-tour')->value('id');
        if (!$hotelTourFormId) {
            return;
        }

        DB::table('task_type_forms')
            ->whereIn('completion_form_id', function ($query) {
                $query->select('id')->from('completion_forms')->whereIn('slug', ['payment-purchase', 'receipt']);
            })
            ->delete();

        foreach (DB::table('task_types')->pluck('id') as $taskTypeId) {
            DB::table('task_type_forms')->updateOrInsert(
                ['task_type_id' => $taskTypeId, 'completion_form_id' => $hotelTourFormId],
                ['sort_order' => 1, 'is_required' => true, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down()
    {
        // Financial forms are no longer mandatory workflow steps; no unsafe rollback mapping is restored.
    }
};
