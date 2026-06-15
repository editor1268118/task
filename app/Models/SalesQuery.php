<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesQuery extends Model
{
    use HasFactory;

    protected $table = 'queries';

    public const SERVICE_TYPES = [
        'Hotel Booking',
        'Tour Package',
        'Flight Booking',
        'Train Ticket',
        'Bus Booking',
        'VISA',
        'Cruise',
        'Corporate Travel',
        'Event Travel',
    ];

    public const SOURCES = [
        'Website',
        'WhatsApp',
        'Phone Call',
        'Reference',
        'Existing Client',
        'Email',
        'Walk-In',
        'Other',
    ];

    public const PRIORITIES = ['Low', 'Medium', 'High', 'Urgent'];

    public const STAGES = [
        'New Query',
        'Contacted',
        'Follow Up',
        'Pricing Shared',
        'Negotiation',
    ];

    public const FOLLOWUP_REQUIRED_STAGES = [
        'Follow Up',
        'Pricing Shared',
        'Negotiation',
    ];

    public const STATUSES = ['Open', 'Confirmed', 'Lost', 'Cancelled', 'Converted'];

    public const LOST_REASONS = [
        'Price High',
        'Budget Issue',
        'Competitor Won',
        'Travel Cancelled',
        'No Response',
        'Not Interested',
        'Other',
    ];

    protected $fillable = [
        'query_no',
        'query_title',
        'customer_id',
        'query_date',
        'service_type',
        'service_type_other',
        'client_name',
        'company_name',
        'mobile',
        'alternate_mobile',
        'email',
        'destination',
        'travel_date',
        'travel_month',
        'number_of_pax',
        'adult_count',
        'child_count',
        'source',
        'priority',
        'assigned_by',
        'assigned_to',
        'stage',
        'status',
        'last_followup_date',
        'next_followup_date',
        'lost_reason',
        'latest_remark',
        'converted_task_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'query_date' => 'date',
        'travel_date' => 'date',
        'last_followup_date' => 'date',
        'next_followup_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $query) {
            if (!$query->query_no) {
                $query->query_no = self::generateQueryNumber();
            }
        });
    }

    public static function generateQueryNumber(): string
    {
        $last = self::query()->latest('id')->value('query_no');
        $next = $last ? ((int) substr($last, 3)) + 1 : 1;

        return 'QRY' . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function convertedTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'converted_task_id');
    }

    public function followups(): HasMany
    {
        return $this->hasMany(QueryFollowup::class, 'query_id')->latest('followup_date');
    }

    public function discussions(): HasMany
    {
        return $this->hasMany(QueryDiscussion::class, 'query_id')->latest();
    }

    public function activities(): HasMany
    {
        return $this->hasMany(QueryActivity::class, 'query_id')->latest('activity_at');
    }

    public function getEffectiveServiceTypeAttribute(): string
    {
        return $this->service_type === 'Other' && $this->service_type_other
            ? $this->service_type_other
            : $this->service_type;
    }

    public function getAgeDaysAttribute(): int
    {
        return max(0, (int) $this->created_at?->startOfDay()->diffInDays(now()->startOfDay()));
    }

    public function getAgeColorAttribute(): string
    {
        return match (true) {
            $this->age_days <= 7 => 'success',
            $this->age_days <= 15 => 'warning',
            $this->age_days <= 30 => 'orange',
            default => 'danger',
        };
    }

    public function canConvert(): bool
    {
        return $this->status === 'Confirmed' && !$this->converted_task_id;
    }
}
