<?php

namespace App\Http\Requests;

use App\Models\VendorPayment;
use Illuminate\Foundation\Http\FormRequest;

class StoreVendorPaymentRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('updateStatus', $this->route('task'));
    }

    public function rules()
    {
        $vendorOptions = implode(',', VendorPayment::VENDOR_OPTIONS);
        $paymentModes = implode(',', VendorPayment::PAYMENT_MODES);

        return [
            'vendor_id' => 'nullable|integer|min:1',
            'vendor_name' => "required|string|in:{$vendorOptions}",
            'custom_vendor_name' => 'required_if:vendor_name,Other|nullable|string|max:255',
            'vendor_account_no' => 'nullable|string|max:255',
            'amount_paid' => 'required|numeric|gt:0',
            'payment_mode' => "required|string|in:{$paymentModes}",
            'custom_payment_mode' => 'required_if:payment_mode,Other|nullable|string|max:255',
            'payment_date' => 'required|date',
            'remarks' => 'nullable|string|max:2000',
        ];
    }

    public function attributes()
    {
        return [
            'amount_paid' => 'payable amount',
            'remarks' => 'payment comments',
        ];
    }
}
