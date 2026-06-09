<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PaymentPurchaseForm extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'task_id',
        'vendor_name',
        'vendor_account_no',
        'custom_vendor_name',
        'payable_amount',
        'payment_mode',
        'custom_payment_mode',
        'payment_date',
        'payment_comments',
        'entered_by',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'payable_amount' => 'decimal:2',
        'payment_date'   => 'date',
    ];

    /**
     * Vendor options for dropdown.
     */
    const VENDOR_OPTIONS = [
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

    /**
     * Payment mode options for dropdown.
     */
    const PAYMENT_MODES = [
        'NEFT',
        'RTGS',
        'IMPS',
        'INDUS CC',
        'ICICI CC',
        'VOW CC',
        'MAYURA CC',
        'UPI (Anuj Sir)',
        'UPI (Manisha Mam)',
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
            ->setDescriptionForEvent(fn(string $eventName) => "Payment Purchase Form has been {$eventName}");
    }

    /**
     * Get the effective vendor name (custom or standard).
     */
    public function getEffectiveVendorNameAttribute(): string
    {
        return $this->vendor_name === 'Other'
            ? ($this->custom_vendor_name ?? 'Other')
            : $this->vendor_name;
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
