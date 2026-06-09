<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ─── Scopes ────────────────────────────────────────────────────

    /**
     * Scope to only active task types.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ─── Relationships ─────────────────────────────────────────────

    /**
     * Tasks belonging to this type.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Completion forms required for this task type.
     */
    public function completionForms()
    {
        return $this->belongsToMany(CompletionForm::class, 'task_type_forms')
                    ->withPivot(['sort_order', 'is_required'])
                    ->orderByPivot('sort_order')
                    ->withTimestamps();
    }

    /**
     * Get only the required completion forms, ordered by sort_order.
     */
    public function requiredForms()
    {
        return $this->completionForms()->wherePivot('is_required', true);
    }
}
