<?php

namespace App\Http\Controllers\Sales;

use App\Exports\SalesQueryExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQueryDiscussionRequest;
use App\Http\Requests\StoreQueryFollowupRequest;
use App\Http\Requests\StoreSalesQueryRequest;
use App\Http\Requests\UpdateQueryDiscussionRequest;
use App\Http\Requests\UpdateSalesQueryRequest;
use App\Models\QueryDiscussion;
use App\Models\SalesQuery;
use App\Models\User;
use App\Services\QueryManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

class QueryController extends Controller
{
    public function __construct(protected QueryManagementService $queries)
    {
    }

    public function index(Request $request)
    {
        $this->authorizeAccess();

        $query = $this->queries->applyFilters($this->queries->baseQuery(Auth::user()), $request);
        $queries = $query->latest('query_date')->latest('id')->paginate(25)->withQueryString();

        return view('sales.queries.index', $this->sharedData() + compact('queries'));
    }

    public function create()
    {
        $this->authorizeAccess();
        $this->authorizeCreate();

        return view('sales.queries.create', $this->sharedData() + [
            'queryNo' => SalesQuery::generateQueryNumber(),
            'duplicates' => collect(),
        ]);
    }

    public function store(StoreSalesQueryRequest $request)
    {
        $this->authorizeAccess();
        $this->authorizeCreate();
        $data = $request->validated();
        $duplicates = $this->queries->duplicates($data);

        if ($duplicates->isNotEmpty() && !$request->boolean('duplicate_confirmed')) {
            return back()->withInput()->with([
                'duplicate_warning' => true,
                'duplicate_queries' => $duplicates,
            ]);
        }

        $query = $this->queries->create($data, Auth::user());

        return redirect()->route('sales.queries.show', $query)->with('success', 'Query created successfully.');
    }

    public function show(SalesQuery $query)
    {
        $this->authorizeQuery($query);

        $query->load([
            'assignedBy',
            'assignedTo',
            'convertedTask',
            'followups.creator',
            'discussions.creator',
            'discussions.mentionedUser',
            'activities.user',
        ]);

        return view('sales.queries.show', $this->sharedData() + compact('query'));
    }

    public function edit(SalesQuery $query)
    {
        $this->authorizeQuery($query);

        return view('sales.queries.edit', $this->sharedData() + compact('query'));
    }

    public function update(UpdateSalesQueryRequest $request, SalesQuery $query)
    {
        $this->authorizeQuery($query);
        $data = $request->validated();
        $duplicates = $this->queries->duplicates($data, $query);

        if ($duplicates->isNotEmpty() && !$request->boolean('duplicate_confirmed')) {
            return back()->withInput()->with([
                'duplicate_warning' => true,
                'duplicate_queries' => $duplicates,
            ]);
        }

        $this->queries->update($query, $data, Auth::user());

        return redirect()->route('sales.queries.show', $query)->with('success', 'Query updated successfully.');
    }

    public function destroy(SalesQuery $query)
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        $query->delete();
        Cache::flush();

        return redirect()->route('sales.queries.index')->with('success', 'Query deleted successfully.');
    }

    public function addFollowup(StoreQueryFollowupRequest $request, SalesQuery $query)
    {
        $this->authorizeQuery($query);

        $this->queries->addFollowup($query, $request->validated(), Auth::user());

        return back()->with('success', 'Follow-up added successfully.');
    }

    public function addDiscussion(StoreQueryDiscussionRequest $request, SalesQuery $query)
    {
        $this->authorizeQuery($query);

        $this->queries->addDiscussion($query, $request->validated(), Auth::user());

        return back()->with('success', 'Discussion added successfully.');
    }

    public function updateDiscussion(UpdateQueryDiscussionRequest $request, SalesQuery $query, QueryDiscussion $discussion)
    {
        $this->authorizeQuery($query);
        abort_unless($discussion->query_id === $query->id, 404);
        abort_unless($discussion->canBeManagedBy(Auth::user()), 403);

        $this->queries->updateDiscussion($discussion, $request->validated(), Auth::user());

        return back()->with('success', 'Discussion updated successfully.');
    }

    public function deleteDiscussion(SalesQuery $query, QueryDiscussion $discussion)
    {
        $this->authorizeQuery($query);
        abort_unless($discussion->query_id === $query->id, 404);
        abort_unless($discussion->canBeManagedBy(Auth::user()), 403);

        $this->queries->deleteDiscussion($discussion, Auth::user());

        return back()->with('success', 'Discussion deleted successfully.');
    }

    public function quickStatus(Request $request, SalesQuery $query)
    {
        $this->authorizeQuery($query);

        $data = $request->validate([
            'stage' => ['required', 'in:' . implode(',', SalesQuery::STAGES)],
            'status' => ['required', 'in:' . implode(',', ['Open', 'Confirmed', 'Lost', 'Cancelled'])],
            'latest_remark' => ['nullable', 'string', 'max:5000'],
            'next_followup_date' => ['nullable', 'date'],
            'next_followup_time' => ['nullable', 'date_format:H:i'],
            'lost_reason' => ['nullable', 'required_if:status,Lost', 'in:' . implode(',', SalesQuery::LOST_REASONS)],
        ]);

        $this->queries->update($query, $data, Auth::user());

        return back()->with('success', 'Query status updated successfully.');
    }

    public function reassign(Request $request, SalesQuery $query)
    {
        abort_unless(Auth::user()->hasAnyRole(['super-admin', 'manager']), 403);
        $this->authorizeQuery($query);

        $data = $request->validate([
            'assigned_to' => ['required', 'exists:users,id'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $this->queries->reassign($query, (int) $data['assigned_to'], $data['reason'], Auth::user());

        return back()->with('success', 'Query reassigned successfully.');
    }

    public function convert(SalesQuery $query)
    {
        $this->authorizeQuery($query);
        abort_unless(Auth::user()->hasAnyRole(['super-admin', 'manager']), 403);

        try {
            $task = $this->queries->convertToTask($query, Auth::user());
        } catch (\LogicException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('tasks.show', $task)->with('success', 'Query converted to task successfully.');
    }

    public function dashboard()
    {
        $this->authorizeAccess();

        return view('sales.queries.dashboard', [
            'stats' => $this->queries->dashboardStats(Auth::user()),
        ]);
    }

    public function followups(Request $request)
    {
        $this->authorizeAccess();
        $base = $this->queries->baseQuery(Auth::user());
        $queries = (clone $base)
            ->whereNotNull('next_followup_date')
            ->whereIn('status', ['Open', 'Confirmed'])
            ->with(['assignedTo'])
            ->orderBy('next_followup_date')
            ->paginate(30)
            ->withQueryString();

        return view('sales.queries.followups', compact('queries'));
    }

    public function reports(Request $request)
    {
        $this->authorizeAccess();

        $query = $this->queries->applyFilters($this->queries->baseQuery(Auth::user()), $request);
        $queries = $query->with(['assignedBy', 'assignedTo'])->latest('query_date')->paginate(50)->withQueryString();

        return view('sales.queries.reports', $this->sharedData() + compact('queries'));
    }

    public function export(Request $request, string $format)
    {
        $this->authorizeAccess();
        abort_unless(in_array($format, ['xlsx', 'csv'], true), 404);

        $queries = $this->queries
            ->applyFilters($this->queries->baseQuery(Auth::user()), $request)
            ->with(['assignedBy', 'assignedTo'])
            ->latest('query_date')
            ->limit(10000)
            ->get();

        if ($queries->isNotEmpty()) {
            $this->queries->log($queries->first(), Auth::user(), 'Export Generated', 'Query export generated: ' . $format);
        }

        return Excel::download(new SalesQueryExport($queries), 'query-register-' . now()->format('Ymd-His') . '.' . $format);
    }

    public function print(Request $request)
    {
        $this->authorizeAccess();

        $queries = $this->queries
            ->applyFilters($this->queries->baseQuery(Auth::user()), $request)
            ->with(['assignedBy', 'assignedTo'])
            ->latest('query_date')
            ->limit(1000)
            ->get();

        return view('sales.queries.print', compact('queries'));
    }

    private function sharedData(): array
    {
        return [
            'employees' => $this->employeesFor(Auth::user()),
            'serviceTypes' => SalesQuery::SERVICE_TYPES,
            'sources' => SalesQuery::SOURCES,
            'priorities' => SalesQuery::PRIORITIES,
            'stages' => SalesQuery::STAGES,
            'statuses' => SalesQuery::STATUSES,
            'lostReasons' => SalesQuery::LOST_REASONS,
            'discussionTypes' => QueryDiscussion::TYPES,
        ];
    }

    private function employeesFor(User $user)
    {
        if ($user->hasRole('manager')) {
            return User::active()->where('department_id', $user->department_id)->orderBy('name')->get();
        }

        return User::active()->whereHas('roles', fn ($q) => $q->whereIn('name', ['employee', 'manager']))->orderBy('name')->get();
    }

    private function authorizeAccess(): void
    {
        $user = Auth::user();

        abort_unless(
            $user->hasRole('super-admin')
            || $user->can('view-queries')
            || $user->can('create-queries'),
            403
        );
    }

    private function authorizeCreate(): void
    {
        abort_unless(Auth::user()->hasRole('super-admin') || Auth::user()->can('create-queries'), 403);
    }

    private function authorizeQuery(SalesQuery $query): void
    {
        $user = Auth::user();
        if ($user->hasRole('super-admin')) {
            return;
        }

        if ($user->hasRole('employee')) {
            abort_unless(
                $query->assigned_to === $user->id
                || $query->created_by === $user->id
                || $query->assigned_by === $user->id,
                403
            );
        }

        if ($user->hasRole('manager')) {
            $teamIds = User::where('department_id', $user->department_id)->pluck('id')->all();
            abort_unless(in_array($query->assigned_to, $teamIds, true) || $query->created_by === $user->id, 403);
        }
    }
}
