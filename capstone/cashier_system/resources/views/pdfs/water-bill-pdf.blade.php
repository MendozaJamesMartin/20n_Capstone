<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Water Billing Statement</title>
    <style>
        body {
            font-family: "Times New Roman", serif;
            font-size: 14px;
            padding: 40px;
        }
        .header {
            text-align: center;
        }
        .subheader {
            font-style: italic;
        }
        .section {
            margin: 20px 0;
        }
        .right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .note {
            margin-top: 40px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>POLYTECHNIC UNIVERSITY OF THE PHILIPPINES</h2>
        <h4>Taguig Campus</h4>
    </div>

    <div class="right">{{ \Carbon\Carbon::parse($bill->bill_date)->format('F d, Y') }}</div>

    <h3 class="text-center">MONTHLY STATEMENT OF ACCOUNT</h3>

    <div class="section">
        <p>TO: {{ $bill->concessionaire_name }}</p>
        <p>RE: {{ $bill->utility_type }}</p>
        <p>Bill Date: {{ $bill->bill_date }}</p>
        <p>Billing Period: {{ $bill->billing_period }}</p>
    </div>

    <div class="section text-center">
        <p>Current Charges: <strong>P {{ number_format($bill->current_charges, 2) }}</strong></p>
        <p>Previous Unpaid Amount: <strong>{{ number_format($bill->previous_unpaid, 2) }}</strong></p>
        <p>Total Amount Due: <strong>P <u>{{ number_format($bill->total_amount_due, 2) }}</u></strong></p>
    </div>

    <div class="note">
        <p>
            Your usual prompt payment of the above stated amount to the Cashier’s Office will be highly appreciated. Payment Due Date: {{ \Carbon\Carbon::parse($bill->due_date)->format('F d, Y') }}
        </p>

        <p>
            Present this billing to the Cashier when paying. Disregard this notice if payment has been made.
        </p>
    </div>

    <div class="section mt-5">
        <p class="right">{{ auth()->check() ? auth()->user()->first_name . ' ' . auth()->user()->last_name : 'Collecting Officer' }}
        <br>
        Collecting Officer</p>
    </div>

    <div class="section mt-5">
        <p>Noted by:</p>
        <p>Engr. Michael Zarco
        <br>
        Administrative Officer</p>
    </div>

</body>
</html>
