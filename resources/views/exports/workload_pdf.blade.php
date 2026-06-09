<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Workload Monitoring Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 24px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        .warning { color: red; font-weight: bold; }
        .healthy { color: green; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Employee Workload Monitoring Report</h1>
        <p>Generated on {{ now()->format('M d, Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Employee</th>
                <th>Department</th>
                <th>Active Tasks</th>
                <th>Pending</th>
                <th>Overdue</th>
                <th>Est. Hours</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($workloads as $emp)
                <tr>
                    <td>{{ $emp->name }}</td>
                    <td>{{ $emp->department?->name ?? 'N/A' }}</td>
                    <td>{{ $emp->active_tasks }}</td>
                    <td>{{ $emp->pending_tasks }}</td>
                    <td style="color: red;">{{ $emp->overdue_tasks }}</td>
                    <td>{{ $emp->assigned_tasks_sum_estimated_hours ?? 0 }} hrs</td>
                    <td>
                        @if($emp->is_overloaded)
                            <span class="warning">OVERLOADED</span>
                        @else
                            <span class="healthy">Healthy</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
