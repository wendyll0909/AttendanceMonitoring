<!DOCTYPE html>
<html>
<head>
    <title>Attendance Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1, h2, h3 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Attendance Report</h1>
        <h2>Nietes Design Builders</h2>
        <h3>Date Range: {{ \Carbon\Carbon::parse($startDate)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('F d, Y') }}</h3>
    </div>

    <h2>Present Employees</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Position</th>
                <th>Total Days Present</th>
                <th>Late Days</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($present as $record)
                <tr>
                    <td>{{ $record['employee']->full_name }}</td>
                    <td>{{ $record['employee']->position->position_name ?? 'N/A' }}</td>
                    <td>{{ $record['total_days'] }}</td>
                    <td>{{ $record['late_days'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center;">No employees present in this date range</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Absent Employees</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Position</th>
                <th>Absent Days</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($absent as $record)
                <tr>
                    <td>{{ $record['employee']->full_name }}</td>
                    <td>{{ $record['employee']->position->position_name ?? 'N/A' }}</td>
                    <td>{{ $record['absent_days'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center;">No employees absent in this date range</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>