<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompletionForm extends Model
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
        'display_name',
        'description',
        'form_class',
        'view_partial',
        'sort_order',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    // ─── Scopes ────────────────────────────────────────────────────

    /**
     * Scope to only active forms.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ─── Relationships ─────────────────────────────────────────────

    /**
     * Task types that require this form.
     */
    public function taskTypes()
    {
        return $this->belongsToMany(TaskType::class, 'task_type_forms')
                    ->withPivot(['sort_order', 'is_required'])
                    ->withTimestamps();
    }

    /**
     * Form submissions for this completion form.
     */
    public function submissions()
    {
        return $this->hasMany(TaskFormSubmission::class);
    }
}
