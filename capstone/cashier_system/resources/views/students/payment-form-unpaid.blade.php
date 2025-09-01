@extends('layout.main-user')

@section('content')

<main style="background-image:url('/bgpup3.jpg'); background-repeat:no-repeat; background-size:cover; min-height: 85vh; padding: 5%;">
    <div class="container-fluid">
        <div class="bg-light p-4 p-md-5 rounded mx-auto" style="max-width: 900px;">
            <h1 class="mb-4">Payment Form</h1>

            @if(session('success'))
                <p class="text-success">{{ session('success') }}</p>
            @elseif(session('error'))
                <p class="text-danger">{{ session('error') }}</p>
            @endif

            @if (!$hasActiveBatch)
                <div class="alert alert-danger">
                    🚫 Cannot submit payment. Please wait for further announcement for Cashier availability.
                </div>
            @endif

            <form method="POST" action="{{ route('student.payment.form') }}" id="paymentForm">
                @csrf

                <!-- Outsider Info -->
                <div class="mb-3">
                    <label for="customer_name" class="form-label">Customer Name</label>
                    <input type="text" class="form-control" id="customer_name" name="customer_name" placeholder="First Name M.I. Last Name" required>
                </div>

                <div class="mb-4">
                    <label for="contact" class="form-label">Contact</label>
                    <input type="text" class="form-control" id="contact" name="contact" placeholder="example@email.com">
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

                <button type="submit" class="btn btn-primary mt-3" id="confirmPaymentButton">
                    Submit Payment
                </button>
            </form>

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
        const amounts = form.querySelectorAll('.fee-amount');

        feeIds.forEach((idInput, i) => {
            const feeId = idInput.value;
            const quantity = quantities[i].value;
            const amount = amounts[i].value;
            if (feeId && quantity > 0 && amount) {
                const qtyInput = document.createElement('input');
                qtyInput.type = 'hidden';
                qtyInput.name = `quantities[${feeId}]`;
                qtyInput.value = quantity;
                qtyInput.classList.add('dynamic-quantity');
                form.appendChild(qtyInput);

                const amtInput = document.createElement('input');
                amtInput.type = 'hidden';
                amtInput.name = `amounts[${feeId}]`;
                amtInput.value = amount;
                amtInput.classList.add('dynamic-quantity');
                form.appendChild(amtInput);
            }
        });

        form.submit();
    });

    window.addEventListener('DOMContentLoaded', () => {
        createRow();

        const hasActiveBatch = @json($hasActiveBatch);
        if (!hasActiveBatch) {
            document.querySelectorAll('#paymentForm input, #paymentForm button, #paymentForm select').forEach(el => {
                el.disabled = true;
            });

            const form = document.getElementById('paymentForm');
            const notice = document.createElement('p');
            notice.className = 'text-danger mt-2';
            notice.textContent = '🛑 Payment form is temporarily disabled.';
            form.appendChild(notice);
        }
    });
</script>

@endsection
