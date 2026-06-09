<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Master Operations Board Print</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #111; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .kpis { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin: 12px 0; }
        .kpi { border: 1px solid #ddd; padding: 8px; }
        .kpi small { color: #666; text-transform: uppercase; }
        .kpi strong { display: block; font-size: 14px; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 5px; vertical-align: top; }
        th { background: #f2f2f2; }
        .text-end { text-align: right; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print()">Print</button>
    <h1>Master Operations Board</h1>
    <div>Generated: {{ now()->format('d M Y h:i A') }}</div>

    <div class="kpis">
        @foreach([
            ['Total Tasks', $kpis['total_tasks']],
            ['Active Tasks', $kpis['active_tasks']],
            ['Operationally Completed', $kpis['operationally_completed']],
            ['Collection Pending', 'INR '.number_format($kpis['collection_pending_amount'], 2)],
            ['Vendor Pending', 'INR '.number_format($kpis['vendor_pending_amount'], 2)],
            ['Closed Tasks', $kpis['closed_tasks']],
            ['Revenue', 'INR '.number_format($kpis['revenue'], 2)],
            ['Expected Profit', 'INR '.number_format($kpis['expected_profit'], 2)],
        ] as [$label, $value])
            <div class="kpi"><small>{{ $label }}</small><strong>{{ $value }}</strong></div>
        @endforeach
    </div>

    <table>
        <thead>
            <tr>
                @foreach($columns as $column)
                    <th>{{ $columnLabels[$column] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    @foreach($columns as $column)
                        <td class="{{ in_array($column, ['sale_amount','total_received','pending_collection','purchase_amount','vendor_paid','vendor_pending','expected_profit'], true) ? 'text-end' : '' }}">
                            @if(in_array($column, ['sale_amount','total_received','pending_collection','purchase_amount','vendor_paid','vendor_pending','expected_profit'], true))
                                INR {{ number_format($row[$column], 2) }}
                            @else
                                {{ $row[$column] }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
