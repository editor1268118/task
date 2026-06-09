<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'employee_id',
        'task_status_id',
        'business_status_id',
        'remarks',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function taskStatus()
    {
        return $this->belongsTo(TaskStatus::class, 'task_status_id');
    }

    public function businessStatus()
    {
        return $this->belongsTo(BusinessStatus::class, 'business_status_id');
    }
}
