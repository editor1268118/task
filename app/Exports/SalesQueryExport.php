<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SalesQueryExport implements FromCollection, WithHeadings
{
    public function __construct(protected Collection $queries)
    {
    }

    public function headings(): array
    {
        return [
            'Query No',
            'Query Date',
            'Service Type',
            'Client Name',
            'Company',
            'Mobile',
            'Destination',
            'Travel Date',
            'Pax',
            'Source',
            'Assigned By',
            'Assigned To',
            'Priority',
            'Stage',
            'Status',
            'Expected Sale Amount',
            'Last Follow-Up',
            'Next Follow-Up',
            'Latest Remark',
            'Age',
            'Created Date',
        ];
    }

    public function collection(): Collection
    {
        return $this->queries->map(fn ($query) => [
            $query->query_no,
            $query->query_date?->format('d M Y'),
            $query->effective_service_type,
            $query->client_name,
            $query->company_name,
            $query->mobile,
            $query->destination,
            $query->travel_date?->format('d M Y'),
            $query->number_of_pax,
            $query->source,
            $query->assignedBy?->name,
            $query->assignedTo?->name,
            $query->priority,
            $query->stage,
            $query->status,
            $query->expected_sale_amount,
            $query->last_followup_date?->format('d M Y'),
            $query->next_followup_date?->format('d M Y'),
            $query->latest_remark,
            $query->age_days . ' days',
            $query->created_at?->timezone(config('app.display_timezone'))->format('d M Y h:i A'),
        ]);
    }
}
