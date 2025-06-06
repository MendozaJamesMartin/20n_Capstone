<!DOCTYPE html>
<html>
<head>
    <title>Concessionaire Billing Statement</title>
</head>
<body>
    <h2>Concessionaire Billing Statement</h2>
    <p>Your Billing Statement for this month's {{$utility_type}} bill is now available to be viewed.</p>
    <p><strong>Amount:</strong> ₱{{ $bill_amount }}</p>
    <p><strong>Due Date:</strong> {{ $due_date }}</p>
    <p>If you have any questions, please contact support.</p>
</body>
</html>