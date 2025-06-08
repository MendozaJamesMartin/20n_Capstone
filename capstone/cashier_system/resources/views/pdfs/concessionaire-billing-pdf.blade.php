<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Billing Statement</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
            line-height: 1.6;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        .bill-item {
            margin-bottom: 10px;
        }

        .footer {
            margin-top: 40px;
        }
    </style>
</head>
<body>

    <h2>Billing Statement for {{ $concessionaire->name }}</h2>

    <p>Please pay the following bills:</p>

    <div class="bill-item">
        • {{ ucfirst($bills->utility_type) }} with ₱{{ number_format($bills->bill_amount, 2) }}
    </div>

    <p class="mt-4">
        On or before <strong>{{ \Carbon\Carbon::parse($due_date)->format('F j, Y') }}</strong>
    </p>

    <div class="footer">
        <p>Thank you.</p>
    </div>

</body>
</html>
