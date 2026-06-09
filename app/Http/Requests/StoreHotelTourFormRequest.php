<?php

namespace App\Http\Requests;

use App\Models\HotelTourForm;
use Illuminate\Foundation\Http\FormRequest;

class StoreHotelTourFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('updateStatus', $this->route('task'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $bookingTypes = implode(',', HotelTourForm::BOOKING_TYPES);
        $serviceTypes = implode(',', HotelTourForm::SERVICE_TYPES);
        $tripTypes    = implode(',', HotelTourForm::TRIP_TYPES);

        return [
            'booking_date'         => 'required|date',
            'state'                => 'nullable|string|max:255',
            'city'                 => 'required|string|max:255',
            'client_type'          => 'nullable|string|max:255',
            'billed_to'            => 'nullable|string|max:255',
            'booking_type'         => "required|string|in:{$bookingTypes}",
            'service_type'         => "required|string|in:{$serviceTypes}",
            'trip_type'            => "required|string|in:{$tripTypes}",
            'no_of_pax'            => 'required|integer|min:1',
            'pax_name'             => 'nullable|string|max:500',
            'no_of_rooms'          => 'nullable|integer|min:1',
            'confirmation_no'      => 'nullable|string|max:255',
            'hotel_room_type'      => 'nullable|string|max:255',
            'check_in_date'        => 'nullable|date',
            'check_out_date'       => 'nullable|date|after_or_equal:check_in_date',
            'sale_amount'          => 'required|numeric|min:0',
            'purchased_amount'     => 'required|numeric|min:0',
            'sale_gst'             => 'nullable|numeric|min:0',
            'gst_expected'         => 'nullable|numeric|min:0',
            'tcs_calculation'      => 'nullable|numeric|min:0',
            'vendor_name'          => 'required|string|max:255',
            'vendor_tds'           => 'nullable|numeric|min:0',
            'discount'             => 'nullable|numeric|min:0',
        ];
    }
}
