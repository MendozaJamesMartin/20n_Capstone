@extends('layout.main-master')
@section('content')

    <div style="background-image: url('/bgpup4.jpg'); background-repeat: no-repeat; background-size: cover; min-height: 85vh; padding: 5%;">
        <main class="container" style="width:50%;"> 

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="bg-light border" style="padding:2%">
                <h3><strong>Concessionaire Transaction Receipt</strong></h3>
                @if ($ReceiptDetails->isNotEmpty())
                    <div>
                        <table class="table">
                            <tr>
                                <td><p><strong>Receipt ID:</strong></p></td>
                                <td><p>{{ $ReceiptDetails[0]->id }}</p></td>
                                <td><p><strong>Receipt Number:</strong></p></td>
                                <td><p>{{ $ReceiptDetails[0]->receipt_number }}</p></td>
                            </tr>
                            <tr>
                                <td><p><strong>Transaction ID:</strong></p></td>
                                <td><p>{{ $ReceiptDetails[0]->transaction_id }}</p></td>
                                <td><p><strong>Date of Print:</strong></p></td>
                                <td><p>{{ $ReceiptDetails[0]->printed_at }}</p></td>
                            </tr>
                        </table>
                    </div>
                    <div>
                        <table class="table">
                        <tr>
                                <td><p><strong> Concessionaire Name: </strong></p></td>
                                <td><p> {{ $ReceiptDetails[0]->name }} </p></td>
                            </tr>
                        </table>
                    </div>
                    <br>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Utility Type</th>
                                <th>Amount Paid</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ReceiptDetails as $bills)
                                <tr>
                                    <td>{{ $bills->bill_id }}</td>
                                    <td>{{ $bills->utility_type }}</td>
                                    <td>{{ $bills->amount_paid }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <table class="table">
                        <p><strong>Total Amount:</strong> {{ $ReceiptDetails[0]->total_amount }}</p>
                    </table>

                @else
                    <p>No details found for this transaction.</p>
                @endif
            </div>
        </main>
    </div>

@endsection
