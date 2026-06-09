<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerInteraction;
use App\Services\CustomerCrmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerInteractionController extends Controller
{
    public function __construct(protected CustomerCrmService $customerCrmService)
    {
    }

    public function index(Request $request)
    {
        $query = CustomerInteraction::with(['customer', 'task', 'creator']);
        $user = Auth::user();

        if ($user->hasRole('employee')) {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhereHas('task', fn ($sub) => $sub->where('assigned_to', $user->id));
            });
        } elseif ($user->hasRole('manager')) {
            $query->whereHas('task', fn ($sub) => $sub->where('department_id', $user->department_id));
        }

        if ($request->filled('type')) {
            $query->where('interaction_type', $request->type);
        }

        $interactions = $query->latest('interaction_date')->paginate(20)->withQueryString();

        return view('crm.interactions.index', compact('interactions'));
    }

    public function store(Request $request, Customer $customer)
    {
        $this->authorize('addInteraction', $customer);

        $data = $request->validate([
            'task_id' => 'nullable|exists:tasks,id',
            'interaction_type' => 'required|in:' . implode(',', CustomerInteraction::TYPES),
            'interaction_date' => 'nullable|date',
            'notes' => 'nullable|string|max:3000',
            'next_followup_date' => 'nullable|date',
            'followup_notes' => 'nullable|string|max:3000',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $this->customerCrmService->recordInteraction($customer, $data, Auth::user());

        return back()->with('success', 'Interaction recorded.');
    }
}
