<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Electricity Billing Statement</title>
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

.center-table {
    width: 100%;
    text-align: center; /* centers the inline-table itself */
    margin: 10px 0;
}

.center-table table {
    display: inline-table;       /* ✅ makes the table center align within the parent */
    border-collapse: collapse;
    font-size: 12px;
    text-align: left;
    width: 65%;                  /* adjust width as needed (60–70% recommended) */
}

.center-table td {
    padding: 3px 5px;
    vertical-align: top;
}

.center-table td:first-child {
    text-align: right;
    padding-right: 15px;
    width: 55%;                  /* keep consistent spacing */
}

.center-table td:last-child {
    text-align: left;
    width: 45%;
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
            As per reading of your electric meter, the total kilowatt hours for the period from 
            {{ \Carbon\Carbon::parse($bill->bill_start_date)->format('F d, Y') }} 
            to {{ \Carbon\Carbon::parse($bill->bill_end_date)->format('F d, Y') }} are as follows:
        </p>
    </div>

    <!-- Reading Section -->
<div class="center-table">
    <table>
        <tr>
            <td>Current Reading:</td>
            <td><u>{{ number_format($bill->current_reading_kwh, 2) }}</u> KW/H</td>
        </tr>
        <tr>
            <td>Previous Reading:</td>
            <td><u>{{ number_format($bill->previous_reading_kwh, 2) }}</u> KW/H</td>
        </tr>
        <tr>
            <td>Total:</td>
            <td><u>{{ number_format($bill->concessionaire_kwh_used, 2) }}</u> KW/H</td>
        </tr>
    </table>
</div>

    <div class="section">
        <p>Hence, the computation of your electric bill is as follows:</p>
    </div>

<!-- Computation Section -->
<div class="center-table">
    <table>
        <tr><td>Total Bill (PUPT):</td><td>P {{ number_format($bill->university_total_bill, 2) }}</td></tr>
        <tr><td>Total kWh Used:</td><td>{{ number_format($bill->university_total_kwh) }} (using 120 multiplier)</td></tr>
        <tr><td>Cost per kWh:</td><td>P {{ number_format($bill->cost_per_kwh, 2) }}</td></tr>
                <tr><td>&nbsp;</td></tr>
        <tr><td>Concessionaire’s Consumption =</td><td>{{ number_format($bill->concessionaire_kwh_used) }}</td></tr>
        <tr><td>x Cost per kWh =</td><td>{{ number_format($bill->cost_per_kwh) }}</td></tr>
        <tr class="bold"><td>Total Amount:</td><td>P {{ number_format($bill->current_charges, 2) }}</td></tr>
                <tr><td>&nbsp;</td></tr>
        <tr><td>Previous Unpaid Amount:</td><td>P {{ number_format($bill->previous_unpaid, 2) }}</td></tr>
        <tr class="bold"><td style="text-decoration: underline;">Total Amount Due:</td><td style="text-decoration: underline;">P {{ number_format($bill->total_due, 2) }}</td></tr>
    </table>
</div>

    <div class="note">
        <p>
            Your usual prompt payment of the above stated amount to the Cashier’s Office will be highly appreciated.
            Payment Due Date: {{ \Carbon\Carbon::parse($bill->due_date)->format('F d, Y') }}
        <br>
            Present this billing to the Cashier when paying. Disregard this notice if payment has been made.
        </p>
    </div>

    <div class="section mt-5" style="text-align: right;">
        <p style="display: inline-block; text-align: left;">
            @if(auth()->check())
                {{ auth()->user()->first_name }}
                @if(!empty(auth()->user()->middle_name))
                    {{ ' ' . substr(auth()->user()->middle_name, 0, 1) . '.' }}
                @endif
                {{ ' ' . auth()->user()->last_name }}
            @else
                Collecting Officer
            @endif
            <br>
            Collecting Officer
        </p>
    </div>

    <div class="section mt-5">
        <p>Noted by:</p>
        <p>Engr. Michael Zarco
        <br>Administrative Officer</p>
    </div>

</body>
</html>
