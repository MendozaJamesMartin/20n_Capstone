@extends('layout.main-master')

@section('content')

<main style="min-height:85vh; padding:5%;">
    <div class="container" style="width:90%">
        <div class="bg-light p-5 rounded shadow">

            <h1 class="mb-4">Finalize Payment</h1>

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

            <div class="row">
                <!-- Left Column: Student Submission -->
                <div class="col-md-5">
                    <h3>Student Submission</h3>
                    <div class="card p-3 mb-4">
                        <p><strong>Name:</strong> {{ $transactionDetails[0]->customer_name ?? 'N/A' }}</p>
                        <p><strong>Email:</strong> {{ $transactionDetails[0]->contact ?? 'N/A' }}</p>

                        <h5 class="mt-3">Submitted Fees</h5>
                        <table class="table table-sm table-bordered text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>Fee Name</th>
                                    <th>Amount</th>
                                    <th>Qty</th>
                                    <th>Label</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactionDetails as $detail)
                                <tr>
                                    <td>{{ $detail->fee_name }}</td>
                                    <td>₱{{ number_format($detail->amount, 2) }}</td>
                                    <td>{{ $detail->quantity }}</td>
                                    <td>{{ $detail->fee_label ?? '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Right Column: Cashier Finalization -->
                <div class="col-md-7">
                    <h3>Cashier Finalization</h3>

                    <form method="POST" action="{{ route('payments.update', ['transactionId' => $transactionId]) }}" id="paymentForm">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Customer Full Name</label>
                            <input type="text" class="form-control" name="customer_name"
                                value="{{ old('customer_name', $transactionDetails[0]->customer_name ?? '') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email Address (optional)</label>
                            <input type="text" class="form-control" name="contact"
                                value="{{ old('contact', $transactionDetails[0]->contact ?? '') }}">
                        </div>

                        <h5 class="mt-4 mb-3">Fees</h5>
                        <div id="feesContainer">
                            @foreach($transactionDetails as $detail)
                                <div class="fee-row card p-3 mb-3 shadow-sm">
                                    <div class="row g-3 align-items-end">
                                        <div class="col-lg-5 col-md-12">
                                            <label class="form-label fw-semibold">Fee Name</label>
                                            <input type="text" class="form-control fee-name" value="{{ $detail->fee_name }}" autocomplete="off">
                                            <input type="hidden" class="fee-id" name="fee_ids[]" value="{{ $detail->fee_id }}">
                                        </div>
                                        <div class="col-lg-2 col-md-6">
                                            <label class="form-label fw-semibold">Amount</label>
                                            <input type="number" step="0.01" class="form-control fee-amount" 
                                                name="amounts[]"
                                                value="{{ number_format($detail->amount, 2, '.', '') }}"
                                                {{ $detail->is_variable ? '' : 'readonly' }}>
                                        </div>
                                        <div class="col-lg-2 col-md-4">
                                            <label class="form-label fw-semibold">Quantity</label>
                                            <input type="number" class="form-control fee-quantity" name="quantities[]" 
                                                value="{{ $detail->quantity }}" min="1">
                                        </div>
                                        <div class="col-lg-2 col-md-6">
                                            <label class="form-label fw-semibold">Label</label>
                                            <select class="form-select fee-label" name="labels[]" required>
                                                <option value="">-- Select Label --</option>
                                                <option value="None" {{ $detail->fee_label == 'None' ? 'selected' : '' }}>None</option>
                                                <option value="Certification Fee" {{ $detail->fee_label == 'Certification Fee' ? 'selected' : '' }}>Certification Fee</option>
                                                <option value="Certified True Copy" {{ $detail->fee_label == 'Certified True Copy' ? 'selected' : '' }}>Certified True Copy</option>
                                                <option value="Others" {{ !in_array($detail->fee_label, ['Certification Fee','Certified True Copy','None','']) ? 'selected' : '' }}>Others (specify)</option>
                                            </select>
                                            <input type="text" name="custom_labels[]" class="form-control mt-2 fee-label-other {{ !in_array($detail->fee_label, ['Certification Fee','Certified True Copy','None','']) ? '' : 'd-none' }}"
                                                value="{{ !in_array($detail->fee_label, ['Certification Fee','Certified True Copy','None','']) ? $detail->fee_label : '' }}"
                                                placeholder="Enter label">
                                        </div>
                                        <div class="col-lg-1 col-md-2 text-center">
                                            <button type="button" class="btn btn-danger btn-sm remove-row">X</button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="text-center">
                            <button type="button" class="btn btn-success btn-sm mb-3" id="addFeeRow">+ Add Fee</button>
                        </div>

                        <div class="mt-3 text-center">
                            <h4 class="fw-bold">Total Amount: ₱<span id="total-amount">0.00</span></h4>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary mt-3" id="confirmPaymentButton">
                                Approve and Confirm Payment
                            </button>
                        </div>
                    </form>

                    <form action="{{ route('payments.disapprove', ['id' => $transactionDetails[0]->transaction_id]) }}" method="POST"
                        onsubmit="return confirm('Are you sure you want to disapprove and delete this transaction?');"
                        class="mt-2 text-center">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            Disapprove
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
    .suggestion-item:hover {
        background-color: #0d6efd;
        color: white;
    }
</style>

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

    function createRow() {
        const container = document.getElementById('feesContainer');
        const row = document.createElement('div');
        row.classList.add('fee-row', 'card', 'p-3', 'mb-3', 'shadow-sm');

        row.innerHTML = `
            <div class="row g-3 align-items-end">
                <div class="col-lg-5 col-md-12">
                    <label class="form-label fw-semibold">Fee Name</label>
                    <input type="text" class="form-control fee-name" placeholder="Search fee..." autocomplete="off">
                    <input type="hidden" class="fee-id" name="fee_ids[]">
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label fw-semibold">Amount</label>
                    <input type="number" class="form-control fee-amount" name="amounts[]" step="0.01" readonly>
                </div>
                <div class="col-lg-2 col-md-4">
                    <label class="form-label fw-semibold">Quantity</label>
                    <input type="number" class="form-control fee-quantity" name="quantities[]" value="1" min="1">
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label fw-semibold">Label</label>
                    <select class="form-select fee-label" name="labels[]" required>
                        <option value="">-- Select Label --</option>
                        <option value="None">None</option>
                        <option value="Certification Fee">Certification Fee</option>
                        <option value="Certified True Copy">Certified True Copy</option>
                        <option value="Others">Others (specify)</option>
                    </select>
                    <input type="text" class="form-control mt-2 fee-label-other d-none" placeholder="Enter label">
                </div>
                <div class="col-lg-1 col-md-2 text-center">
                    <button type="button" class="btn btn-danger btn-sm remove-row">X</button>
                </div>
            </div>
        `;
        container.appendChild(row);
        bindRow(row);
    }

    function bindRow(row) {
        const feeNameInput = row.querySelector('.fee-name');
        const amountInput = row.querySelector('.fee-amount');
        const feeIdInput = row.querySelector('.fee-id');
        const quantityInput = row.querySelector('.fee-quantity');
        const labelSelect = row.querySelector('.fee-label');
        const labelOther = row.querySelector('.fee-label-other');

        let suggestionsList = document.createElement('div');
        suggestionsList.classList.add('border', 'rounded', 'bg-white', 'position-absolute', 'shadow-sm');
        suggestionsList.style.maxHeight = '150px';
        suggestionsList.style.overflowY = 'auto';
        suggestionsList.style.display = 'none';
        suggestionsList.style.zIndex = '2000';
        document.body.appendChild(suggestionsList);

        function positionSuggestions() {
            const rect = feeNameInput.getBoundingClientRect();
            suggestionsList.style.width = rect.width + 'px';
            suggestionsList.style.left = rect.left + window.scrollX + 'px';
            suggestionsList.style.top = rect.bottom + window.scrollY + 'px';
        }

        function renderSuggestions(filteredFees) {
            suggestionsList.innerHTML = '';
            filteredFees.forEach(fee => {
                const div = document.createElement('div');
                div.classList.add('suggestion-item', 'p-2');
                div.textContent = `${fee.fee_name} — ₱${parseFloat(fee.amount).toFixed(2)}`;
                div.style.cursor = 'pointer';
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
            amountInput.readOnly = !fee.is_variable;
            feeIdInput.value = fee.id;
            suggestionsList.style.display = 'none';
            updateTotal();
        }

        feeNameInput.addEventListener('input', () => {
            const query = feeNameInput.value.toLowerCase();
            if (query.length < 1) return (suggestionsList.style.display = 'none');
            const matches = feesData.filter(f => f.fee_name.toLowerCase().includes(query)).slice(0, 10);
            renderSuggestions(matches);
        });

        document.addEventListener('click', (e) => {
            if (!suggestionsList.contains(e.target) && e.target !== feeNameInput) {
                suggestionsList.style.display = 'none';
            }
        });

        labelSelect.addEventListener('change', () => {
            if (labelSelect.value === 'Others') labelOther.classList.remove('d-none');
            else {
                labelOther.classList.add('d-none');
                labelOther.value = '';
            }
        });

        amountInput.addEventListener('input', updateTotal);
        quantityInput.addEventListener('input', updateTotal);
        row.querySelector('.remove-row').addEventListener('click', () => {
            row.remove();
            updateTotal();
        });
    }

    document.getElementById('addFeeRow').addEventListener('click', createRow);
    window.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.fee-row').forEach(bindRow);
        updateTotal();
    });
</script>

@endsection
