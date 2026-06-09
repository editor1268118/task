<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskFormSubmission extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'task_id',
        'completion_form_id',
        'form_type',
        'form_id',
        'submitted_by',
        'submitted_at',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    /**
     * Status constants.
     */
    const STATUS_PENDING   = 'pending';
    const STATUS_DRAFT     = 'draft';
    const STATUS_SUBMITTED = 'submitted';

    // ─── Relationships ─────────────────────────────────────────────

    /**
     * The task this submission belongs to.
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * The completion form definition.
     */
    public function completionForm()
    {
        return $this->belongsTo(CompletionForm::class);
    }

    /**
     * The user who submitted this form.
     */
    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Polymorphic relation to the actual form data (PaymentPurchaseForm, ReceiptForm, etc.).
     */
    public function form()
    {
        return $this->morphTo('form', 'form_type', 'form_id');
    }

    // ─── Scopes ────────────────────────────────────────────────────

    /**
     * Scope to only submitted forms.
     */
    public function scopeSubmitted($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }

    /**
     * Scope to pending or draft forms.
     */
    public function scopeIncomplete($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_DRAFT]);
    }
}
