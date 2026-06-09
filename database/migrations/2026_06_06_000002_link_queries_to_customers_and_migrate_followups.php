<?php

use App\Models\Customer;
use App\Models\SalesQuery;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('queries', function (Blueprint $table) {
            if (!Schema::hasColumn('queries', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->after('id')->constrained('customers')->nullOnDelete();
                $table->index('customer_id');
            }
        });

        $this->linkQueriesToCustomers();
        $this->migrateLegacyFollowUpsToQueryFollowUps();
    }

    public function down(): void
    {
        Schema::table('queries', function (Blueprint $table) {
            if (Schema::hasColumn('queries', 'customer_id')) {
                $table->dropForeign(['customer_id']);
                $table->dropIndex(['customer_id']);
                $table->dropColumn('customer_id');
            }
        });
    }

    private function linkQueriesToCustomers(): void
    {
        foreach (DB::table('queries')->whereNull('customer_id')->orderBy('id')->get() as $query) {
            $customer = Customer::query()
                ->when($query->mobile, fn ($q) => $q->orWhere('mobile', $query->mobile))
                ->when($query->email, fn ($q) => $q->orWhere('email', $query->email))
                ->when($query->company_name, fn ($q) => $q->orWhere('company_name', $query->company_name))
                ->first();

            if (!$customer) {
                $customer = Customer::create([
                    'customer_type' => $query->company_name ? 'B2B' : 'B2C',
                    'company_name' => $query->company_name,
                    'contact_person' => $query->client_name,
                    'mobile' => $query->mobile,
                    'alternate_mobile' => $query->alternate_mobile,
                    'email' => $query->email,
                    'status' => 'Active',
                    'created_by' => $query->created_by,
                ]);
            }

            DB::table('queries')->where('id', $query->id)->update(['customer_id' => $customer->id]);
        }
    }

    private function migrateLegacyFollowUpsToQueryFollowUps(): void
    {
        if (!Schema::hasTable('follow_ups')) {
            return;
        }

        foreach (DB::table('follow_ups')->orderBy('id')->get() as $followUp) {
            $query = SalesQuery::where('customer_id', $followUp->customer_id)
                ->whereIn('status', ['Open', 'Confirmed'])
                ->latest('query_date')
                ->first();

            if (!$query) {
                continue;
            }

            $exists = DB::table('query_followups')
                ->where('query_id', $query->id)
                ->whereDate('followup_date', $followUp->followup_date)
                ->where('remarks', $followUp->notes)
                ->exists();

            if (!$exists) {
                DB::table('query_followups')->insert([
                    'query_id' => $query->id,
                    'followup_date' => $followUp->followup_date,
                    'remarks' => trim(($followUp->notes ?? '') . ' [Migrated from CRM follow-up status: ' . $followUp->status . ']'),
                    'next_followup_date' => null,
                    'created_by' => $followUp->created_by,
                    'created_at' => $followUp->created_at ?? now(),
                    'updated_at' => $followUp->updated_at ?? now(),
                ]);

                DB::table('query_activities')->insert([
                    'query_id' => $query->id,
                    'activity_at' => $followUp->created_at ?? now(),
                    'user_id' => $followUp->created_by,
                    'action' => 'Follow-Up Added',
                    'remarks' => 'Migrated from CRM follow-up',
                    'properties' => json_encode(['legacy_follow_up_id' => $followUp->id]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
};
