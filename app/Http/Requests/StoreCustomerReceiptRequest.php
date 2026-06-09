<?php

namespace App\Http\Requests;

use App\Models\CustomerReceipt;
use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerReceiptRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('updateStatus', $this->route('task'));
    }

    public function rules()
    {
        $clientTypes = implode(',', CustomerReceipt::CLIENT_TYPES);
        $paymentModes = implode(',', CustomerReceipt::PAYMENT_MODES);

        return [
            'client_type' => "required|string|in:{$clientTypes}",
            'custom_client_type' => 'required_if:client_type,Other|nullable|string|max:255',
            'client_company_name' => 'required|string|max:255',
            'contact_no' => 'required|string|max:50',
            'amount_received' => 'required|numeric|gt:0',
            'payment_mode' => "required|string|in:{$paymentModes}",
            'custom_payment_mode' => 'required_if:payment_mode,Other|nullable|string|max:255',
            'payment_date' => 'required|date',
            'remarks' => 'nullable|string|max:2000',
        ];
    }

    public function attributes()
    {
        return [
            'payment_date' => 'date of receipt',
            'remarks' => 'comments',
        ];
    }
}
