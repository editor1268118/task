<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; color: #333; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-top: 4px solid #0d6efd; }
        .header { margin-bottom: 20px; }
        .metrics-card { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
        .btn { display: inline-block; padding: 10px 20px; background-color: #0d6efd; color: #fff; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Daily Task Summary</h2>
            <p>Hello {{ $user->name }},</p>
            <p>Here is the daily overview of your tasks for {{ now()->format('l, F j, Y') }}.</p>
        </div>

        <div class="metrics-card">
            <table>
                <tr>
                    <th>Total Active Tasks</th>
                    <td>{{ $metrics['active'] ?? 0 }}</td>
                </tr>
                <tr>
                    <th>Pending Tasks</th>
                    <td>{{ $metrics['pending'] ?? 0 }}</td>
                </tr>
                <tr>
                    <th>Completed Today</th>
                    <td style="color: green;">{{ $metrics['completed_today'] ?? 0 }}</td>
                </tr>
                <tr>
                    <th>Overdue Tasks</th>
                    <td style="color: red; font-weight: bold;">{{ $metrics['overdue'] ?? 0 }}</td>
                </tr>
            </table>
        </div>

        <p>Please log in to the Amigos TMS dashboard to manage your workflow and address any overdue tasks.</p>
        
        <p style="text-align: center; margin-top: 30px;">
            <a href="{{ route('dashboard') }}" class="btn">Go to Dashboard</a>
        </p>
    </div>
</body>
</html>
