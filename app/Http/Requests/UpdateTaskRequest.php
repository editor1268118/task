<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('update', $this->task);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:high,medium,low',
            'department_id' => 'nullable|exists:departments,id',
            'assigned_to' => 'required|exists:users,id',
            'start_date' => 'nullable|date',
            'due_date' => 'required|date',
            'estimated_hours' => 'nullable|numeric|min:0',
            'actual_hours' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:1000',
            'task_type_id' => 'required|exists:task_types,id',
            'customer_id' => 'nullable|exists:customers,id',
            'client_name' => 'nullable|string|max:255',
            'client_contact' => 'nullable|string|max:255',
            'additional_info' => 'nullable|string|max:2000',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $departmentId = $this->input('department_id');
            $assigneeId = $this->input('assigned_to');

            if (!$departmentId || !$assigneeId) {
                return;
            }

            $assignee = User::find($assigneeId);
            if ($assignee && (int) $assignee->department_id !== (int) $departmentId) {
                $validator->errors()->add('assigned_to', 'Selected employee does not belong to the selected department.');
            }
        });
    }
}
