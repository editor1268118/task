<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Customer extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public const TYPES = ['B2B', 'B2C', 'Corporate', 'Travel Agent', 'Government', 'Event Client', 'Other'];
    public const STATUSES = ['Active', 'Inactive', 'Blacklisted'];

    protected $fillable = [
        'customer_code',
        'customer_type',
        'company_name',
        'contact_person',
        'mobile',
        'alternate_mobile',
        'email',
        'gst_number',
        'address',
        'city',
        'state',
        'country',
        'remarks',
        'status',
        'created_by',
    ];

    public static function generateCustomerCode(): string
    {
        $last = static::withTrashed()->latest('id')->first();
        $next = $last ? ((int) substr($last->customer_code, 3)) + 1 : 1;

        return 'CUS' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Customer $customer) {
            if (!$customer->customer_code) {
                $customer->customer_code = static::generateCustomerCode();
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function queries()
    {
        return $this->hasMany(SalesQuery::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function interactions()
    {
        return $this->hasMany(CustomerInteraction::class)->latest('interaction_date');
    }

    public function followUps()
    {
        return $this->hasMany(FollowUp::class)->latest('followup_date');
    }

    public function documents()
    {
        return $this->hasMany(CustomerDocument::class)->latest();
    }
}
