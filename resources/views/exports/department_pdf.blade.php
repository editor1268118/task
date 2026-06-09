<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Department Performance Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 24px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Department Performance Report</h1>
        <p>Generated on {{ now()->format('M d, Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Department</th>
                <th>Total Tasks</th>
                <th>Completed</th>
                <th>Overdue</th>
                <th>Completion Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach($departments as $dept)
                @php
                    $rate = $dept->total_tasks > 0 ? round(($dept->completed_tasks / $dept->total_tasks) * 100, 1) : 0;
                @endphp
                <tr>
                    <td><strong>{{ $dept->name }}</strong></td>
                    <td>{{ $dept->total_tasks }}</td>
                    <td style="color: green;">{{ $dept->completed_tasks }}</td>
                    <td style="color: red;">{{ $dept->overdue_tasks }}</td>
                    <td>{{ $rate }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
