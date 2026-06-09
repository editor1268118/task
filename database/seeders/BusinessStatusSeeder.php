<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BusinessStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $statuses = [
            ['name' => 'Follow Up', 'slug' => 'follow_up', 'color' => '#0dcaf0'],
            ['name' => 'Quotation Sent', 'slug' => 'quotation_sent', 'color' => '#ffc107'],
            ['name' => 'Waiting for Client', 'slug' => 'waiting_for_client', 'color' => '#6c757d'],
            ['name' => 'Waiting for Payment', 'slug' => 'waiting_for_payment', 'color' => '#fd7e14'],
            ['name' => 'Booking In Process', 'slug' => 'booking_in_process', 'color' => '#0d6efd'],
            ['name' => 'Booking Confirmed', 'slug' => 'booking_confirmed', 'color' => '#20c997'],
            ['name' => 'Refund Initiated', 'slug' => 'refund_initiated', 'color' => '#dc3545'],
            ['name' => 'Refund Requested', 'slug' => 'refund_requested', 'color' => '#dc3545'],
            ['name' => 'Refund Processed', 'slug' => 'refund_processed', 'color' => '#198754'],
            ['name' => 'Ticket Issued', 'slug' => 'ticket_issued', 'color' => '#198754'],
            ['name' => 'Visa Submitted', 'slug' => 'visa_submitted', 'color' => '#0d6efd'],
            ['name' => 'Hotel Confirmed', 'slug' => 'hotel_confirmed', 'color' => '#20c997'],
            ['name' => 'Voucher Sent', 'slug' => 'voucher_sent', 'color' => '#198754'],
            ['name' => 'Client Not Responding', 'slug' => 'client_not_responding', 'color' => '#6c757d'],
            ['name' => 'Payment Received', 'slug' => 'payment_received', 'color' => '#198754'],
        ];

        foreach ($statuses as $status) {
            \App\Models\BusinessStatus::updateOrCreate(
                ['slug' => $status['slug']],
                $status
            );
        }
    }
}
