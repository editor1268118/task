<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ReceiptForm extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'task_id',
        'client_type',
        'client_company_name',
        'contact_no',
        'receipt_date',
        'payment_mode',
        'custom_payment_mode',
        'amount_received',
        'comments',
        'entered_by',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount_received' => 'decimal:2',
        'receipt_date'    => 'date',
    ];

    /**
     * Client type options.
     */
    const CLIENT_TYPES = [
        'B2B',
        'B2C',
        'Other',
    ];

    /**
     * Payment mode options for receipt.
     */
    const PAYMENT_MODES = [
        'NEFT / ICICI Bank - 1022',
        'UPI / ICICI Bank',
        'Payu / Payment Gateway',
        'Bank Deposit / ICICI Bank',
        'Cash',
        'Other',
    ];

    /**
     * Activity log configuration.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Receipt Form has been {$eventName}");
    }

    /**
     * Get the effective payment mode (custom or standard).
     */
    public function getEffectivePaymentModeAttribute(): string
    {
        return $this->payment_mode === 'Other'
            ? ($this->custom_payment_mode ?? 'Other')
            : $this->payment_mode;
    }

    // ─── Relationships ─────────────────────────────────────────────

    /**
     * The task this form belongs to.
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * The user who entered this form.
     */
    public function enteredBy()
    {
        return $this->belongsTo(User::class, 'entered_by');
    }
}
