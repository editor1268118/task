<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('vendor_payments', function (Blueprint $table) {
            $table->string('vendor_account_no')->nullable()->after('vendor_name');
            $table->string('custom_vendor_name')->nullable()->after('vendor_account_no');
            $table->string('custom_payment_mode')->nullable()->after('payment_mode');
        });

        Schema::table('customer_receipts', function (Blueprint $table) {
            $table->string('client_type')->nullable()->after('booking_id');
            $table->string('custom_client_type')->nullable()->after('client_type');
            $table->string('client_company_name')->nullable()->after('custom_client_type');
            $table->string('contact_no')->nullable()->after('client_company_name');
            $table->string('custom_payment_mode')->nullable()->after('payment_mode');
        });
    }

    public function down()
    {
        Schema::table('vendor_payments', function (Blueprint $table) {
            $table->dropColumn(['vendor_account_no', 'custom_vendor_name', 'custom_payment_mode']);
        });

        Schema::table('customer_receipts', function (Blueprint $table) {
            $table->dropColumn(['client_type', 'custom_client_type', 'client_company_name', 'contact_no', 'custom_payment_mode']);
        });
    }
};
