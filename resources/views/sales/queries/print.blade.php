<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Query Register Print</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 5px; text-align: left; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <h2>Query Register</h2>
    <p>Generated: {{ now()->timezone(config('app.display_timezone'))->format('d M Y h:i A') }}</p>
    <table>
        <thead><tr><th>Query No</th><th>Date</th><th>Service</th><th>Client</th><th>Company</th><th>Mobile</th><th>Destination</th><th>Next Follow-Up</th><th>Time</th><th>Assigned To</th><th>Stage</th><th>Status</th><th>Age</th></tr></thead>
        <tbody>
            @foreach($queries as $query)
                <tr>
                    <td>{{ $query->query_no }}</td>
                    <td>{{ $query->query_date?->format('d M Y') }}</td>
                    <td>{{ $query->effective_service_type }}</td>
                    <td>{{ $query->client_name }}</td>
                    <td>{{ $query->company_name }}</td>
                    <td>{{ $query->mobile }}</td>
                    <td>{{ $query->destination }}</td>
                    <td>{{ $query->next_followup_date?->format('d M Y') ?? '-' }}</td>
                    <td>{{ $query->formatted_next_followup_time }}</td>
                    <td>{{ $query->assignedTo?->name }}</td>
                    <td>{{ $query->stage }}</td>
                    <td>{{ $query->status }}</td>
                    <td>{{ $query->age_days }} days</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <script>window.print();</script>
</body>
</html>
