<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerReceipt;

class CustomerReportController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Customer::class);

        $topCustomers = Customer::withCount('tasks')->withSum('bookings as sales_total', 'sale_amount')
            ->orderByDesc('sales_total')
            ->take(10)
            ->get();

        $mostActiveCustomers = Customer::withCount(['tasks', 'interactions'])
            ->orderByDesc('interactions_count')
            ->take(10)
            ->get();

        $outstandingCustomers = Customer::with('bookings.receipts')
            ->get()
            ->map(function (Customer $customer) {
                $sale = (float) $customer->bookings->sum('sale_amount');
                $received = (float) $customer->bookings->sum(fn ($booking) => $booking->receipts->whereIn('receipt_status', CustomerReceipt::APPROVED_STATUSES)->sum('amount_received'));
                $customer->outstanding = max(0, $sale - $received);
                return $customer;
            })
            ->where('outstanding', '>', 0)
            ->sortByDesc('outstanding')
            ->take(10);

        $customerStats = [
            'total_customers' => Customer::count(),
            'repeat_customers' => Customer::has('tasks', '>', 1)->count(),
            'customers_with_queries' => Customer::has('queries')->count(),
        ];

        return view('crm.reports.index', compact('topCustomers', 'mostActiveCustomers', 'outstandingCustomers', 'customerStats'));
    }
}
