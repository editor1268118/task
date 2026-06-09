<?php

namespace App\Http\Requests;

use App\Models\QueryDiscussion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQueryDiscussionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['super-admin', 'manager', 'employee']);
    }

    public function rules(): array
    {
        return [
            'discussion_type' => ['required', Rule::in(QueryDiscussion::TYPES)],
            'message' => ['required', 'string', 'max:5000'],
            'mentioned_user_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
