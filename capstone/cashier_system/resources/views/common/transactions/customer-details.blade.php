@extends('layout.main-master')
@section('content')

<div style="background-image: url('/bgpup4.jpg'); background-repeat: no-repeat; background-size: cover; min-height: 85vh; padding: 5%;">
    <main class="container" style="width:50%;">

        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="bg-light border" style="padding:2%">
            <h3><strong>Student Transaction Details</strong></h3>
            @if ($TransactionDetails->isNotEmpty())
            <div>
                <table class="table">
                    <tr>
                        <td>
                            <p><strong>Transaction ID:</strong></p>
                        </td>
                        <td>
                            <p>{{ $TransactionDetails[0]->transaction_id }}</p>
                        </td>
                        <td>
                            <p><strong>Transaction Date:</strong></p>
                        </td>
                        <td>
                            <p>{{ $TransactionDetails[0]->transaction_date }}</p>
                        </td>
                        <td>
                            <p>{{ $TransactionDetails[0]->receipt_number }}</p>
                        </td>
                    </tr>
                </table>
            </div>
            <div>
                <table class="table">
                    <tr>
                        <td>
                            <p> {{ $TransactionDetails[0]->customer_name }}</p>
                        </td>
                    </tr>
                </table>
            </div>
            <br>

            <table class="table table-bordered">
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
                        <td>{{ $fees->fee_amount }}</td>
                        <td>{{ $fees->quantity }}</td>
                        <td>{{ $fees->subtotal }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <table class="table">
                <td><p><strong>Amount Paid:</strong> {{ $TransactionDetails[0]->amount_paid }}</p></td>
                <td><p><strong>Balance Due:</strong> {{ $TransactionDetails[0]->balance_due }}</p></td>
                <td><p><strong>Total Amount:</strong> {{ $TransactionDetails[0]->total_amount }}</p></td>
            </table>

            @else
            <p>No details found for this transaction.</p>
            @endif
        </div>
    </main>
</div>

@if(session('auto_print'))
<script>
    window.addEventListener('load', function () {
        window.print();
    });
</script>
@endif

<style>
@media print {
    .no-print {
        display: none !important;
    }
}
</style>

@endsection