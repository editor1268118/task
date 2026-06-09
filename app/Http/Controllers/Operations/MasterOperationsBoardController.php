<?php

namespace App\Http\Controllers\Operations;

use App\Exports\MasterOperationsBoardExport;
use App\Http\Controllers\Controller;
use App\Models\BusinessStatus;
use App\Models\OperationBoardColumnPreference;
use App\Models\OperationBoardSavedFilter;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\TaskType;
use App\Models\User;
use App\Services\MasterOperationsBoardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class MasterOperationsBoardController extends Controller
{
    public function __construct(protected MasterOperationsBoardService $boardService)
    {
    }

    public function index(Request $request)
    {
        $this->authorizeAccess();

        $user = Auth::user();
        $query = $this->boardService->applyFilters($this->boardService->baseQuery($user), $request);
        $kpis = $this->boardService->kpis(clone $query);
        $tasks = $query->latest('updated_at')->paginate(25)->withQueryString();
        $rows = $this->boardService->rows($tasks->getCollection());
        $columns = $this->boardService->selectedColumns($user, $request);

        return view('operations.master-board.index', [
            'tasks' => $tasks,
            'rows' => $rows,
            'columns' => $columns,
            'columnLabels' => MasterOperationsBoardService::COLUMN_LABELS,
            'allColumns' => MasterOperationsBoardService::DEFAULT_COLUMNS,
            'kpis' => $kpis,
            'savedFilters' => OperationBoardSavedFilter::where('user_id', $user->id)->latest()->get(),
            'employees' => $this->employeesFor($user),
            'taskTypes' => TaskType::active()->orderBy('name')->get(),
            'taskStatuses' => TaskStatus::active()->orderBy('name')->get(),
            'businessStatuses' => BusinessStatus::active()->orderBy('name')->get(),
        ]);
    }

    public function export(Request $request, string $format)
    {
        $this->authorizeAccess();

        abort_unless(in_array($format, ['xlsx', 'csv'], true), 404);

        $user = Auth::user();
        $query = $this->boardService->applyFilters($this->boardService->baseQuery($user), $request);
        $tasks = $query->latest('updated_at')->limit(10000)->get();
        $columns = $this->boardService->selectedColumns($user, $request);
        $rows = $this->boardService->rows($tasks);

        activity()
            ->causedBy($user)
            ->withProperties([
                'export_type' => $format,
                'filters' => $request->except(['_token']),
                'columns' => $columns,
                'record_count' => $rows->count(),
            ])
            ->log('Master Operations Board export generated');

        return Excel::download(
            new MasterOperationsBoardExport($rows, $columns),
            'master-operations-board-' . now()->format('Ymd-His') . '.' . $format
        );
    }

    public function print(Request $request)
    {
        $this->authorizeAccess();

        $user = Auth::user();
        $query = $this->boardService->applyFilters($this->boardService->baseQuery($user), $request);
        $rows = $this->boardService->rows($query->latest('updated_at')->limit(1000)->get());
        $columns = $this->boardService->selectedColumns($user, $request);
        $kpis = $this->boardService->kpis(clone $query);

        return view('operations.master-board.print', compact('rows', 'columns', 'kpis') + [
            'columnLabels' => MasterOperationsBoardService::COLUMN_LABELS,
        ]);
    }

    public function saveFilter(Request $request)
    {
        $this->authorizeAccess();

        $data = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        OperationBoardSavedFilter::updateOrCreate(
            ['user_id' => Auth::id(), 'name' => $data['name']],
            ['filters' => $request->except(['_token', 'name'])]
        );

        return back()->with('success', 'Filter view saved.');
    }

    public function saveColumns(Request $request)
    {
        $this->authorizeAccess();

        $data = $request->validate([
            'columns' => 'required|array',
            'columns.*' => 'string',
        ]);

        OperationBoardColumnPreference::updateOrCreate(
            ['user_id' => Auth::id()],
            ['columns' => array_values(array_intersect(MasterOperationsBoardService::DEFAULT_COLUMNS, $data['columns']))]
        );

        return back()->with('success', 'Column preferences saved.');
    }

    private function authorizeAccess(): void
    {
        abort_unless(Auth::user()->hasAnyRole(['super-admin', 'manager', 'finance']), 403);
    }

    private function employeesFor(User $user)
    {
        if ($user->hasRole('manager')) {
            return User::active()->where('department_id', $user->department_id)->orderBy('name')->get();
        }

        return User::active()->orderBy('name')->get();
    }
}
