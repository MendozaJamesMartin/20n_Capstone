@extends('layout.main-master')

@section('content')

<main style="background-image:url('/bgpup3.jpg'); background-repeat:no-repeat; background-size:cover; min-height: 85vh; padding: 5%;">
    <div class="container" style="width:75%">
        <div class="bg-light" style="padding:5%">
            <h1>Concessionaire Payment Form</h1>

            @if(session('success'))
            <p style="color: green;">{{ session('success') }}</p>
            @elseif(session('error'))
            <p style="color: red;">{{ session('error') }}</p>
            @endif

            <form method="POST" action="{{ route('InsertNewConcessionaireTransaction') }}">
                @csrf

                <!-- Concessionaire Selection -->
                <label for="concessionaire">Select Concessionaire:</label>
                <select id="concessionaire" name="concessionaire_id" class="form-control">
                    <option value="">-- Select Concessionaire --</option>
                    @foreach($concessionaires as $concessionaire)
                    <option value="{{ $concessionaire->id }}">{{ $concessionaire->name }}</option>
                    @endforeach
                </select>

                <!-- Unpaid Bills Table (Initially Hidden) -->
                <h4 class="mt-3">Unpaid Bills</h4>
                <table class="table table-striped" id="bills-table">
                    <thead>
                        <tr>
                            <th>Utility Type</th>
                            <th>Bill Amount (₱)</th>
                            <th>Balance Due (₱)</th>
                            <th>Due Date</th>
                            <th>Payment Amount (₱)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bills as $bill)
                        <tr>
                            <td>{{ $bill->utility_type }}</td>
                            <td>{{ $bill->bill_amount }}</td>
                            <td>{{ $bill->balance_due }}</td>
                            <td>{{ $bill->due_date }}</td>
                            <td>
                                <input type="number" class="form-control quantity-input" name="amount" min="0" value="0">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <button type="submit" class="btn btn-primary mt-3">Submit Payment</button>
            </form>
        </div>
    </div>
</main>

@endsection