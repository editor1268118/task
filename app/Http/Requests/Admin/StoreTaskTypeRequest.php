<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskTypeRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('manage-task-types');
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:task_types,name'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:task_types,slug'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
            'requires_operational_form' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'requires_operational_form' => $this->boolean('requires_operational_form', true),
        ]);
    }
}
