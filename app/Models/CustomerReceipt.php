<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CustomerReceipt extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const APPROVED_STATUSES = [
        self::STATUS_APPROVED,
        'received',
    ];

    protected $fillable = [
        'reference_no',
        'task_id',
        'booking_id',
        'client_type',
        'custom_client_type',
        'client_company_name',
        'contact_no',
        'amount_received',
        'payment_mode',
        'custom_payment_mode',
        'payment_date',
        'remarks',
        'received_by',
        'receipt_status',
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
        'amount_received' => 'decimal:2',
        'payment_date' => 'date',
        'verified_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public const CLIENT_TYPES = [
        'B2B',
        'B2C',
        'Other',
    ];

    public const PAYMENT_MODES = [
        'NEFT / ICICI Bank - 1022',
        'UPI / ICICI Bank',
        'Payu / Payment Gateway',
        'Bank Deposit / ICICI Bank',
        'Cash',
        'Other',
    ];

    protected static function booted(): void
    {
        static::created(function (CustomerReceipt $receipt) {
            if (!$receipt->reference_no) {
                $receipt->forceFill([
                    'reference_no' => 'REC' . str_pad((string) $receipt->id, 6, '0', STR_PAD_LEFT),
                ])->saveQuietly();
            }
        });
    }

    public function getEffectiveClientTypeAttribute(): string
    {
        return $this->client_type === 'Other'
            ? ($this->custom_client_type ?? 'Other')
            : ($this->client_type ?? 'Not specified');
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

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
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
