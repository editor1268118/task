<?php

namespace App\Http\Requests;

use App\Models\ReceiptForm;
use Illuminate\Foundation\Http\FormRequest;

class StoreReceiptFormRequest extends FormRequest
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
        $clientTypes  = implode(',', ReceiptForm::CLIENT_TYPES);
        $paymentModes = implode(',', ReceiptForm::PAYMENT_MODES);

        return [
            'client_type'         => "required|string|in:{$clientTypes}",
            'client_company_name' => 'required|string|max:255',
            'contact_no'          => 'required|string|max:50',
            'receipt_date'        => 'required|date',
            'payment_mode'        => "required|string|in:{$paymentModes}",
            'custom_payment_mode' => 'required_if:payment_mode,Other|nullable|string|max:255',
            'amount_received'     => 'required|numeric|min:0',
            'comments'            => 'nullable|string|max:2000',
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
            'custom_payment_mode.required_if' => 'Please specify the payment mode when "Other" is selected.',
        ];
    }
}
