<?php

namespace App\Http\Requests;

use App\Models\SalesQuery;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSalesQueryRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $adultCount = $this->input('adult_count');
        $childCount = $this->input('child_count');

        if ($adultCount !== null || $childCount !== null) {
            $this->merge([
                'number_of_pax' => max(0, (int) $adultCount) + max(0, (int) $childCount),
            ]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()->hasRole('super-admin') || $this->user()->can('create-queries');
    }

    public function rules(): array
    {
        return [
            'query_date' => ['required', 'date'],
            'query_title' => ['required', 'string', 'max:255'],
            'service_type' => ['required', Rule::in(SalesQuery::SERVICE_TYPES)],
            'service_type_other' => ['nullable', 'string', 'max:255'],
            'client_name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'max:30'],
            'alternate_mobile' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'destination' => ['nullable', 'string', 'max:255'],
            'travel_date' => ['nullable', 'date'],
            'travel_month' => ['nullable', 'date_format:Y-m'],
            'number_of_pax' => ['required', 'integer', 'min:1'],
            'adult_count' => ['nullable', 'integer', 'min:0'],
            'child_count' => ['nullable', 'integer', 'min:0'],
            'source' => ['required', Rule::in(SalesQuery::SOURCES)],
            'priority' => ['required', Rule::in(SalesQuery::PRIORITIES)],
            'assigned_to' => ['required', 'exists:users,id'],
            'stage' => ['required', Rule::in(SalesQuery::STAGES)],
            'status' => ['required', Rule::in(['Open', 'Confirmed', 'Lost', 'Cancelled'])],
            'next_followup_date' => ['nullable', 'required_if:stage,Follow Up,Pricing Shared,Negotiation', 'date'],
            'lost_reason' => ['nullable', 'required_if:status,Lost', Rule::in(SalesQuery::LOST_REASONS)],
            'latest_remark' => ['nullable', 'string', 'max:5000'],
            'duplicate_confirmed' => ['nullable', 'boolean'],
        ];
    }
}
