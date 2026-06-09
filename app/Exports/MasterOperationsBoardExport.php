<?php

namespace App\Exports;

use App\Services\MasterOperationsBoardService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MasterOperationsBoardExport implements FromCollection, WithHeadings
{
    public function __construct(
        protected Collection $rows,
        protected array $columns
    ) {
    }

    public function collection()
    {
        return $this->rows->map(function (array $row) {
            return collect($this->columns)
                ->mapWithKeys(fn ($column) => [MasterOperationsBoardService::COLUMN_LABELS[$column] => $row[$column] ?? null])
                ->all();
        });
    }

    public function headings(): array
    {
        return collect($this->columns)
            ->map(fn ($column) => MasterOperationsBoardService::COLUMN_LABELS[$column])
            ->all();
    }
}
