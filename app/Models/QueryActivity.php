<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueryActivity extends Model
{
    protected $fillable = [
        'query_id',
        'activity_at',
        'user_id',
        'action',
        'remarks',
        'properties',
    ];

    protected $casts = [
        'activity_at' => 'datetime',
        'properties' => 'array',
    ];

    public function salesQuery(): BelongsTo
    {
        return $this->belongsTo(SalesQuery::class, 'query_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
