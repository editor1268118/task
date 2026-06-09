<?php

namespace App\Services;

use App\Models\Task;
use App\Models\CompletionForm;
use App\Models\TaskFormSubmission;
use App\Models\HotelTourForm;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompletionWorkflowService
{
    public function __construct(protected FinanceWorkflowService $financeWorkflowService)
    {
    }

    /**
     * Map form slugs to their Eloquent model classes.
     */
    protected array $formModelMap = [
        'hotel-tour' => HotelTourForm::class,
    ];

    /**
     * Map form slugs to Task relationship method names.
     */
    protected array $formRelationMap = [
        'hotel-tour' => 'hotelTourForm',
    ];

    /**
     * Start the completion process for a task.
     * Sets status to completion_pending and initializes form submission records.
     */
    public function startCompletionProcess(Task $task, User $user): Task
    {
        return DB::transaction(function () use ($task, $user) {
            // Update task status
            $statusModel = \App\Models\TaskStatus::where('slug', Task::STATUS_COMPLETION_PENDING)->first();
            if ($statusModel) {
                $task->update([
                    'task_status_id'        => $statusModel->id,
                    'completion_started_at' => now(),
                    'operational_status'    => Task::OPERATIONAL_BOOKING_IN_PROCESS,
                    'current_department'    => Task::DEPARTMENT_OPERATIONS,
                ]);
            }

            // Create submission tracking records for each required form
            $requiredForms = $task->getRequiredForms();

            foreach ($requiredForms as $form) {
                TaskFormSubmission::updateOrCreate(
                    [
                        'task_id'            => $task->id,
                        'completion_form_id' => $form->id,
                    ],
                    [
                        'form_type'    => $form->form_class,
                        'form_id'      => null,
                        'submitted_by' => $user->id,
                        'status'       => TaskFormSubmission::STATUS_PENDING,
                    ]
                );
            }

            activity()
                ->performedOn($task)
                ->causedBy($user)
                ->withProperties(['status' => 'completion_pending'])
                ->log('Completion process started');

            Log::info("Completion process started for task {$task->task_no} by {$user->name}");

            return $task->fresh();
        });
    }

    /**
     * Get wizard steps with current status for a task.
     *
     * @return array<int, array{step: int, form: CompletionForm, status: string, submission: ?TaskFormSubmission}>
     */
    public function getWizardSteps(Task $task): array
    {
        $requiredForms = $task->getRequiredForms();
        $submissions   = $task->formSubmissions()->with('completionForm')->get()->keyBy('completion_form_id');

        $steps = [];
        $stepNumber = 1;

        foreach ($requiredForms as $form) {
            $submission = $submissions->get($form->id);

            $steps[] = [
                'step'       => $stepNumber,
                'form'       => $form,
                'status'     => $submission ? $submission->status : 'pending',
                'submission' => $submission,
            ];
            $stepNumber++;
        }

        // Add review step
        $steps[] = [
            'step'       => $stepNumber,
            'form'       => null,
            'status'     => $task->areAllFormsSubmitted() ? 'ready' : 'locked',
            'submission' => null,
            'is_review'  => true,
        ];

        return $steps;
    }

    /**
     * Get the current step number (first non-submitted step).
     */
    public function getCurrentStep(Task $task): int
    {
        $steps = $this->getWizardSteps($task);

        foreach ($steps as $step) {
            if (isset($step['is_review'])) {
                return $step['step'];
            }
            if ($step['status'] !== TaskFormSubmission::STATUS_SUBMITTED) {
                return $step['step'];
            }
        }

        return 1;
    }

    /**
     * Get form data for a specific step.
     */
    public function getStepFormData(Task $task, int $stepNumber): ?array
    {
        $steps = $this->getWizardSteps($task);
        $step  = collect($steps)->firstWhere('step', $stepNumber);

        if (!$step || isset($step['is_review'])) {
            return null;
        }

        $form     = $step['form'];
        $slug     = $form->slug;
        $relation = $this->formRelationMap[$slug] ?? null;

        $existingData = null;
        if ($relation) {
            $existingData = $task->{$relation};
        }

        return [
            'form'         => $form,
            'existingData' => $existingData,
            'submission'   => $step['submission'],
            'slug'         => $slug,
        ];
    }

    /**
     * Save form data as draft (no validation required for mandatory fields).
     */
    public function saveDraft(Task $task, string $formSlug, array $data, User $user): void
    {
        DB::transaction(function () use ($task, $formSlug, $data, $user) {
            $formRecord = $this->upsertFormData($task, $formSlug, $data, $user, 'draft');

            // Update submission tracker
            $completionForm = CompletionForm::where('slug', $formSlug)->firstOrFail();
            $modelClass     = $this->formModelMap[$formSlug];

            TaskFormSubmission::updateOrCreate(
                [
                    'task_id'            => $task->id,
                    'completion_form_id' => $completionForm->id,
                ],
                [
                    'form_type'    => $modelClass,
                    'form_id'     => $formRecord->id,
                    'submitted_by' => $user->id,
                    'status'       => TaskFormSubmission::STATUS_DRAFT,
                ]
            );

            activity()
                ->performedOn($task)
                ->causedBy($user)
                ->withProperties(['form' => $completionForm->display_name, 'action' => 'draft_saved'])
                ->log("{$completionForm->display_name} draft saved");
        });
    }

    /**
     * Submit a form (with full validation assumed to have passed at request level).
     */
    public function submitForm(Task $task, string $formSlug, array $data, User $user): void
    {
        DB::transaction(function () use ($task, $formSlug, $data, $user) {
            $formRecord = $this->upsertFormData($task, $formSlug, $data, $user, 'submitted');

            // Update submission tracker
            $completionForm = CompletionForm::where('slug', $formSlug)->firstOrFail();
            $modelClass     = $this->formModelMap[$formSlug];

            TaskFormSubmission::updateOrCreate(
                [
                    'task_id'            => $task->id,
                    'completion_form_id' => $completionForm->id,
                ],
                [
                    'form_type'    => $modelClass,
                    'form_id'     => $formRecord->id,
                    'submitted_by' => $user->id,
                    'submitted_at' => now(),
                    'status'       => TaskFormSubmission::STATUS_SUBMITTED,
                ]
            );

            activity()
                ->performedOn($task)
                ->causedBy($user)
                ->withProperties(['form' => $completionForm->display_name, 'action' => 'submitted'])
                ->log("{$completionForm->display_name} submitted");

            // Check if all forms are submitted → update task status
            $task->refresh();
            if ($task->areAllFormsSubmitted()) {
                $this->financeWorkflowService->markOperationallyCompleted($task, $user);

                activity()
                    ->performedOn($task)
                    ->causedBy($user)
                    ->log('Operational booking form submitted');

            }
        });
    }

    /**
     * Confirm operational completion only; finance closes the task separately.
     */
    public function completeTask(Task $task, User $user): bool
    {
        if (!$this->validateAllForms($task)) {
            return false;
        }

        $this->financeWorkflowService->markOperationallyCompleted($task, $user);
        Log::info("Task {$task->task_no} marked operationally completed by {$user->name}");

        return true;
    }

    /**
     * Validate that all required forms have been submitted.
     */
    public function validateAllForms(Task $task): bool
    {
        return $task->areAllFormsSubmitted();
    }

    /**
     * Get a summary of form statuses for display.
     *
     * @return array<string, array{name: string, status: string, submitted_at: ?string}>
     */
    public function getFormStatusSummary(Task $task): array
    {
        $submissions = $task->formSubmissions()
            ->with('completionForm')
            ->get();

        $summary = [];
        foreach ($submissions as $sub) {
            $summary[$sub->completionForm->slug] = [
                'name'         => $sub->completionForm->display_name,
                'status'       => $sub->status,
                'submitted_at' => $sub->submitted_at?->timezone(config('app.display_timezone'))->format('d M Y h:i A'),
            ];
        }

        return $summary;
    }

    /**
     * Create or update form data record.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function upsertFormData(Task $task, string $formSlug, array $data, User $user, string $status)
    {
        $relation = $this->formRelationMap[$formSlug] ?? null;

        if (!$relation) {
            throw new \InvalidArgumentException("Unknown form slug: {$formSlug}");
        }

        $existing = $task->{$relation};

        $data['task_id']    = $task->id;
        $data['entered_by'] = $user->id;
        $data['status']     = $status;

        if ($existing) {
            $existing->update($data);
            if ($formSlug === 'hotel-tour') {
                $this->financeWorkflowService->syncBookingFromHotelForm($task->fresh(), $data, $user);
            }
            return $existing->fresh();
        }

        $modelClass = $this->formModelMap[$formSlug];
        $record = $modelClass::create($data);
        if ($formSlug === 'hotel-tour') {
            $this->financeWorkflowService->syncBookingFromHotelForm($task->fresh(), $data, $user);
        }

        return $record;
    }
}
