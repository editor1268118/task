<?php

namespace App\Http\Requests;

use App\Models\QueryDiscussion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQueryFollowupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['super-admin', 'manager', 'employee']);
    }

    public function rules(): array
    {
        return [
            'discussion_type' => ['nullable', Rule::in(QueryDiscussion::TYPES)],
            'followup_date' => ['required', 'date'],
            'remarks' => ['required', 'string', 'max:5000'],
            'next_followup_date' => ['nullable', 'date', 'after_or_equal:followup_date'],
            'next_followup_time' => ['nullable', 'date_format:H:i'],
        ];
    }
}
