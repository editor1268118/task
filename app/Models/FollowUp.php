<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class FollowUp extends Model
{
    use HasFactory, LogsActivity;

    public const STATUSES = ['Pending', 'Completed', 'Missed', 'Cancelled', 'Rescheduled'];
    public const PRIORITIES = ['Low', 'Medium', 'High', 'Urgent'];

    protected $fillable = [
        'customer_id',
        'task_id',
        'followup_date',
        'priority',
        'status',
        'notes',
        'assigned_to',
        'created_by',
        'completed_by',
        'completed_at',
    ];

    protected $casts = [
        'followup_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    public function scopeMissed($query)
    {
        return $query->where(function ($q) {
            $q->where('status', 'Missed')
                ->orWhere(fn ($sub) => $sub->where('status', 'Pending')->whereDate('followup_date', '<', today()));
        });
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
