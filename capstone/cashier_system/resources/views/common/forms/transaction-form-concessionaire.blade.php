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

            <form method="GET" action="{{ route('concessionaire.transaction.new') }}">
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="concessionaire">Select Concessionaire:</label>
                        <select class="form-control" name="concessionaire_id" id="concessionaire" onchange="this.form.submit()">
                            <option value="">-- All Concessionaires --</option>
                            @foreach($concessionaires as $concessionaire)
                            <option value="{{ $concessionaire->id }}" {{ request('concessionaire_id') == $concessionaire->id ? 'selected' : '' }}>
                                {{ $concessionaire->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>

            @if(isset($bills) && count($bills) > 0)
            <form method="POST" action="{{ route('concessionaire.transaction.new') }}">
                @csrf
                <!-- Unpaid Bills Table (Initially Hidden) -->
                <h4 class="mt-3">Unpaid Bills</h4>
                <table class="table table-striped" id="bills-table">
                    <thead>
                        <tr>
                            <th></th>
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
                            <td>
                                <input type="checkbox" name="bill_id[]" value="{{ $bill->id }}">
                            </td>
                            <td>{{ $bill->utility_type }}</td>
                            <td>{{ $bill->bill_amount }}</td>
                            <td>{{ $bill->balance_due }}</td>
                            <td>{{ $bill->due_date }}</td>
                            <td>
                                <input type="number" name="amount[{{ $bill->id }}]" step="0.01" min="0" max="{{ $bill->balance_due }}">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <button type="submit" class="btn btn-primary mt-3">Submit Payment</button>
            </form>
            @else
            <p>No unpaid bills found.</p>
            @endif
        </div>
    </div>
</main>

@endsection