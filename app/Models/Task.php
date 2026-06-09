<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Task extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * Task status constants.
     */
    const STATUS_PENDING            = 'pending';
    const STATUS_ASSIGNED           = 'assigned';
    const STATUS_IN_PROGRESS        = 'in_progress';
    const STATUS_COMPLETED          = 'completed';
    const STATUS_ON_HOLD            = 'on_hold';
    const STATUS_CANCELLED          = 'cancelled';
    const STATUS_FOLLOW_UP          = 'follow_up';
    const STATUS_COMPLETION_PENDING = 'completion_pending';
    const STATUS_FORMS_SUBMITTED    = 'forms_submitted';
    const STATUS_OPERATIONALLY_COMPLETED = 'operationally_completed';
    const STATUS_COLLECTION_PENDING = 'collection_pending';
    const STATUS_VENDOR_PAYMENT_PENDING = 'vendor_payment_pending';
    const STATUS_FINANCE_REVIEW_PENDING = 'finance_review_pending';
    const STATUS_CLOSED = 'closed';

    const OPERATIONAL_PENDING = 'pending';
    const OPERATIONAL_IN_PROGRESS = 'in_progress';
    const OPERATIONAL_BOOKING_IN_PROCESS = 'booking_in_process';
    const OPERATIONAL_COMPLETED = 'operationally_completed';

    const FINANCIAL_UNPAID = 'unpaid';
    const FINANCIAL_PARTIAL = 'partial_payment';
    const FINANCIAL_PENDING_BALANCE = 'pending_balance';
    const FINANCIAL_FULLY_PAID = 'fully_paid';
    const FINANCIAL_VENDOR_PENDING = 'vendor_pending';
    const FINANCIAL_REFUND_PENDING = 'refund_pending';
    const FINANCIAL_REFUNDED = 'refunded';

    const FINAL_ACTIVE = 'active';
    const FINAL_UNDER_COLLECTION = 'under_collection';
    const FINAL_UNDER_REVIEW = 'under_review';
    const FINAL_CLOSED = 'closed';

    const DEPARTMENT_SALES = 'Sales';
    const DEPARTMENT_OPERATIONS = 'Operations';
    const DEPARTMENT_FINANCE = 'Finance';
    const DEPARTMENT_MANAGEMENT = 'Management';

    /**
     * Task priority constants.
     */
    const PRIORITY_HIGH   = 'high';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_LOW    = 'low';

    /**
     * Task number prefix.
     */
    const TASK_PREFIX = 'TSK';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'task_no',
        'title',
        'description',
        'priority',
        'task_status_id',
        'business_status_id',
        'department_id',
        'assigned_by',
        'assigned_to',
        'start_date',
        'due_date',
        'estimated_hours',
        'actual_hours',
        'completion_percentage',
        'remarks',
        'task_type_id',
        'customer_id',
        'client_name',
        'client_contact',
        'additional_info',
        'completion_started_at',
        'completed_at',
        'operational_status',
        'financial_status',
        'final_status',
        'current_department',
        'finance_approved_at',
        'finance_approved_by',
        'management_approved_at',
        'management_approved_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date'            => 'date',
        'due_date'              => 'date',
        'estimated_hours'       => 'decimal:2',
        'actual_hours'          => 'decimal:2',
        'completion_percentage' => 'integer',
        'completion_started_at' => 'datetime',
        'completed_at'          => 'datetime',
        'finance_approved_at'   => 'datetime',
        'management_approved_at' => 'datetime',
    ];

    /**
     * Activity log configuration.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'title', 'description', 'priority', 'task_status_id', 'business_status_id',
                'assigned_to', 'start_date', 'due_date',
                'estimated_hours', 'actual_hours',
                'completion_percentage', 'remarks',
                'operational_status', 'financial_status', 'final_status',
                'current_department', 'finance_approved_at', 'finance_approved_by',
                'management_approved_at', 'management_approved_by',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Task {$this->task_no} has been {$eventName}");
    }

    /**
     * Boot method for auto-generating task numbers.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($task) {
            if (empty($task->task_no)) {
                $task->task_no = self::generateTaskNumber();
            }
        });
    }

    /**
     * Generate the next task number in TSK0001 format.
     */
    public static function generateTaskNumber(): string
    {
        $lastTask = self::withTrashed()->orderBy('id', 'desc')->first();
        $nextNumber = $lastTask ? ((int) substr($lastTask->task_no, 3)) + 1 : 1;

        return self::TASK_PREFIX . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get all available statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING            => 'Pending',
            self::STATUS_ASSIGNED           => 'Assigned',
            self::STATUS_IN_PROGRESS        => 'In Progress',
            self::STATUS_COMPLETION_PENDING => 'Completion Pending',
            self::STATUS_FORMS_SUBMITTED    => 'Forms Submitted',
            self::STATUS_COMPLETED          => 'Completed',
            self::STATUS_ON_HOLD            => 'On Hold',
            self::STATUS_CANCELLED          => 'Cancelled',
            self::STATUS_FOLLOW_UP          => 'Follow up',
            self::STATUS_OPERATIONALLY_COMPLETED => 'Operationally Completed',
            self::STATUS_COLLECTION_PENDING => 'Collection Pending',
            self::STATUS_VENDOR_PAYMENT_PENDING => 'Vendor Payment Pending',
            self::STATUS_FINANCE_REVIEW_PENDING => 'Finance Review Pending',
            self::STATUS_CLOSED             => 'Closed',
        ];
    }

    /**
     * Get all available priorities.
     */
    public static function getPriorities(): array
    {
        return [
            self::PRIORITY_HIGH   => 'High',
            self::PRIORITY_MEDIUM => 'Medium',
            self::PRIORITY_LOW    => 'Low',
        ];
    }

    /**
     * Check if task is overdue.
     */
    public function isOverdue(): bool
    {
        $statusSlug = $this->status;
        return $this->due_date
            && $this->due_date->isPast()
            && !in_array($statusSlug, [self::STATUS_COMPLETED, self::STATUS_CLOSED, self::STATUS_CANCELLED]);
    }

    /**
     * Backward compatibility accessor for $task->status
     */
    public function getStatusAttribute()
    {
        if ($this->taskStatus) {
            return $this->taskStatus->slug;
        }
        if ($this->businessStatus) {
            return $this->businessStatus->slug;
        }
        return self::STATUS_PENDING;
    }

    // ─── Scopes ────────────────────────────────────────────────────

    public function scopeStatus($query, string $status)
    {
        return $query->where(function ($q) use ($status) {
            $q->whereHas('taskStatus', function ($sub) use ($status) {
                $sub->where('slug', $status);
            })->orWhereHas('businessStatus', function ($sub) use ($status) {
                $sub->where('slug', $status);
            });
        });
    }

    public function scopeStatusIn($query, array $statuses)
    {
        return $query->where(function ($q) use ($statuses) {
            $q->whereHas('taskStatus', function ($sub) use ($statuses) {
                $sub->whereIn('slug', $statuses);
            })->orWhereHas('businessStatus', function ($sub) use ($statuses) {
                $sub->whereIn('slug', $statuses);
            });
        });
    }

    public function scopeStatusNotIn($query, array $statuses)
    {
        return $query->whereDoesntHave('taskStatus', function ($sub) use ($statuses) {
            $sub->whereIn('slug', $statuses);
        })->whereDoesntHave('businessStatus', function ($sub) use ($statuses) {
            $sub->whereIn('slug', $statuses);
        });
    }

    public function scopePriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereDoesntHave('taskStatus', function ($q) {
                $q->whereIn('slug', [self::STATUS_COMPLETED, self::STATUS_CLOSED, self::STATUS_CANCELLED]);
            });
    }

    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeAssignedBy($query, int $userId)
    {
        return $query->where('assigned_by', $userId);
    }

    public function scopeInDepartment($query, int $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeFinanceRelevant($query)
    {
        return $query
            ->where('final_status', '!=', self::FINAL_CLOSED)
            ->where(function ($q) {
                $q->where('current_department', self::DEPARTMENT_FINANCE)
                    ->orWhereIn('financial_status', [
                        self::FINANCIAL_UNPAID,
                        self::FINANCIAL_PARTIAL,
                        self::FINANCIAL_PENDING_BALANCE,
                        self::FINANCIAL_VENDOR_PENDING,
                        self::FINANCIAL_REFUND_PENDING,
                    ])
                    ->orWhereIn('final_status', [
                        self::FINAL_UNDER_COLLECTION,
                        self::FINAL_UNDER_REVIEW,
                    ])
                    ->orWhereHas('taskStatus', function ($sub) {
                        $sub->whereIn('slug', [
                            self::STATUS_COLLECTION_PENDING,
                            self::STATUS_VENDOR_PAYMENT_PENDING,
                            self::STATUS_FINANCE_REVIEW_PENDING,
                        ]);
                    });
            });
    }

    // ─── Relationships ─────────────────────────────────────────────

    /**
     * Get the department this task belongs to.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the task lifecycle status.
     */
    public function taskStatus()
    {
        return $this->belongsTo(TaskStatus::class, 'task_status_id');
    }

    /**
     * Get the operational business status.
     */
    public function businessStatus()
    {
        return $this->belongsTo(BusinessStatus::class, 'business_status_id');
    }

    /**
     * Get the user who assigned the task.
     */
    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Get the user this task is assigned to.
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get comments on this task.
     */
    public function comments()
    {
        return $this->hasMany(TaskComment::class)->latest();
    }

    /**
     * Get attachments on this task.
     */
    public function attachments()
    {
        return $this->hasMany(TaskAttachment::class);
    }

    // ─── Completion Workflow Relationships ──────────────────────────

    /**
     * Get the task type.
     */
    public function taskType()
    {
        return $this->belongsTo(TaskType::class);
    }

    /**
     * Get the payment purchase form.
     */
    public function paymentPurchaseForm()
    {
        return $this->hasOne(PaymentPurchaseForm::class);
    }

    /**
     * Get the receipt form.
     */
    public function receiptForm()
    {
        return $this->hasOne(ReceiptForm::class);
    }

    /**
     * Get the hotel & tour package form.
     */
    public function hotelTourForm()
    {
        return $this->hasOne(HotelTourForm::class);
    }

    /**
     * Get all form submissions for this task.
     */
    public function formSubmissions()
    {
        return $this->hasMany(TaskFormSubmission::class);
    }

    public function booking()
    {
        return $this->hasOne(Booking::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function customerReceipts()
    {
        return $this->hasMany(CustomerReceipt::class);
    }

    public function vendorPayments()
    {
        return $this->hasMany(VendorPayment::class);
    }

    public function financeApprover()
    {
        return $this->belongsTo(User::class, 'finance_approved_by');
    }

    public function managementApprover()
    {
        return $this->belongsTo(User::class, 'management_approved_by');
    }

    public function customerInteractions()
    {
        return $this->hasMany(CustomerInteraction::class)->latest('interaction_date');
    }

    public function followUps()
    {
        return $this->hasMany(FollowUp::class)->latest('followup_date');
    }

    public function customerDocuments()
    {
        return $this->hasMany(CustomerDocument::class)->latest();
    }

    public function isFinanceRelevant(): bool
    {
        return $this->final_status !== self::FINAL_CLOSED
            && ($this->current_department === self::DEPARTMENT_FINANCE
            || in_array($this->financial_status, [
                self::FINANCIAL_UNPAID,
                self::FINANCIAL_PARTIAL,
                self::FINANCIAL_PENDING_BALANCE,
                self::FINANCIAL_VENDOR_PENDING,
                self::FINANCIAL_REFUND_PENDING,
            ], true)
            || in_array($this->status, [
                self::STATUS_COLLECTION_PENDING,
                self::STATUS_VENDOR_PAYMENT_PENDING,
                self::STATUS_FINANCE_REVIEW_PENDING,
            ], true)
            || in_array($this->final_status, [
                self::FINAL_UNDER_COLLECTION,
                self::FINAL_UNDER_REVIEW,
            ], true));
    }

    // ─── Completion Workflow Helpers ────────────────────────────────

    /**
     * Get the required completion forms for this task's type.
     */
    public function getRequiredForms()
    {
        if (!$this->task_type_id || !$this->taskType) {
            return collect();
        }

        return $this->taskType->requiredForms()->where('slug', 'hotel-tour')->get();
    }

    /**
     * Check if all required forms have been submitted.
     */
    public function areAllFormsSubmitted(): bool
    {
        $required = $this->getRequiredForms();

        if ($required->isEmpty()) {
            return true;
        }

        $submittedCount = $this->formSubmissions()
            ->where('status', TaskFormSubmission::STATUS_SUBMITTED)
            ->count();

        return $submittedCount >= $required->count();
    }

    /**
     * Check if the completion process can be started.
     */
    public function canStartCompletion(): bool
    {
        return in_array($this->status, [
            self::STATUS_ASSIGNED,
            self::STATUS_IN_PROGRESS,
            self::STATUS_FOLLOW_UP,
        ]) && $this->task_type_id !== null;
    }

    /**
     * Check if task has a completion workflow requirement.
     */
    public function hasCompletionWorkflow(): bool
    {
        return $this->task_type_id !== null
            && $this->getRequiredForms()->isNotEmpty();
    }

    /**
     * Check if task is currently in the completion process.
     */
    public function isInCompletionProcess(): bool
    {
        return in_array($this->status, [
            self::STATUS_COMPLETION_PENDING,
            self::STATUS_FORMS_SUBMITTED,
            self::STATUS_OPERATIONALLY_COMPLETED,
        ]) || $this->operational_status === self::OPERATIONAL_COMPLETED;
    }
}
