@extends('layout.main-user')

@section('content')

<main style="background-image:url('/bgpup3.jpg'); background-repeat:no-repeat; background-size:cover; min-height:85vh; padding:5%;">
    <div class="container-fluid">
        <div class="bg-light p-4 p-md-5 rounded mx-auto" style="max-width:900px;">
            <h1 class="mb-4 text-center">Payment Form</h1>

            @if(session('success'))
            <p class="text-success text-center">{{ session('success') }}</p>
            @elseif(session('error'))
            <p class="text-danger text-center">{{ session('error') }}</p>
            @endif

            @if (!$hasActiveBatch)
            <div class="alert alert-danger text-center">
                🚫 Cannot submit payment. Please wait for further announcement for Cashier availability.
            </div>
            @endif

            <form method="POST" action="{{ route('student.payment.form') }}" id="paymentForm">
                @csrf

                <!-- Customer Info -->
                <div class="mb-3">
                    <label for="customer_name" class="form-label fw-semibold">Customer Name</label>
                    <input type="text" class="form-control" id="customer_name" name="customer_name"
                        placeholder="LAST NAME, FIRST NAME M.I." required>
                </div>

                <!-- Fee Selection -->
                <h3 class="mt-4 mb-3 text-center text-md-start">Fees</h3>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center" id="fees-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width:60%">Fee Name</th>
                                <th style="width:15%">Amount</th>
                                <th style="width:15%">Quantity</th>
                                <th style="width:10%">Remove</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Rows added dynamically -->
                        </tbody>
                    </table>
                </div>

                <div class="d-grid d-md-inline mb-3">
                    <button type="button" class="btn btn-success btn-sm mb-3 w-100 w-md-auto" id="addFeeRow">
                        + Add Fee
                    </button>
                </div>

                <div class="mt-3 text-center">
                    <h4>Total Amount: ₱<span id="total-amount">0.00</span></h4>
                </div>

                <div class="d-grid d-md-inline">
                    <button type="submit" class="btn btn-primary mt-3 w-100 w-md-auto" id="confirmPaymentButton">
                        Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<style>
    @media (max-width: 576px) {
        main {
            padding: 3%;
        }

        .bg-light {
            padding: 1.5rem !important;
        }

        h1 {
            font-size: 1.6rem;
        }

        table {
            font-size: 0.85rem;
        }

        .btn {
            font-size: 0.9rem;
        }

        .form-label {
            font-size: 0.9rem;
        }
    }

    .suggestion-item:hover {
        background-color: #0d6efd;
        color: white;
    }

    .suggestions-list {
        max-height: 150px;
        overflow-y: auto;
        scrollbar-width: thin;
    }
</style>

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
        <td style="position: relative;">
            <input type="text" class="form-control fee-name" placeholder="Search fee..." autocomplete="off">
            <input type="hidden" class="fee-id" name="fee_ids[]">
        </td>
        <td><input type="number" step="0.01" class="form-control fee-amount" readonly></td>
        <td><input type="number" class="form-control fee-quantity" name="quantities_temp[]" value="1" min="1"></td>
        <td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>
    `;
        tbody.appendChild(tr);

        const feeNameInput = tr.querySelector('.fee-name');
        const amountInput = tr.querySelector('.fee-amount');
        const quantityInput = tr.querySelector('.fee-quantity');
        const feeIdInput = tr.querySelector('.fee-id');

        let suggestionsList = document.createElement('div');
        suggestionsList.classList.add('border', 'rounded', 'bg-white', 'position-absolute', 'shadow-sm');
        suggestionsList.style.maxHeight = '150px';
        suggestionsList.style.overflowY = 'auto';
        suggestionsList.style.display = 'none';
        suggestionsList.style.zIndex = '2000';
        document.body.appendChild(suggestionsList);

        let currentIndex = -1;

        function positionSuggestions() {
            const rect = feeNameInput.getBoundingClientRect();
            suggestionsList.style.width = rect.width + 'px';
            suggestionsList.style.left = rect.left + window.scrollX + 'px';
            suggestionsList.style.top = rect.bottom + window.scrollY + 'px';
        }

        function renderSuggestions(filteredFees) {
            suggestionsList.innerHTML = '';
            filteredFees.forEach((fee, index) => {
                const div = document.createElement('div');
                div.classList.add('suggestion-item', 'p-2');
                div.textContent = fee.fee_name;
                div.style.cursor = 'pointer';
                div.dataset.index = index;
                div.addEventListener('click', () => selectFee(fee));
                suggestionsList.appendChild(div);
            });
            if (filteredFees.length) {
                positionSuggestions();
                suggestionsList.style.display = 'block';
            } else {
                suggestionsList.style.display = 'none';
            }
        }

        function selectFee(fee) {
            feeNameInput.value = fee.fee_name;
            amountInput.value = parseFloat(fee.amount).toFixed(2);
            feeIdInput.value = fee.id;
            suggestionsList.style.display = 'none';
            updateTotal();
        }

        feeNameInput.addEventListener('input', () => {
            const query = feeNameInput.value.toLowerCase();
            currentIndex = -1;
            if (query.length < 1) return (suggestionsList.style.display = 'none');
            const matches = feesData
                .filter(fee => fee.fee_name.toLowerCase().includes(query))
                .slice(0, 10);
            renderSuggestions(matches);
        });

        feeNameInput.addEventListener('focus', () => {
            let matches;
            const query = feeNameInput.value.trim().toLowerCase();

            if (query.length > 0) {
                matches = feesData.filter(fee => fee.fee_name.toLowerCase().includes(query)).slice(0, 10);
            } else {
                // show all (or first 10) fees by default when empty
                matches = feesData.slice(0, 10);
            }

            renderSuggestions(matches);
        });

        feeNameInput.addEventListener('keydown', (e) => {
            const items = suggestionsList.querySelectorAll('.suggestion-item');
            if (!items.length) return;
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                currentIndex = (currentIndex + 1) % items.length;
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                currentIndex = (currentIndex - 1 + items.length) % items.length;
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (currentIndex >= 0 && items[currentIndex]) {
                    const selectedFee = feesData.find(f => f.fee_name === items[currentIndex].textContent);
                    if (selectedFee) selectFee(selectedFee);
                }
            } else return;
            items.forEach(item => item.classList.remove('bg-primary', 'text-white'));
            if (items[currentIndex]) {
                items[currentIndex].classList.add('bg-primary', 'text-white');
                items[currentIndex].scrollIntoView({
                    block: 'nearest'
                });
            }
        });

        document.addEventListener('click', (e) => {
            if (!suggestionsList.contains(e.target) && e.target !== feeNameInput) {
                suggestionsList.style.display = 'none';
            }
        });

        quantityInput.addEventListener('input', updateTotal);
        tr.querySelector('.remove-row').addEventListener('click', () => {
            tr.remove();
            updateTotal();
        });
    }

    document.getElementById('addFeeRow').addEventListener('click', createRow);

    document.getElementById('confirmPaymentButton').addEventListener('click', function() {
        const inputs = document.querySelectorAll('.fee-name');
        let hasInvalid = false;

        inputs.forEach(input => {
            const feeIdInput = input.closest('tr').querySelector('.fee-id');
            if (!feeIdInput.value) {
                input.classList.add('is-invalid');
                hasInvalid = true;
            } else {
                input.classList.remove('is-invalid');
            }
        });

        if (hasInvalid) {
            alert("Please select valid fees before submitting.");
            return;
        }

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
            notice.className = 'text-danger mt-2 text-center';
            notice.textContent = '🛑 Payment form is temporarily disabled.';
            form.appendChild(notice);
        }
    });
</script>

@endsection