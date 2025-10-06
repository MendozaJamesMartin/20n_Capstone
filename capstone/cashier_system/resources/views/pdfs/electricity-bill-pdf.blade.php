<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Electricity Billing Statement</title>
    <style>
        body {
            font-family: "Times New Roman", serif;
            font-size: 12px;
            padding: 40px;
        }
        .header {
            text-align: center;
        }
        .subheader {
            font-style: italic;
            color: crimson;
        }
        .section {
            margin: 20px 0;
        }
        .bold {
            font-weight: bold;
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
        <p>RE: {{ $bill->utility_type }} Bill for the Month of <strong><u>{{ $bill->billing_period }}</u></strong></p>

        <p>
            As per reading of your electric meter, the total kilowatt hours for the period from {{ \Carbon\Carbon::parse($bill->bill_start_date)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($bill->bill_end_date)->format('F d, Y') }} are as follows:
        </p>
    </div>

        <div class="text-center">
            <p>Current Reading: <u>{{ number_format($bill->current_reading_kwh, 2) }}</u> KW/H</p>
            <p>Previous Reading: <u>{{ number_format($bill->previous_reading_kwh, 2) }}</u> KW/H</p>
            <p>Total: <u>{{ number_format($bill->total_kwh_used, 2) }}</u> KW/H</p>
        </div>

    <div class="section">
        <p>Hence, the computation of your electric bill is as follows:</p>

        <div class="text-center">
            <p>Total Bill (PUPT): P {{ number_format($bill->university_total_bill, 2) }}</p>
            <p>Total kWh Used: {{ number_format($bill->university_total_kwh, 2) }} (using 120 multiplier)</p>
            <p>Cost per kWh: P {{ number_format($bill->cost_per_kwh, 4) }}</p>

            <p>Concessionaire’s Consumption = {{ number_format($bill->concessionaire_kwh_used, 2) }}</p>
            <p>x Cost per kWh = {{ number_format($bill->cost_per_kwh, 4) }}</p>
            <p><strong>Total Amount: P {{ number_format($bill->total_due, 2) }}</strong></p>

            <p>Previous Unpaid Amount: P {{ number_format($bill->previous_unpaid, 2) }}</p>
            <p><strong>Total Amount Due: P {{ number_format($bill->total_due, 2) }}</strong></p>
        </div>
    </div>

    <div class="note">
        <p>
            Your usual prompt payment of the above stated amount to the Cashier’s Office will be highly appreciated.
            Payment Due Date: {{ \Carbon\Carbon::parse($bill->due_date)->format('F d, Y') }}
        <br>
            Present this billing to the Cashier when paying. Disregard this notice if payment has been made.
        </p>
    </div>

    <div class="section mt-5">
        <p class="right">{{ auth()->check() ? auth()->user()->first_name . ' ' . auth()->user()->last_name : 'Collecting Officer' }}
        <br>Collecting Officer</p>
    </div>

    <div class="section mt-5">
        <p>Noted by:</p>
        <p>Engr. Michael Zarco
        <br>Administrative Officer</p>
    </div>

</body>
</html>
