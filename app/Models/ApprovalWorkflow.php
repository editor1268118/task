<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalWorkflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_type_id',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function taskType()
    {
        return $this->belongsTo(TaskType::class);
    }

    public function steps()
    {
        return $this->hasMany(ApprovalStep::class)->orderBy('step_order');
    }
}
