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

            <form method="POST" action="{{ route('payments.update', ['transactionId' => $transactionId]) }}" id="paymentForm">
                @csrf
                @method('PUT')

                <!-- Student Info -->
                <label class="form-label">Student Full Name</label>
                <div class="mb-3 d-flex gap-2">
                    <input type="text" class="form-control" name="customer_name" value="{{ old('customer_name', $transactionDetails[0]->customer_name ?? '') }}">
                </div>

                <div class="mb-4">
                    <label for="email" class="form-label">Email</label>
                    <input type="text" class="form-control" name="email" value="{{ old('email', $transactionDetails[0]->email ?? '') }}">
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
                        @if(!empty($selectedFees) && $selectedFeeDetails->isNotEmpty())
                            @foreach($selectedFees as $feeId => $quantity)
                                @php
                                    $fee = $selectedFeeDetails->firstWhere('id', $feeId);
                                @endphp
                                @if($fee)
                                    <tr class="fee-row">
                                        <td>
                                            <input list="feeSuggestions" class="form-control fee-name" value="{{ $fee->fee_name }}">
                                            <input type="hidden" class="fee-id" name="fee_ids[]" value="{{ $fee->id }}">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" class="form-control fee-amount" value="{{ number_format($fee->amount, 2, '.', '') }}">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control fee-quantity" name="quantities_temp[]" value="{{ $quantity }}" min="1">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm remove-row">X</button>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        @endif
                    </tbody>
                </table>

                <button type="button" class="btn btn-success btn-sm mb-3" id="addFeeRow">+ Add Fee</button>

                <div class="mt-3">
                    <h4>Total Amount: ₱<span id="total-amount">0.00</span></h4>
                </div>

                <button type="submit" class="btn btn-primary mt-3" id="confirmPaymentButton">
                    Approve
                </button>
            </form>
            
                <form action="{{ route('payments.disapprove', ['id' => $transactionDetails[0]->transaction_id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to disapprove and delete this transaction?');" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger mt-3" title="Disapprove Payment">
                        Disapprove
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

    function updateTotal() {
        let total = 0;
        document.querySelectorAll('.fee-row').forEach(row => {
            const amount = parseFloat(row.querySelector('.fee-amount').value) || 0;
            const quantity = parseInt(row.querySelector('.fee-quantity').value) || 0;
            total += amount * quantity;
        });
        document.getElementById('total-amount').textContent = total.toFixed(2);
    }

    function bindEventsToRow(row) {
        const nameInput = row.querySelector('.fee-name');
        const amountInput = row.querySelector('.fee-amount');
        const quantityInput = row.querySelector('.fee-quantity');
        const feeIdInput = row.querySelector('.fee-id');

        if (nameInput) {
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
        }

        if (quantityInput) {
            quantityInput.addEventListener('input', updateTotal);
        }

        const removeBtn = row.querySelector('.remove-row');
        if (removeBtn) {
            removeBtn.addEventListener('click', () => {
                row.remove();
                updateTotal();
            });
        }
    }

    function createRow() {
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
        bindEventsToRow(tr);
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

    // On page load: bind events to existing rows and update total
    window.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.fee-row').forEach(bindEventsToRow);
        updateTotal();
    });
</script>

@endsection
