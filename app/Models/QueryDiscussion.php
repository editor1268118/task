<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueryDiscussion extends Model
{
    public const TYPES = [
        'Call',
        'WhatsApp',
        'Email',
        'Meeting',
        'Internal Note',
        'Follow-Up',
        'Other',
    ];

    protected $fillable = [
        'query_id',
        'discussion_type',
        'message',
        'mentioned_user_id',
        'attachments',
        'created_by',
    ];

    protected $casts = [
        'attachments' => 'array',
    ];

    public function salesQuery(): BelongsTo
    {
        return $this->belongsTo(SalesQuery::class, 'query_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function mentionedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentioned_user_id');
    }

    public function canBeManagedBy(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'manager'])
            || $this->created_by === $user->id;
    }
}
