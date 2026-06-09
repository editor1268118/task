<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTaskTypeRequest;
use App\Http\Requests\Admin\UpdateTaskTypeRequest;
use App\Models\CompletionForm;
use App\Models\TaskType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TaskTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = TaskType::withCount('tasks')->with('completionForms');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $taskTypes = $query->orderBy('name')->paginate(10)->withQueryString();

        return view('admin.task-types.index', compact('taskTypes'));
    }

    public function create()
    {
        return view('admin.task-types.create');
    }

    public function store(StoreTaskTypeRequest $request)
    {
        $taskType = TaskType::create([
            'name' => $request->name,
            'slug' => Str::slug($request->slug ?: $request->name),
            'description' => $request->description,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->syncOperationalForm($taskType, $request->boolean('requires_operational_form', true));

        return redirect()->route('admin.task-types.index')
            ->with('success', 'Task type created successfully.');
    }

    public function edit(TaskType $taskType)
    {
        $requiresOperationalForm = $taskType->completionForms()
            ->where('completion_forms.slug', 'hotel-tour')
            ->exists();

        return view('admin.task-types.edit', compact('taskType', 'requiresOperationalForm'));
    }

    public function update(UpdateTaskTypeRequest $request, TaskType $taskType)
    {
        $taskType->update([
            'name' => $request->name,
            'slug' => Str::slug($request->slug ?: $request->name),
            'description' => $request->description,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->syncOperationalForm($taskType, $request->boolean('requires_operational_form'));

        return redirect()->route('admin.task-types.index')
            ->with('success', 'Task type updated successfully.');
    }

    public function destroy(TaskType $taskType)
    {
        if ($taskType->tasks()->exists()) {
            return back()->with('error', 'Cannot delete a task type that is already used by tasks. Deactivate it instead.');
        }

        $taskType->delete();

        return redirect()->route('admin.task-types.index')
            ->with('success', 'Task type deleted successfully.');
    }

    private function syncOperationalForm(TaskType $taskType, bool $enabled): void
    {
        $form = CompletionForm::where('slug', 'hotel-tour')->first();

        if (!$form) {
            return;
        }

        if ($enabled) {
            $taskType->completionForms()->syncWithoutDetaching([
                $form->id => [
                    'sort_order' => 1,
                    'is_required' => true,
                ],
            ]);
            return;
        }

        $taskType->completionForms()->detach($form->id);
    }
}
