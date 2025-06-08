<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 14px; padding: 20px; }
        h3 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        .summary td { border: none; }
    </style>
</head>
<body>

    <h3><strong>Student Transaction Receipt</strong></h3>

        <table>
            <tr>
                <td><strong>Transaction ID:</strong> {{ $TransactionDetails[0]->transaction_id }}</td>
                <td><strong>Transaction Date:</strong> {{ $TransactionDetails[0]->transaction_date }}</td>
                <td><strong>Receipt #:</strong> {{ $TransactionDetails[0]->receipt_number }}</td>
            </tr>
        </table>

        <table>
            <tr>
                <td><strong>Customer Name:</strong> {{ $TransactionDetails[0]->customer_name }}</td>
            </tr>
        </table>

        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Amount</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($TransactionDetails as $fees)
                <tr>
                    <td>{{ $fees->fee_name }}</td>
                    <td>₱{{ number_format($fees->fee_amount, 2) }}</td>
                    <td>{{ $fees->quantity }}</td>
                    <td>₱{{ number_format($fees->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table class="summary">
            <tr>
                <td><strong>Amount Paid:</strong> ₱{{ number_format($TransactionDetails[0]->amount_paid, 2) }}</td>
                <td><strong>Balance Due:</strong> ₱{{ number_format($TransactionDetails[0]->balance_due, 2) }}</td>
                <td><strong>Total Amount:</strong> ₱{{ number_format($TransactionDetails[0]->total_amount, 2) }}</td>
            </tr>
        </table>

</body>
</html>
