@extends('layout.main-master')
@section('content')

<div style="background-image: url('/bgpup4.jpg'); background-repeat: no-repeat; background-size: cover; min-height: 85vh; padding: 5%;">
    <main class="container" style="width:50%;">

        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="bg-light border" style="padding:2%">
            <h3><strong>Concessionaire Transaction Details</strong></h3>
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
                            <p> {{ $TransactionDetails[0]->concessionaire_name }}</p>
                        </td>
                        <td>
                            <p> {{ $TransactionDetails[0]->concessionaire_contact }}</p>
                        </td>
                    </tr>
                </table>
            </div>
            <br>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Bill Type</th>
                        <th>Bill Amount</th>
                        <th>Amount Paid</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($TransactionDetails as $bills)
                    <tr>
                        <td>{{ $bills->bill_type }}</td>
                        <td>{{ $bills->bill_amount }}</td>
                        <td>{{ $bills->amount_paid }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <table class="table">
                <td>
                    <p><strong>Total Amount:</strong> {{ $TransactionDetails[0]->total_amount }}</p>
                </td>
            </table>

            @else
            <p>No details found for this transaction.</p>
            @endif
        </div>
    </main>
</div>

@endsection