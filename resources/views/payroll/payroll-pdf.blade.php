<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payroll Report - {{ $selectedMonth }}</title>
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
        p.company {
            text-align: center;
            font-size: 14px;
            margin-bottom: 20px;
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
    <h1>Payroll Report - {{ $selectedMonth }}</h1>
    <p class="company">Nietes Design Builders</p>

    <h2>Employee Payroll</h2>
    <table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Position</th>
            <th>Days Worked</th>
            <th>Base Salary</th>
            <th>Overtime Pay</th>
            <th>Total Pay</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($payrollData as $data)
            <tr>
                <td>{{ $data['employee']->full_name }}</td>
                <td>{{ $data['employee']->position->position_name ?? 'N/A' }}</td>
                <td>{{ $data['days_worked'] }}</td>
                <td>P{{ number_format($data['salary'], 2) }}</td>
                <td>P{{ number_format($data['overtime_pay'], 2) }}</td>
                <td>P{{ number_format($data['total_pay'], 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>