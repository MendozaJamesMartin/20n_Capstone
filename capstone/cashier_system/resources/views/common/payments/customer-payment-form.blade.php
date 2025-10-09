@extends('layout.main-master')

@section('content')

<main style="background-image:url('/bgpup3.jpg'); background-repeat:no-repeat; background-size:cover; min-height: 85vh; padding: 5%;">

    <div class="container" style="width:75%">
        <div class="bg-light p-5">
            <h1>Customer Payment Form</h1>

            @if(session('success'))
            <p class="text-success">{{ session('success') }}</p>
            @elseif(session('error'))
            <p class="text-danger">{{ session('error') }}</p>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (!$hasActiveBatch)
            <div class="alert alert-danger">
                🚫 Cannot finalize transaction. No receipt numbers available. Please load a new batch first.
            </div>
            @endif

            <form method="POST" action="{{ route('payments.customer.new') }}" id="paymentForm">
                @csrf

                <!-- Outsider Info -->
                <div class="mb-3">
                    <label for="customer_name" class="form-label">Customer Name</label>
                    <input type="text" class="form-control" id="customer_name" name="customer_name" placeholder="LAST NAME, FIRST NAME M.I." required>
                </div>

                <!-- Fee Selection -->
                <h3>Fees</h3>
                <table class="table table-bordered align-middle text-center" id="fees-table">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 35%">Fee Name</th>
                            <th style="width: 15%">Amount</th>
                            <th style="width: 15%">Quantity</th>
                            <th style="width: 25%">Fee Label</th>
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
                    Confirm Payment
                </button>
            </form>

        </div>
    </div>

</main>

<script>
    const feesData = @json($fees);
    let rowCount = 0;

    // Enhanced Fee Name Autocomplete (with instant suggestions)
    function attachFeeSearch(input, amountInput, feeIdInput) {
        const wrapper = document.createElement('div');
        wrapper.style.position = 'relative';
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);

        const suggestionBox = document.createElement('div');
        suggestionBox.className = 'suggestion-box bg-white border rounded shadow-sm position-absolute w-100 mt-1';
        suggestionBox.style.zIndex = '1000';
        suggestionBox.style.maxHeight = '180px';
        suggestionBox.style.overflowY = 'auto';
        suggestionBox.style.display = 'none';
        wrapper.appendChild(suggestionBox);

        function renderSuggestions(matches) {
            suggestionBox.innerHTML = '';
            matches.forEach(fee => {
                const div = document.createElement('div');
                div.textContent = `${fee.fee_name} — ₱${parseFloat(fee.amount).toFixed(2)}`;
                div.className = 'p-2 suggestion-item';
                div.style.cursor = 'pointer';
                div.addEventListener('mousedown', () => {
                    input.value = fee.fee_name;
                    amountInput.value = parseFloat(fee.amount).toFixed(2);
                    amountInput.readOnly = !fee.is_variable;
                    feeIdInput.value = fee.id;
                    input.classList.remove('is-invalid');
                    suggestionBox.style.display = 'none';
                    updateTotal();
                });
                suggestionBox.appendChild(div);
            });
            suggestionBox.style.display = matches.length ? 'block' : 'none';
        }

        function handleSearch() {
            const query = input.value.toLowerCase().trim();
            const matches = query.length
                ? feesData.filter(f => f.fee_name.toLowerCase().includes(query)).slice(0, 10)
                : feesData.slice(0, 10);
            renderSuggestions(matches);
        }

        input.addEventListener('input', handleSearch);
        input.addEventListener('focus', handleSearch);
        input.addEventListener('blur', () => setTimeout(() => (suggestionBox.style.display = 'none'), 150));
    }

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
                <input type="hidden" class="fee-id" name="fee_ids[]" required>
            </td>
            <td>
                <input type="number" step="0.01" class="form-control fee-amount" readonly>
            </td>
            <td>
                <input type="number" class="form-control fee-quantity" name="quantities_temp[]" value="1" min="1">
            </td>
            <td>
                <select class="form-select fee-label" name="fee_labels_temp[]" required>
                    <option value="">-- Select Label --</option>
                    <option value="None">None</option>
                    <option value="Certification Fee">Certification Fee</option>
                    <option value="Certified True Copy">Certified True Copy</option>
                    <option value="Others">Others (specify)</option>
                </select>
                <input type="text" class="form-control mt-2 fee-label-other d-none" placeholder="Enter label">
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
        attachFeeSearch(nameInput, amountInput, feeIdInput);

        nameInput.addEventListener('change', () => {
            const fee = feesData.find(f => f.fee_name.toLowerCase() === nameInput.value.toLowerCase());
            if (fee) {
                amountInput.value = parseFloat(fee.amount).toFixed(2);
                amountInput.readOnly = !fee.is_variable; // ✅ editable only if variable
                feeIdInput.value = fee.id;
                nameInput.classList.remove('is-invalid');
            } else {
                amountInput.value = '';
                feeIdInput.value = '';
                nameInput.classList.add('is-invalid');
            }
            updateTotal();
        });

        const labelSelect = tr.querySelector('.fee-label');
        const labelOther = tr.querySelector('.fee-label-other');

        labelSelect.addEventListener('change', () => {
            if (labelSelect.value === 'Others') {
                labelOther.classList.remove('d-none');
            } else {
                labelOther.classList.add('d-none');
                labelOther.value = '';
            }
        });

        amountInput.addEventListener('input', updateTotal); // listen for manual typing
        quantityInput.addEventListener('input', updateTotal);

        quantityInput.addEventListener('input', updateTotal);

        tr.querySelector('.remove-row').addEventListener('click', () => {
            tr.remove();
            updateTotal();
        });
    }

    document.getElementById('addFeeRow').addEventListener('click', createRow);

    document.getElementById('confirmPaymentButton').addEventListener('click', function() {

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

        // Before submission, create hidden inputs as sequential arrays
        const form = document.getElementById('paymentForm');
        document.querySelectorAll('.dynamic-quantity').forEach(e => e.remove());

        const feeIds = form.querySelectorAll('.fee-id');
        const quantities = form.querySelectorAll('.fee-quantity');
        const amounts = form.querySelectorAll('.fee-amount');
        const labelsSelect = form.querySelectorAll('.fee-label');
        const labelsOther = form.querySelectorAll('.fee-label-other');

        feeIds.forEach((idInput, i) => {
            const feeId = idInput.value;
            const quantity = quantities[i].value;
            const amount = amounts[i].value;

            let finalLabel = labelsSelect[i].value;
            if (finalLabel === 'Others') {
                finalLabel = labelsOther[i].value.trim();
            } else if (finalLabel === 'None') {
                finalLabel = ''; // treat as no label
            }

            if (feeId && quantity > 0 && amount) {
                const idHidden = document.createElement('input');
                idHidden.type = 'hidden';
                idHidden.name = 'fee_ids[]';
                idHidden.value = feeId;
                idHidden.classList.add('dynamic-quantity');
                form.appendChild(idHidden);

                const qtyHidden = document.createElement('input');
                qtyHidden.type = 'hidden';
                qtyHidden.name = 'quantities[]';
                qtyHidden.value = quantity;
                qtyHidden.classList.add('dynamic-quantity');
                form.appendChild(qtyHidden);

                const amtHidden = document.createElement('input');
                amtHidden.type = 'hidden';
                amtHidden.name = 'amounts[]';
                amtHidden.value = amount;
                amtHidden.classList.add('dynamic-quantity');
                form.appendChild(amtHidden);

                const lblHidden = document.createElement('input');
                lblHidden.type = 'hidden';
                lblHidden.name = 'labels[]';
                lblHidden.value = finalLabel || '';
                lblHidden.classList.add('dynamic-quantity');
                form.appendChild(lblHidden);
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
            notice.textContent = '🛑 Payment form is disabled due to no available receipt numbers.';
            form.appendChild(notice);
        }
    });
</script>

@endsection