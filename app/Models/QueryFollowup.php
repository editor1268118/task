<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueryFollowup extends Model
{
    protected $fillable = [
        'query_id',
        'followup_date',
        'remarks',
        'next_followup_date',
        'next_followup_time',
        'created_by',
    ];

    protected $casts = [
        'followup_date' => 'date',
        'next_followup_date' => 'date',
    ];

    public function salesQuery(): BelongsTo
    {
        return $this->belongsTo(SalesQuery::class, 'query_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
