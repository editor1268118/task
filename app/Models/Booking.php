<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Booking extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'task_id',
        'customer_id',
        'client_name',
        'sale_amount',
        'purchase_amount',
        'expected_profit',
        'booking_type',
        'booking_status',
        'operational_status',
        'created_by',
        'approved_by',
    ];

    protected $casts = [
        'sale_amount' => 'decimal:2',
        'purchase_amount' => 'decimal:2',
        'expected_profit' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function receipts()
    {
        return $this->hasMany(CustomerReceipt::class)->latest('payment_date');
    }

    public function vendorPayments()
    {
        return $this->hasMany(VendorPayment::class)->latest('payment_date');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
