<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskAttachmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('attach', $this->task);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,docx,xlsx|max:10240', // Max 10MB
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages()
    {
        return [
            'file.mimes' => 'The attachment must be a file of type: pdf, jpg, jpeg, png, docx, xlsx.',
            'file.max' => 'The attachment must not be greater than 10MB.',
        ];
    }
}
