<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CustomerDocument extends Model
{
    use HasFactory, LogsActivity;

    public const TYPES = ['Passport', 'GST', 'PAN', 'Approval Letter', 'Requirement Documents', 'Other'];

    protected $fillable = [
        'customer_id',
        'task_id',
        'document_type',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'remarks',
        'uploaded_by',
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

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
