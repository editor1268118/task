<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class HotelTourForm extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'task_id',
        'booking_date',
        'state',
        'city',
        'client_type',
        'billed_to',
        'booking_type',
        'service_type',
        'trip_type',
        'no_of_pax',
        'pax_name',
        'no_of_rooms',
        'confirmation_no',
        'hotel_room_type',
        'check_in_date',
        'check_out_date',
        'sale_amount',
        'purchased_amount',
        'sale_gst',
        'gst_expected',
        'tcs_calculation',
        'vendor_name',
        'total_vendor_payment',
        'vendor_tds',
        'discount',
        'payment_received',
        'entered_by',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'booking_date'         => 'date',
        'check_in_date'        => 'date',
        'check_out_date'       => 'date',
        'no_of_pax'            => 'integer',
        'no_of_rooms'          => 'integer',
        'sale_amount'          => 'decimal:2',
        'purchased_amount'     => 'decimal:2',
        'sale_gst'             => 'decimal:2',
        'gst_expected'         => 'decimal:2',
        'tcs_calculation'      => 'decimal:2',
        'total_vendor_payment' => 'decimal:2',
        'vendor_tds'           => 'decimal:2',
        'discount'             => 'decimal:2',
        'payment_received'     => 'decimal:2',
    ];

    /**
     * Booking type options.
     */
    const BOOKING_TYPES = [
        'Domestic Hotel',
        'Domestic Taxi',
        'Domestic Package',
        'Domestic Sightseeing',
        'International Hotel',
        'International Taxi',
        'International Package',
        'International Sightseeing',
        'VISA',
        'Cruise',
        'Dummy Booking',
    ];

    /**
     * Service type options.
     */
    const SERVICE_TYPES = [
        'Booked',
        'Cancelled',
        'Refunded',
        'Rescheduled',
        'Commission',
    ];

    /**
     * Trip type options.
     */
    const TRIP_TYPES = [
        'FIT',
        'GIT',
        'Event',
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
            ->setDescriptionForEvent(fn(string $eventName) => "Hotel & Tour Form has been {$eventName}");
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
