@extends('layout.main-master')

@section('content')

<main style="min-height:85vh; padding:5% 5% 8% 5%; background: linear-gradient(to bottom, #f5f7fa, #eef1f5);">
        
    <div class="container-fluid">
        <div class="p-4 p-md-5 rounded mx-auto shadow-sm classy-card" style="max-width:900px;">
        <div class="accent-bar mb-4"></div>

           <h1 class="mb-4 text-center">Customer Payment Form</h1>

            @if(session('success'))
                <p class="text-success text-center">{{ session('success') }}</p>
            @elseif(session('error'))
                <p class="text-danger text-center">{{ session('error') }}</p>
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
                <div class="alert alert-danger text-center">
                    🚫 Cannot finalize transaction. No receipt numbers available. Please load a new batch first.
                </div>
            @endif

            <form method="POST" action="{{ route('payments.customer.new') }}" id="paymentForm">
                @csrf

                <!-- Customer Info -->
                <div class="mb-3">
                    <label for="customer_name" class="form-label fw-semibold">Customer Name</label>
                    <input type="text" class="form-control" id="customer_name" name="customer_name"
                        placeholder="LAST NAME, FIRST NAME M.I." required>
                </div>

                <div class="mb-3">
                    <label for="contact" class="form-label fw-semibold">Email Address (optional)</label>
                    <input type="text" class="form-control" id="contact" name="contact"
                        placeholder="customer@email.com">
                </div>

                <!-- Fee Section -->
                <h3 class="mt-4 mb-3 text-center text-md-start">Fees</h3>

                <div id="feesContainer">
                    <!-- Dynamic fee cards -->
                </div>

                <div class="d-grid d-md-inline mb-3 text-center">
                    <button type="button" class="btn btn-success btn-sm mb-3 w-100 w-md-auto" id="addFeeRow">
                        + Add Fee
                    </button>
                </div>

                <div class="mt-3 text-center">
                    <h4 class="fw-bold">Total Amount: ₱<span id="total-amount">0.00</span></h4>
                </div>

                <div class="d-grid d-md-inline text-center">
                    <button type="submit" class="btn btn-primary mt-3 w-100 w-md-auto" id="confirmPaymentButton">
                        Confirm Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<style>
    .suggestion-item:hover {
        background-color: #0d6efd;
        color: white;
    }

    .classy-card {
        background: #ffffff;
        border-radius: 18px;
        border: 1px solid #e8e8e8;
        box-shadow: 0 4px 14px rgba(0,0,0,0.07);
        transition: box-shadow .2s ease-in-out;
    }

    .classy-card:hover {
        box-shadow: 0 6px 18px rgba(0,0,0,0.10);
    }

    h1, h3, h4 {
        font-family: 'Inter', sans-serif;
        letter-spacing: -0.4px;
    }

    h1 {
        font-weight: 700;
    }

    h3 {
        font-weight: 600;
        color: #333;
    }

    .fee-row {
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        background: #ffffff;
        transition: box-shadow 0.2s ease;
    }

    .fee-row:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .btn-success.btn-sm, .btn-danger.btn-sm, .btn-primary {
        border-radius: 10px !important;
        padding: 8px 16px !important;
        font-weight: 600;
    }

    .btn-danger.btn-sm {
        padding: 6px 12px !important;
    }

    .form-control, .form-select {
        border-radius: 10px;
        border: 1px solid #d7d7d7;
        padding: 10px 12px;
    }

    .form-control:focus, .form-select:focus {
        border-color: #0d6efd !important;
        box-shadow: 0 0 0 0.15rem rgba(13,110,253,0.15) !important;
    }

    .alert {
        border-radius: 12px;
        padding: 14px 18px;
        font-size: 15px;
    }

    #total-amount {
        font-size: 1.8rem;
    }

    .accent-bar {
        height: 6px;
        width: 100%;
        border-radius: 6px;
        background: linear-gradient(to right, #0d6efd, #3b82f6);
    }

</style>

<script>
const feesData = @json($fees);
let rowCount = 0;
const maxRows = 8;

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
    if (rowCount >= maxRows) {
        alert(`You can only add up to ${maxRows} fees.`);
        return;
    }

    rowCount++;
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
                    <option value="None">None</option>
                    <option value="Certification Fee">Certification Fee</option>
                    <option value="Certified True Copy">Certified True Copy</option>
                    <option value="Others">Others (specify)</option>
                </select>
                <input type="text" class="form-control mt-2 fee-label-other d-none" name="labels_other[]">
            </div>
            <div class="col-lg-1 col-md-2 text-center">
                <button type="button" class="btn btn-danger btn-sm remove-row">X</button>
            </div>
        </div>
    `;

    container.appendChild(row);

    const feeNameInput = row.querySelector('.fee-name');
    const amountInput = row.querySelector('.fee-amount');
    const quantityInput = row.querySelector('.fee-quantity');
    const feeIdInput = row.querySelector('.fee-id');
    const labelSelect = row.querySelector('.fee-label');
    const labelOther = row.querySelector('.fee-label-other');

    // Suggestion dropdown
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
        const matches = feesData
            .filter(f => f.fee_name.toLowerCase().includes(query))
            .slice(0, 10);
        renderSuggestions(matches);
    });

    feeNameInput.addEventListener('focus', () => {
        const matches = feesData.slice(0, 10);
        renderSuggestions(matches);
    });

    document.addEventListener('click', (e) => {
        if (!suggestionsList.contains(e.target) && e.target !== feeNameInput) {
            suggestionsList.style.display = 'none';
        }
    });

    labelSelect.addEventListener('change', () => {
        if (labelSelect.value === 'Others') {
            labelOther.classList.remove('d-none');
        } else {
            labelOther.classList.add('d-none');
            labelOther.value = '';
        }
    });

    amountInput.addEventListener('input', updateTotal);
    quantityInput.addEventListener('input', updateTotal);

    row.querySelector('.remove-row').addEventListener('click', () => {
        row.remove();
        rowCount--;
        updateTotal();
        // Re-enable Add Fee button if we are below maxRows
        document.getElementById('addFeeRow').disabled = false;
    });

    // Disable Add Fee button if max reached
    document.getElementById('addFeeRow').disabled = rowCount >= maxRows;
}

const addFeeButton = document.getElementById('addFeeRow');
addFeeButton.addEventListener('click', createRow);

// Initial row
window.addEventListener('DOMContentLoaded', () => {
    createRow();
});

</script>

@endsection
