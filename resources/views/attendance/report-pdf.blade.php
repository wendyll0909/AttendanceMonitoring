<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Attendance Report - {{ $selectedDate }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        h1 {
            text-align: center;
            font-size: 18px;
        }
        h2 {
            font-size: 16px;
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>Attendance Report - {{ $selectedDate }}</h1>
    <p style="text-align: center;">Nietes Design Builders</p>

    <h2>Present Employees</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Position</th>
                <th>Check-In Time</th>
                <th>Check-Out Time</th>
                <th>Late Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($present as $attendance)
                <tr>
                    <td>{{ $attendance->employee->full_name }}</td>
                    <td>{{ $attendance->employee->position->position_name ?? 'N/A' }}</td>
                    <td>{{ $attendance->check_in_time ?? 'N/A' }}</td>
                    <td>{{ $attendance->check_out_time ?? 'N/A' }}</td>
                    <td>{{ $attendance->late_status ? 'Yes' : 'No' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Absent Employees</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Position</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($absent as $employee)
                <tr>
                    <td>{{ $employee->employee->full_name }}</td>
                    <td>{{ $employee->employee->position->position_name ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>