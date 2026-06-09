<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class VendorPayment extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const APPROVED_STATUSES = [
        self::STATUS_APPROVED,
        'paid',
    ];

    protected $fillable = [
        'reference_no',
        'task_id',
        'booking_id',
        'vendor_id',
        'vendor_name',
        'vendor_account_no',
        'custom_vendor_name',
        'amount_paid',
        'payment_mode',
        'custom_payment_mode',
        'payment_date',
        'remarks',
        'payment_status',
        'entered_by',
        'verified_at',
        'verified_by',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'deleted_by',
        'delete_reason',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'payment_date' => 'date',
        'verified_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public const VENDOR_OPTIONS = [
        'Indigo KTDEL409',
        'Air India Exp 1120AMIGOS_ADMIN',
        'ALLIANCE AIR MANISHA@910619',
        'FrequencyWeekly (IRCTC B2B)',
        'IATA BSP',
        'IRCTC (ID-MANISHA BANSAL)',
        'IRCTC (ID-Rabecca)',
        'Alliance Air (OPS1@910619)',
        'Indigo STAN',
        'Riya Travels',
        'ClearTrip',
        'Make My Trip',
        'Ottila',
        'Trip Jack',
        'GRN Connect',
        'My Biz (MMT)',
        'SKTC',
        'IndusInd CC',
        'VOW CC',
        'Mayura CC',
        'Other',
    ];

    public const PAYMENT_MODES = [
        'NEFT',
        'RTGS ( Urgent )',
        'IMPS ( Very Urgent )',
        'INDUS CC',
        'ICICI CC',
        'VOW CC',
        'MAYURA CC',
        'UPI (Anuj Sir)',
        'UPI (Manisha Mam)',
        'Other',
    ];

    protected static function booted(): void
    {
        static::created(function (VendorPayment $payment) {
            if (!$payment->reference_no) {
                $payment->forceFill([
                    'reference_no' => 'VPM' . str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT),
                ])->saveQuietly();
            }
        });
    }

    public function getEffectiveVendorNameAttribute(): string
    {
        return $this->vendor_name === 'Other'
            ? ($this->custom_vendor_name ?? 'Other')
            : ($this->vendor_name ?? 'Not specified');
    }

    public function getEffectivePaymentModeAttribute(): string
    {
        return $this->payment_mode === 'Other'
            ? ($this->custom_payment_mode ?? 'Other')
            : $this->payment_mode;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->dontSubmitEmptyLogs();
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function enteredBy()
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
