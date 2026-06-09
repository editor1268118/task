<?php

namespace App\Http\Requests;

use App\Models\PaymentPurchaseForm;
use Illuminate\Foundation\Http\FormRequest;

class StorePaymentPurchaseFormRequest extends FormRequest
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
        $vendorOptions  = implode(',', PaymentPurchaseForm::VENDOR_OPTIONS);
        $paymentModes   = implode(',', PaymentPurchaseForm::PAYMENT_MODES);

        return [
            'vendor_name'         => "required|string|in:{$vendorOptions}",
            'vendor_account_no'   => 'nullable|string|max:255',
            'custom_vendor_name'  => 'required_if:vendor_name,Other|nullable|string|max:255',
            'payable_amount'      => 'required|numeric|min:0',
            'payment_mode'        => "required|string|in:{$paymentModes}",
            'custom_payment_mode' => 'required_if:payment_mode,Other|nullable|string|max:255',
            'payment_date'        => 'required|date',
            'payment_comments'    => 'nullable|string|max:2000',
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'custom_vendor_name.required_if'  => 'Please specify the vendor name when "Other" is selected.',
            'custom_payment_mode.required_if' => 'Please specify the payment mode when "Other" is selected.',
        ];
    }
}
