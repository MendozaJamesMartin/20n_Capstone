@extends('layout.main-master')

@section('content')

<main style="background-image:url('/bgpup3.jpg'); background-repeat:no-repeat; background-size:cover; min-height: 85vh; padding: 5%;">

    <div class="container" style="width:75%">
        <div class="bg-light" style="padding:5%">
            <h1>Student Payment Form</h1>

            @if(session('success'))
            <p style="color: green;">{{ session('success') }}</p>
            @elseif(session('error'))
            <p style="color: red;">{{ session('error') }}</p>
            @endif

            <form method="POST" action="{{ route('payments.outsider.new') }}" id="paymentForm">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="name" class="form-control" id="name" name="name" placeholder="First Name M.I. Last Name">
                        </div>
                        <div class="mb-3">
                            <label for="contact" class="form-label">Contact</label>
                            <input type="contact" class="form-control" id="contact" name="contact" placeholder="example@email.com">
                        </div>
                    </div>
                </div>

                <!-- Fees Table -->
                <table class="table">
                    <tr>
                        <th colspan="3">
                            <h3>Select Fees</h3>
                        </th>
                    </tr>
                </table>

                <div style="max-height: 220px; overflow-y: scroll; border: 1px solid #ccc;">
                    <table class="table table-secondary table-striped">
                        <thead style="position: sticky; top: 0; background-color: #f8f9fa; z-index: 1;">
                            <tr>
                                <th>Fee Name</th>
                                <th>Amount</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($fees as $fee)
                            <tr>
                                <td>{{ $fee->fee_name }}</td>
                                <td>{{ number_format($fee->amount, 2) }}</td>
                                <td>
                                    <input type="number" class="form-control quantity-input"
                                        name="quantities[{{ $fee->id }}]"
                                        data-price="{{ $fee->amount }}" min="0" value="0">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <h3>Total Amount: <span id="total-amount">0.00</span></h3>
                </div>

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

        </div>
    </div>

</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const quantityInputs = document.querySelectorAll('.quantity-input');
        const totalAmountElement = document.getElementById('total-amount');

        quantityInputs.forEach(input => {
            input.addEventListener('input', calculateTotal);
        });

        function calculateTotal() {
            let total = 0;

            quantityInputs.forEach(input => {
                const price = parseFloat(input.dataset.price);
                const quantity = parseFloat(input.value) || 0;

                total += price * quantity;
            });

            totalAmountElement.textContent = total.toFixed(2);
        }
    });

    document.getElementById('confirmPaymentButton').addEventListener('click', function() {
        // Copy receipt number from modal input to hidden input
        const receiptNumber = document.getElementById('modal_receipt_number').value;
        document.getElementById('receipt_number').value = receiptNumber;

        // Submit the form
        document.getElementById('paymentForm').submit();
    });
</script>

@endsection