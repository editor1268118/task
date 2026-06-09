<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\CompletionWorkflowService;
use App\Http\Requests\StoreHotelTourFormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompletionWorkflowController extends Controller
{
    protected CompletionWorkflowService $workflowService;

    /**
     * Map form slugs to their FormRequest classes.
     */
    protected array $formRequestMap = [
        'hotel-tour' => StoreHotelTourFormRequest::class,
    ];

    public function __construct(CompletionWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Start the completion process for a task.
     */
    public function start(Task $task)
    {
        $this->authorize('updateStatus', $task);

        if (!$task->canStartCompletion()) {
            return redirect()->route('tasks.show', $task)
                ->with('error', 'This task cannot start the completion process. Ensure it is assigned or in progress and has a task type assigned.');
        }

        $this->workflowService->startCompletionProcess($task, Auth::user());

        return redirect()->route('tasks.completion.wizard', ['task' => $task, 'step' => 1])
            ->with('success', 'Completion process started. Please fill in the required forms.');
    }

    /**
     * Display the wizard at a specific step.
     */
    public function wizard(Task $task, ?int $step = null)
    {
        $this->authorize('updateStatus', $task);

        if (!$task->isInCompletionProcess()) {
            return redirect()->route('tasks.show', $task)
                ->with('error', 'This task is not in the completion process.');
        }

        $steps       = $this->workflowService->getWizardSteps($task);
        $currentStep = $step ?? $this->workflowService->getCurrentStep($task);
        $totalSteps  = count($steps);

        // Clamp step number
        if ($currentStep < 1 || $currentStep > $totalSteps) {
            $currentStep = 1;
        }

        $stepData = $steps[$currentStep - 1] ?? null;

        if (!$stepData) {
            return redirect()->route('tasks.show', $task)->with('error', 'Invalid step.');
        }

        // If this is the review step
        if (isset($stepData['is_review'])) {
            return redirect()->route('tasks.completion.review', $task);
        }

        // Get form data for this step
        $formData = $this->workflowService->getStepFormData($task, $currentStep);

        $task->load(['taskType', 'formSubmissions.completionForm']);

        return view('completion.wizard', compact(
            'task', 'steps', 'currentStep', 'totalSteps', 'stepData', 'formData'
        ));
    }

    /**
     * Store/submit form data for a specific step.
     */
    public function storeStep(Task $task, int $step, Request $request)
    {
        $this->authorize('updateStatus', $task);

        $steps    = $this->workflowService->getWizardSteps($task);
        $stepData = $steps[$step - 1] ?? null;

        if (!$stepData || isset($stepData['is_review'])) {
            return redirect()->route('tasks.completion.wizard', ['task' => $task, 'step' => 1])
                ->with('error', 'Invalid step.');
        }

        $formSlug = $stepData['form']->slug;

        // Validate using the appropriate FormRequest
        $requestClass = $this->formRequestMap[$formSlug] ?? null;
        if ($requestClass) {
            $validatedData = app($requestClass)->validated();
        } else {
            $validatedData = $request->all();
        }

        // Submit the form
        $this->workflowService->submitForm($task, $formSlug, $validatedData, Auth::user());

        // Determine next step
        $nextStep = $step + 1;
        $totalSteps = count($steps);

        if ($nextStep > $totalSteps) {
            return redirect()->route('tasks.completion.review', $task)
                ->with('success', 'Form submitted successfully.');
        }

        // Check if next step is review
        if (isset($steps[$nextStep - 1]['is_review'])) {
            return redirect()->route('tasks.completion.review', $task)
                ->with('success', 'Form submitted successfully.');
        }

        return redirect()->route('tasks.completion.wizard', ['task' => $task, 'step' => $nextStep])
            ->with('success', 'Form submitted. Proceed to the next step.');
    }

    /**
     * Save form data as draft for a specific step.
     */
    public function saveDraft(Task $task, int $step, Request $request)
    {
        $this->authorize('updateStatus', $task);

        $steps    = $this->workflowService->getWizardSteps($task);
        $stepData = $steps[$step - 1] ?? null;

        if (!$stepData || isset($stepData['is_review'])) {
            return redirect()->route('tasks.completion.wizard', ['task' => $task, 'step' => 1])
                ->with('error', 'Invalid step.');
        }

        $formSlug = $stepData['form']->slug;

        // Save as draft without full validation
        $this->workflowService->saveDraft($task, $formSlug, $request->all(), Auth::user());

        return redirect()->route('tasks.completion.wizard', ['task' => $task, 'step' => $step])
            ->with('success', 'Draft saved successfully.');
    }

    /**
     * Show the review page with all submitted forms.
     */
    public function review(Task $task)
    {
        $this->authorize('updateStatus', $task);

        if (!$task->isInCompletionProcess()) {
            return redirect()->route('tasks.show', $task)
                ->with('error', 'This task is not in the completion process.');
        }

        $task->load([
            'taskType',
            'hotelTourForm.enteredBy',
            'formSubmissions.completionForm',
        ]);

        $steps         = $this->workflowService->getWizardSteps($task);
        $formSummary   = $this->workflowService->getFormStatusSummary($task);
        $allSubmitted  = $task->areAllFormsSubmitted();

        return view('completion.review', compact(
            'task', 'steps', 'formSummary', 'allSubmitted'
        ));
    }

    /**
     * Final completion — validate all forms and mark task completed.
     */
    public function complete(Task $task)
    {
        $this->authorize('updateStatus', $task);

        if (!$task->isInCompletionProcess()) {
            return redirect()->route('tasks.show', $task)
                ->with('error', 'This task is not in the completion process.');
        }

        $success = $this->workflowService->completeTask($task, Auth::user());

        if (!$success) {
            return redirect()->route('tasks.completion.review', $task)
                ->with('error', 'Cannot complete operations. Please submit the operational booking form.');
        }

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Operations completed. The task remains active until settlement and finance approval.');
    }
}
