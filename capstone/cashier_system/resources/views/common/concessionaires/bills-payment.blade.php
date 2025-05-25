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

            <form method="GET" action="{{ route('concessionaires.billing.payment') }}">
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
            <form method="POST" action="{{ route('concessionaires.billing.payment') }}" id="paymentForm">
                @csrf
                <!-- Unpaid Bills Table (Initially Hidden) -->
                <h4 class="mt-3">Unpaid Bills</h4>
                <table class="table table-striped" id="bills-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Concessionaire</th>
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
                            <td>{{ optional($bill->concessionaire)->name ?? 'N/A' }}</td>
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

                <!-- Hidden input for receipt number -->
                <input type="hidden" name="receipt_number" id="receipt_number">

                <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#receiptModal">
                    Submit Payment
                </button>
            </form>

            <!-- Modal -->
            <div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="receiptModalLabel">Enter Receipt Number</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="modal_receipt_number" class="form-label">Receipt Number</label>
                                <input type="text" class="form-control" id="modal_receipt_number" placeholder="Enter Receipt Number">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="confirmPaymentButton">Submit Payment</button>
                        </div>
                    </div>
                </div>
            </div>

            @else
            <p>No unpaid bills found.</p>
            @endif
        </div>
    </div>
</main>

<script>
document.getElementById('confirmPaymentButton').addEventListener('click', function () {
    // Copy receipt number from modal input to hidden input
    const receiptNumber = document.getElementById('modal_receipt_number').value;
    document.getElementById('receipt_number').value = receiptNumber;

    // Submit the form
    document.getElementById('paymentForm').submit();
});
</script>

@endsection