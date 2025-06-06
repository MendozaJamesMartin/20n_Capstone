@extends('layout.main-master')

@section('content')

<main style="background-image:url('/bgpup3.jpg'); background-repeat:no-repeat; background-size:cover; min-height: 85vh; padding: 5%;">

    <div class="container" style="width:75%">
        <div class="bg-light p-5">
            <h1>Student Payment Form</h1>

            @if(session('success'))
                <p class="text-success">{{ session('success') }}</p>
            @elseif(session('error'))
                <p class="text-danger">{{ session('error') }}</p>
            @endif

            <form method="POST" action="{{ route('payments.student.new') }}" id="paymentForm">
                @csrf

                <!-- Student Info -->
                <div class="mb-3">
                    <label for="student_id" class="form-label">Student ID</label>
                    <input type="text" class="form-control" id="student_id" name="student_id" placeholder="XXXX-XXXXX-XX-X">
                </div>

                <label class="form-label">Student Full Name</label>
                <div class="mb-3 d-flex gap-2">
                    <input type="text" class="form-control" name="first_name" placeholder="First Name">
                    <input type="text" class="form-control" name="middle_name" placeholder="Middle Name">
                    <input type="text" class="form-control" name="last_name" placeholder="Last Name">
                    <input type="text" class="form-control" name="suffix" placeholder="Suffix">
                </div>

                <div class="mb-4">
                    <label for="email" class="form-label">Email</label>
                    <input type="text" class="form-control" name="email" placeholder="email@example.com">
                </div>

                <!-- Fee Selection -->
                <h3>Fees</h3>
                <table class="table table-bordered align-middle text-center" id="fees-table">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60%">Fee Name</th>
                            <th style="width: 15%">Amount</th>
                            <th style="width: 15%">Quantity</th>
                            <th style="width: 10%">Remove</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Rows added dynamically -->
                    </tbody>
                </table>

                <button type="button" class="btn btn-success btn-sm mb-3" id="addFeeRow">+ Add Fee</button>

                <div class="mt-3">
                    <h4>Total Amount: ₱<span id="total-amount">0.00</span></h4>
                </div>

                <input type="hidden" name="receipt_number" id="receipt_number">

                <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#receiptModal">
                    Submit Payment
                </button>
            </form>

            <!-- Modal for Receipt Number -->
            <div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Enter Receipt Number</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <label for="modal_receipt_number" class="form-label">Receipt Number</label>
                            <input type="text" class="form-control" id="modal_receipt_number" placeholder="Enter Receipt Number">
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary" id="confirmPaymentButton">Print Receipt</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Datalist for fee name autocomplete -->
            <datalist id="feeSuggestions">
                @foreach($fees as $fee)
                    <option value="{{ $fee->fee_name }}" data-id="{{ $fee->id }}" data-amount="{{ $fee->amount }}">
                @endforeach
            </datalist>
        </div>
    </div>

</main>

<script>
    const feesData = @json($fees);
    let rowCount = 0;

    function updateTotal() {
        let total = 0;
        document.querySelectorAll('.fee-row').forEach(row => {
            const amount = parseFloat(row.querySelector('.fee-amount').value) || 0;
            const quantity = parseInt(row.querySelector('.fee-quantity').value) || 0;
            total += amount * quantity;
        });
        document.getElementById('total-amount').textContent = total.toFixed(2);
    }

    function createRow() {
        rowCount++;
        const tbody = document.querySelector('#fees-table tbody');
        const tr = document.createElement('tr');
        tr.classList.add('fee-row');

        tr.innerHTML = `
            <td>
                <input list="feeSuggestions" class="form-control fee-name" placeholder="Type to search">
                <input type="hidden" class="fee-id" name="fee_ids[]">
            </td>
            <td>
                <input type="number" step="0.01" class="form-control fee-amount" readonly>
            </td>
            <td>
                <input type="number" class="form-control fee-quantity" name="quantities_temp[]" value="1" min="1">
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm remove-row">X</button>
            </td>
        `;

        tbody.appendChild(tr);

        const nameInput = tr.querySelector('.fee-name');
        const amountInput = tr.querySelector('.fee-amount');
        const quantityInput = tr.querySelector('.fee-quantity');
        const feeIdInput = tr.querySelector('.fee-id');

        nameInput.addEventListener('change', () => {
            const fee = feesData.find(f => f.fee_name.toLowerCase() === nameInput.value.toLowerCase());
            if (fee) {
                amountInput.value = parseFloat(fee.amount).toFixed(2);
                feeIdInput.value = fee.id;
                nameInput.classList.remove('is-invalid');
            } else {
                amountInput.value = '';
                feeIdInput.value = '';
                nameInput.classList.add('is-invalid');
            }
            updateTotal();
        });

        quantityInput.addEventListener('input', updateTotal);

        tr.querySelector('.remove-row').addEventListener('click', () => {
            tr.remove();
            updateTotal();
        });
    }

    document.getElementById('addFeeRow').addEventListener('click', createRow);

    document.getElementById('confirmPaymentButton').addEventListener('click', function () {
        const receiptNumber = document.getElementById('modal_receipt_number').value;
        document.getElementById('receipt_number').value = receiptNumber;

        // Validate all fee name inputs
        const nameInputs = document.querySelectorAll('.fee-name');
        let hasInvalid = false;

        nameInputs.forEach(nameInput => {
            const fee = feesData.find(f => f.fee_name.toLowerCase() === nameInput.value.toLowerCase());
            if (!fee) {
                nameInput.classList.add('is-invalid');
                hasInvalid = true;
            } else {
                nameInput.classList.remove('is-invalid');
            }
        });

        if (hasInvalid) {
            alert("Please correct invalid fee names before submitting.");
            return;
        }

        // Before submission, create hidden inputs for quantities[fee_id]
        const form = document.getElementById('paymentForm');
        document.querySelectorAll('.dynamic-quantity').forEach(e => e.remove());

        const feeIds = form.querySelectorAll('.fee-id');
        const quantities = form.querySelectorAll('.fee-quantity');

        feeIds.forEach((idInput, i) => {
            const feeId = idInput.value;
            const quantity = quantities[i].value;
            if (feeId && quantity > 0) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `quantities[${feeId}]`;
                input.value = quantity;
                input.classList.add('dynamic-quantity');
                form.appendChild(input);
            }
        });

        form.submit();
    });

    // Optional: create one row by default
    window.addEventListener('DOMContentLoaded', createRow);
</script>

@endsection
