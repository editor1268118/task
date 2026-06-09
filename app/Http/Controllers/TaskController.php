<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskType;
use App\Models\Department;
use App\Models\User;
use App\Models\Customer;
use App\Services\TaskService;
use App\Services\CompletionWorkflowService;
use App\Services\FinanceWorkflowService;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    protected $taskService;
    protected $workflowService;
    protected $financeWorkflowService;

    public function __construct(TaskService $taskService, CompletionWorkflowService $workflowService, FinanceWorkflowService $financeWorkflowService)
    {
        $this->taskService     = $taskService;
        $this->workflowService = $workflowService;
        $this->financeWorkflowService = $financeWorkflowService;
        $this->authorizeResource(Task::class, 'task');
    }

    /**
     * Display a listing of the tasks.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Task::with(['department', 'assigner', 'assignee']);

        // Apply Role-Based Filtering First
        if ($user->hasRole('employee')) {
            $query->where('assigned_to', $user->id);
        } elseif ($user->hasRole('manager')) {
            $query->where('department_id', $user->department_id);
        } elseif ($user->hasRole('finance')) {
            $query->financeRelevant();
        }

        // Apply User Filters
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('task_no', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status != '') {
            $query->status($request->status);
        }

        if ($request->boolean('overdue')) {
            $query->overdue();
        }

        if ($request->has('priority') && $request->priority != '') {
            $query->where('priority', $request->priority);
        }

        if ($request->has('department') && $request->department != '' && !$user->hasRole('manager')) {
            $query->where('department_id', $request->department);
        }

        if ($request->has('assigned_to') && $request->assigned_to != '' && !$user->hasRole('employee')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        $tasks = $query->latest()->paginate(15)->withQueryString();

        $departments = Department::active()->orderBy('name')->get();
        
        // Build assignees list based on role
        if ($user->hasRole('super-admin')) {
            $assignees = User::whereIn('status', ['active'])->orderBy('name')->get();
        } elseif ($user->hasRole('manager')) {
            $assignees = User::where('department_id', $user->department_id)->whereIn('status', ['active'])->orderBy('name')->get();
        } else {
            $assignees = collect();
        }

        return view('tasks.index', compact('tasks', 'departments', 'assignees'));
    }

    /**
     * Show the form for creating a new task.
     */
    public function create()
    {
        $user = Auth::user();
        
        if ($user->hasRole('super-admin')) {
            $departments = Department::active()->orderBy('name')->get();
            $assignees = User::whereIn('status', ['active'])->orderBy('name')->get();
        } else {
            $departments = Department::where('id', $user->department_id)->get();
            $assignees = User::where('department_id', $user->department_id)->whereIn('status', ['active'])->orderBy('name')->get();
        }

        $taskNo    = Task::generateTaskNumber();
        $taskTypes = TaskType::active()->orderBy('name')->get();
        $customers = Customer::orderBy('company_name')->orderBy('contact_person')->get();

        return view('tasks.create', compact('departments', 'assignees', 'taskNo', 'taskTypes', 'customers'));
    }

    /**
     * Store a newly created task.
     */
    public function store(StoreTaskRequest $request)
    {
        $this->taskService->createTask($request->validated(), Auth::user());

        return redirect()->route('tasks.index')
            ->with('success', 'Task created and assigned successfully.');
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task)
    {
        $task->load([
            'department', 'assigner', 'assignee', 'comments.user', 'attachments.uploader',
            'activities.causer', 'taskType', 'formSubmissions.completionForm', 'booking',
            'customerReceipts.receivedBy', 'vendorPayments.enteredBy', 'financeApprover',
            'managementApprover', 'customer.interactions.creator',
        ]);
        
        // Sort activities latest first
        $activities = $task->activities->sortByDesc('created_at');

        // Completion workflow data
        $wizardSteps   = null;
        $formSummary   = [];
        if ($task->isInCompletionProcess() || $task->operational_status === Task::OPERATIONAL_COMPLETED) {
            $wizardSteps = $this->workflowService->getWizardSteps($task);
            $formSummary = $this->workflowService->getFormStatusSummary($task);
        }

        $financialSummary = $this->financeWorkflowService->summary($task);
        $financeLedger = $this->financeWorkflowService->ledger($task);

        return view('tasks.show', compact('task', 'activities', 'wizardSteps', 'formSummary', 'financialSummary', 'financeLedger'));
    }

    /**
     * Show the form for editing the specified task.
     */
    public function edit(Task $task)
    {
        $user = Auth::user();

        if ($user->hasRole('super-admin')) {
            $departments = Department::active()->orderBy('name')->get();
            $assignees = User::whereIn('status', ['active'])->orderBy('name')->get();
        } else {
            $departments = Department::where('id', $user->department_id)->get();
            $assignees = User::where('department_id', $user->department_id)->whereIn('status', ['active'])->orderBy('name')->get();
        }

        $taskTypes = TaskType::active()->orderBy('name')->get();
        $customers = Customer::orderBy('company_name')->orderBy('contact_person')->get();

        return view('tasks.edit', compact('task', 'departments', 'assignees', 'taskTypes', 'customers'));
    }

    /**
     * Update the specified task.
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $this->taskService->updateTask($task, $request->validated(), Auth::user());

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Task updated successfully.');
    }

    /**
     * Update the task's status.
     */
    public function updateStatus(UpdateTaskStatusRequest $request, Task $task)
    {
        try {
            $this->taskService->updateStatus(
                $task,
                $request->status,
                Auth::user(),
                $request->completion_percentage
            );

            return redirect()->route('tasks.show', $task)
                ->with('success', 'Task status updated successfully.');
        } catch (\LogicException $e) {
            return redirect()->route('tasks.show', $task)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(Task $task)
    {
        $task->delete(); // Soft delete

        return redirect()->route('tasks.index')
            ->with('success', 'Task deleted successfully.');
    }
}
