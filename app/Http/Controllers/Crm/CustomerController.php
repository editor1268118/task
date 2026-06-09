<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerReceipt;
use App\Models\CustomerDocument;
use App\Models\CustomerInteraction;
use App\Models\User;
use App\Services\CustomerCrmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function __construct(protected CustomerCrmService $customerCrmService)
    {
        $this->authorizeResource(Customer::class, 'customer');
    }

    public function index(Request $request)
    {
        $query = Customer::withCount(['tasks', 'queries']);
        $user = Auth::user();

        if ($user->hasRole('employee')) {
            $query->where(function ($q) use ($user) {
                $q->whereHas('tasks', fn ($sub) => $sub->where('assigned_to', $user->id))
                    ->orWhereHas('queries', fn ($sub) => $sub->where('assigned_to', $user->id));
            });
        } elseif ($user->hasRole('manager')) {
            $teamIds = User::where('department_id', $user->department_id)->pluck('id');
            $query->where(function ($q) use ($user, $teamIds) {
                $q->whereHas('tasks', fn ($sub) => $sub->where('department_id', $user->department_id))
                    ->orWhereHas('queries', fn ($sub) => $sub->whereIn('assigned_to', $teamIds));
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_code', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $customers = $query->latest()->paginate(15)->withQueryString();

        return view('crm.customers.index', compact('customers'));
    }

    public function create()
    {
        return view('crm.customers.create', [
            'customer' => new Customer(),
            'types' => Customer::TYPES,
            'statuses' => Customer::STATUSES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateCustomer($request);
        $data['created_by'] = Auth::id();

        $customer = Customer::create($data);

        return redirect()->route('crm.customers.show', $customer)->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer)
    {
        $customer->load([
            'tasks.assignee',
            'tasks.taskType',
            'tasks.booking.receipts',
            'queries.assignedTo',
            'queries.convertedTask',
            'bookings.receipts.receivedBy',
            'bookings.vendorPayments.enteredBy',
            'interactions.creator',
            'documents.uploader',
        ]);

        $taskIds = $customer->tasks->pluck('id');
        $bookings = $customer->bookings;
        $saleAmount = (float) $bookings->sum('sale_amount');
        $received = (float) $bookings->sum(fn ($booking) => $booking->receipts->whereIn('receipt_status', CustomerReceipt::APPROVED_STATUSES)->sum('amount_received'));
        $vendorCost = (float) $bookings->sum('purchase_amount');

        $overview = [
            'total_queries' => $customer->queries->count(),
            'total_tasks' => $customer->tasks->count(),
            'active_tasks' => $customer->tasks->where('final_status', '!=', \App\Models\Task::FINAL_CLOSED)->count(),
            'closed_tasks' => $customer->tasks->where('final_status', \App\Models\Task::FINAL_CLOSED)->count(),
            'total_sales' => $saleAmount,
            'total_received' => $received,
            'pending_balance' => max(0, $saleAmount - $received),
            'vendor_cost' => $vendorCost,
            'profit_estimate' => $saleAmount - $vendorCost,
        ];

        $receipts = $bookings->flatMap->receipts->sortByDesc('payment_date');
        $vendorPayments = $bookings->flatMap->vendorPayments->sortByDesc('payment_date');
        $activities = \Spatie\Activitylog\Models\Activity::where(function ($query) use ($customer, $taskIds) {
            $query->where(function ($sub) use ($customer) {
                $sub->where('subject_type', Customer::class)->where('subject_id', $customer->id);
            })->orWhere(function ($sub) use ($taskIds) {
                $sub->where('subject_type', \App\Models\Task::class)->whereIn('subject_id', $taskIds);
            });
        })->latest()->take(50)->get();

        $queryActivities = \App\Models\QueryActivity::with('user')
            ->whereIn('query_id', $customer->queries->pluck('id'))
            ->latest('activity_at')
            ->take(50)
            ->get();

        return view('crm.customers.show', compact('customer', 'overview', 'receipts', 'vendorPayments', 'activities', 'queryActivities'));
    }

    public function edit(Customer $customer)
    {
        return view('crm.customers.edit', [
            'customer' => $customer,
            'types' => Customer::TYPES,
            'statuses' => Customer::STATUSES,
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $customer->update($this->validateCustomer($request));

        return redirect()->route('crm.customers.show', $customer)->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('crm.customers.index')->with('success', 'Customer deleted successfully.');
    }

    private function validateCustomer(Request $request): array
    {
        return $request->validate([
            'customer_type' => 'required|in:' . implode(',', Customer::TYPES),
            'company_name' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:50',
            'alternate_mobile' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'gst_number' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:2000',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'remarks' => 'nullable|string|max:2000',
            'status' => 'required|in:' . implode(',', Customer::STATUSES),
        ]);
    }
}
