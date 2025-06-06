@extends('layout.main-master')

@section('content')

<main class="py-4 py-md-5" style="background-image:url('/bgpup3.jpg'); background-repeat:no-repeat; background-size:cover; min-height: 85vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <div class="bg-light p-4 p-md-5 rounded shadow">
                    <h1 class="h3 h2-md">Student Payment Form</h1>

                    @if(session('success'))
                        <p class="text-success">{{ session('success') }}</p>
                    @elseif(session('error'))
                        <p class="text-danger">{{ session('error') }}</p>
                    @endif

                    <form method="POST" action="{{ route('student.payment.form') }}" id="paymentForm">
                        @csrf

                        <div class="mb-3">
                            <label for="student_id" class="form-label">Student ID</label>
                            <input type="text" class="form-control" id="student_id" name="student_id" placeholder="XXXX-XXXXX-XX-X">
                        </div>

                        <label class="form-label">Student Full Name</label>
                        <div class="row g-2 mb-3">
                            <div class="col-md">
                                <input type="text" class="form-control" name="first_name" placeholder="First Name">
                            </div>
                            <div class="col-md">
                                <input type="text" class="form-control" name="middle_name" placeholder="Middle Name">
                            </div>
                            <div class="col-md">
                                <input type="text" class="form-control" name="last_name" placeholder="Last Name">
                            </div>
                            <div class="col-md">
                                <input type="text" class="form-control" name="suffix" placeholder="Suffix">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="email" class="form-label">Email</label>
                            <input type="text" class="form-control" name="email" placeholder="email@example.com">
                        </div>

                        <h3>Fees</h3>
                        <div class="table-responsive mb-3" style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-bordered align-middle text-center">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Fee Name</th>
                                        <th>Amount</th>
                                        <th>Quantity</th>
                                        <th>Remove</th>
                                    </tr>
                                </thead>
                                <tbody id="fees-table-body">
                                </tbody>
                            </table>
                        </div>

                        <button type="button" class="btn btn-success btn-sm mb-3 w-100" id="addFeeRow">+ Add Fee</button>

                        <div class="mt-3">
                            <h4>Total Amount: ₱<span id="total-amount">0.00</span></h4>
                        </div>

                        <button class="btn btn-danger btn-lg w-100 w-md-auto mt-3" type="submit">Submit</button>
                    </form>

                    <datalist id="feeSuggestions">
                        @foreach($fees as $fee)
                            <option value="{{ $fee->fee_name }}" data-id="{{ $fee->id }}" data-amount="{{ $fee->amount }}">
                        @endforeach
                    </datalist>
                </div>
            </div>
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
        const tbody = document.getElementById('fees-table-body');
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

    window.addEventListener('DOMContentLoaded', createRow);
</script>

@endsection
