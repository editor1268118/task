<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaskTypeSeeder extends Seeder
{
    public function run()
    {
        $taskTypes = [
            ['name' => 'Flight', 'slug' => 'flight'],
            ['name' => 'Train', 'slug' => 'train'],
            ['name' => 'Bus', 'slug' => 'bus'],
            ['name' => 'Hotel', 'slug' => 'hotel'],
            ['name' => 'Tour Package', 'slug' => 'tour-package'],
            ['name' => 'VISA', 'slug' => 'visa'],
            ['name' => 'Cruise', 'slug' => 'cruise'],
        ];

        foreach ($taskTypes as $type) {
            DB::table('task_types')->updateOrInsert(
                ['slug' => $type['slug']],
                array_merge($type, ['is_active' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }

        $forms = [
            [
                'name' => 'hotel_tour_form',
                'slug' => 'hotel-tour',
                'display_name' => 'Hotel & Tour Package Form',
                'description' => 'Record operational booking details.',
                'form_class' => 'App\\Models\\HotelTourForm',
                'view_partial' => 'completion.partials.hotel-tour',
                'sort_order' => 1,
            ],
            [
                'name' => 'payment_purchase_form',
                'slug' => 'payment-purchase',
                'display_name' => 'Payment Purchase Form',
                'description' => 'Legacy entry form; new vendor payments use the ledger.',
                'form_class' => 'App\\Models\\PaymentPurchaseForm',
                'view_partial' => 'completion.partials.payment-purchase',
                'sort_order' => 2,
            ],
            [
                'name' => 'receipt_form',
                'slug' => 'receipt',
                'display_name' => 'Receipt Form',
                'description' => 'Legacy entry form; new receipts use the ledger.',
                'form_class' => 'App\\Models\\ReceiptForm',
                'view_partial' => 'completion.partials.receipt',
                'sort_order' => 3,
            ],
        ];

        foreach ($forms as $form) {
            DB::table('completion_forms')->updateOrInsert(
                ['slug' => $form['slug']],
                array_merge($form, ['is_active' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }

        $typeIds = DB::table('task_types')->pluck('id', 'slug');
        $formId = DB::table('completion_forms')->where('slug', 'hotel-tour')->value('id');

        DB::table('task_type_forms')->delete();
        foreach ($taskTypes as $type) {
            DB::table('task_type_forms')->insert([
                'task_type_id' => $typeIds[$type['slug']],
                'completion_form_id' => $formId,
                'sort_order' => 1,
                'is_required' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Seeded task types with operational-only completion mapping.');
    }
}
