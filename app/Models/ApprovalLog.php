<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'approval_step_id',
        'user_id',
        'status',
        'comment',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function step()
    {
        return $this->belongsTo(ApprovalStep::class, 'approval_step_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
