<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CustomerInteraction extends Model
{
    use HasFactory, LogsActivity;

    public const TYPES = [
        'Call',
        'WhatsApp',
        'Email',
        'Meeting',
        'Pricing Shared',
        'Follow-Up',
        'Client Confirmation',
        'Complaint',
        'Other',
    ];

    protected $fillable = [
        'customer_id',
        'task_id',
        'interaction_type',
        'interaction_date',
        'notes',
        'next_followup_date',
        'created_by',
    ];

    protected $casts = [
        'interaction_date' => 'datetime',
        'next_followup_date' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
