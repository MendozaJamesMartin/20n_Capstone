<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Concessionaire Receipt</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            padding: 20px;
        }

        h3 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .bordered th,
        .bordered td {
            border: 1px solid #000;
            padding: 8px;
        }

        .no-border td {
            padding: 5px;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>

<h3><strong>Concessionaire Transaction Receipt</strong></h3>

    <table class="no-border">
        <tr>
            <td><strong>Transaction ID:</strong> {{ $TransactionDetails[0]->transaction_id }}</td>
            <td><strong>Date:</strong> {{ $TransactionDetails[0]->transaction_date }}</td>
            <td><strong>Receipt No.:</strong> {{ $TransactionDetails[0]->receipt_number }}</td>
        </tr>
    </table>

    <table class="no-border">
        <tr>
            <td><strong>Name:</strong> {{ $TransactionDetails[0]->concessionaire_name }}</td>
            <td><strong>Contact:</strong> {{ $TransactionDetails[0]->concessionaire_contact }}</td>
        </tr>
    </table>

    <br>

    <table class="bordered">
        <thead>
            <tr>
                <th>Bill Type</th>
                <th>Bill Amount</th>
                <th>Amount Paid</th>
            </tr>
        </thead>
        <tbody>
            @foreach($TransactionDetails as $bill)
                <tr>
                    <td>{{ $bill->bill_type }}</td>
                    <td class="text-right">₱{{ number_format($bill->bill_amount, 2) }}</td>
                    <td class="text-right">₱{{ number_format($bill->amount_paid, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="no-border">
        <tr>
            <td class="text-right"><strong>Total Amount:</strong> ₱{{ number_format($TransactionDetails[0]->total_amount, 2) }}</td>
        </tr>
    </table>

    <br>
    <p><em>Thank you for your payment!</em></p>

</body>
</html>
