<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Productivity Report</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 24px; }
        .details { margin-bottom: 30px; }
        .details p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; }
        .highlight { font-weight: bold; }
        .success { color: green; }
        .danger { color: red; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Employee Productivity Report</h1>
        <p>Generated on {{ now()->format('M d, Y H:i:s') }}</p>
    </div>

    <div class="details">
        <p><strong>Employee:</strong> {{ $targetUser->name }} (ID: {{ $targetUser->employee_id ?? 'N/A' }})</p>
        <p><strong>Department:</strong> {{ $targetUser->department?->name ?? 'None' }}</p>
        <p><strong>Email:</strong> {{ $targetUser->email }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Metric</th>
                <th>Value</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Assigned Tasks</td>
                <td class="highlight">{{ $metrics['total'] }}</td>
            </tr>
            <tr>
                <td>Completed Tasks</td>
                <td class="highlight success">{{ $metrics['completed'] }}</td>
            </tr>
            <tr>
                <td>Pending Tasks</td>
                <td>{{ $metrics['pending'] }}</td>
            </tr>
            <tr>
                <td>Overdue Tasks</td>
                <td class="highlight danger">{{ $metrics['overdue'] }}</td>
            </tr>
            <tr>
                <td>Active Tasks (In Progress/On Hold)</td>
                <td>{{ $metrics['active'] }}</td>
            </tr>
            <tr>
                <td>Cancelled Tasks</td>
                <td>{{ $metrics['cancelled'] }}</td>
            </tr>
            <tr>
                <td><strong>Overall Completion Rate</strong></td>
                <td><strong>{{ $metrics['completionRate'] }}%</strong></td>
            </tr>
        </tbody>
    </table>

    <p style="text-align: center; color: #777; font-size: 12px; margin-top: 50px;">
        This is an automatically generated report by Amigos TMS.
    </p>
</body>
</html>
