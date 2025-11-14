@extends('layout.main-user')

@section('content')

<main style="min-height:85vh; padding:5% 5% 8% 5%; background: linear-gradient(to bottom, #f5f7fa, #eef1f5);">
    
    <div class="container-fluid">
        <div class="p-4 p-md-5 rounded mx-auto shadow-sm classy-card" style="max-width:900px;">
        <div class="accent-bar mb-4"></div>
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

                <div class="mb-3">
                    <label for="contact" class="form-label fw-semibold">Email Address (optional)</label>
                    <input type="text" class="form-control" id="contact" name="contact"
                        placeholder="customer@email.com">
                </div>

                <!-- Fee Section -->
                <h3 class="mt-4 mb-3 text-center text-md-start">Fees</h3>

                <div id="feesContainer">
                    <!-- Fee rows will be added dynamically -->
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
                        Submit
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

    .suggestions-list {
        max-height: 150px;
        overflow-y: auto;
        scrollbar-width: thin;
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

    // ---------- Cooldown configuration ----------
    const COOLDOWN_MINUTES = 3; // change to desired minutes
    const COOLDOWN_KEY = 'studentPaymentCooldownUntil';
    let cooldownIntervalId = null;

    // ---------- Cooldown helpers ----------
    function applyCooldownFromTimestamp(cooldownUntil) {
        if (cooldownIntervalId) {
            clearInterval(cooldownIntervalId);
            cooldownIntervalId = null;
        }

        const form = document.getElementById('paymentForm');
        const controls = form.querySelectorAll('input, button, select, textarea');
        controls.forEach(el => el.disabled = true);

        const addFeeBtn = document.getElementById('addFeeRow');
        if (addFeeBtn) addFeeBtn.disabled = true;
        document.querySelectorAll('.remove-row').forEach(b => b.disabled = true);

        let notice = document.getElementById('cooldownNotice');
        if (!notice) {
            notice = document.createElement('p');
            notice.id = 'cooldownNotice';
            notice.className = 'text-danger mt-3 text-center fw-bold';
            form.appendChild(notice);
        }

        function update() {
            const now = Date.now();
            let remainingMs = cooldownUntil - now;
            if (remainingMs <= 0) {
                clearInterval(cooldownIntervalId);
                cooldownIntervalId = null;
                localStorage.removeItem(COOLDOWN_KEY);
                notice.textContent = "✅ You can now submit again.";
                controls.forEach(el => el.disabled = false);
                if (addFeeBtn) addFeeBtn.disabled = false;
                document.querySelectorAll('.remove-row').forEach(b => b.disabled = false);
                setTimeout(() => notice.remove(), 2000);
                return;
            }

            const remainingSeconds = Math.floor(remainingMs / 1000);
            const mins = Math.floor(remainingSeconds / 60);
            const secs = remainingSeconds % 60;
            notice.textContent = `⏳ Please wait ${mins}m ${secs}s before submitting again.`;
        }

        update();
        cooldownIntervalId = setInterval(update, 1000);
    }

    function checkCooldownOnLoad() {
        const raw = localStorage.getItem(COOLDOWN_KEY);
        if (!raw) return;
        const cooldownUntil = parseInt(raw, 10);
        if (isNaN(cooldownUntil)) {
            localStorage.removeItem(COOLDOWN_KEY);
            return;
        }
        if (cooldownUntil > Date.now()) {
            applyCooldownFromTimestamp(cooldownUntil);
        } else {
            localStorage.removeItem(COOLDOWN_KEY);
        }
    }

    function startCooldownNow(minutes = COOLDOWN_MINUTES) {
        const cooldownUntil = Date.now() + minutes * 60 * 1000;
        localStorage.setItem(COOLDOWN_KEY, String(cooldownUntil));
        applyCooldownFromTimestamp(cooldownUntil);
    }

    // ---------- Handle form submission ----------
    const paymentForm = document.getElementById('paymentForm');
    if (paymentForm) {
        paymentForm.addEventListener('submit', function (e) {
            // Don't start cooldown or disable until AFTER the form has been accepted by the browser
            // Create overlay first
            const overlay = document.createElement('div');
            overlay.className =
                'position-fixed top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-flex align-items-center justify-content-center';
            overlay.style.zIndex = '5000';
            overlay.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="mt-3 fw-semibold text-primary">Processing payment...</div>
                </div>
            `;
            document.body.appendChild(overlay);

            // ✅ Let Laravel handle the form POST naturally
            // We'll trigger cooldown *after* the request leaves the page
            window.addEventListener('beforeunload', () => {
                startCooldownNow();
            });

            // Do not call preventDefault() or disable inputs
            // The browser will handle the POST including the CSRF token
        });
    }

    // ---------- Initialization ----------
    window.addEventListener('DOMContentLoaded', () => {
        createRow();

        const hasActiveBatch = @json($hasActiveBatch);
        if (!hasActiveBatch) {
            document.querySelectorAll('#paymentForm input, #paymentForm button, #paymentForm select, #paymentForm textarea').forEach(el => {
                el.disabled = true;
            });

            const form = document.getElementById('paymentForm');
            const notice = document.createElement('p');
            notice.className = 'text-danger mt-2 text-center';
            notice.textContent = '🛑 Payment form is temporarily disabled.';
            form.appendChild(notice);
        } else {
            checkCooldownOnLoad();
        }
    });

    window.addEventListener('pageshow', () => checkCooldownOnLoad());

    // ---------- Fee row & total logic ----------
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
        const container = document.getElementById('feesContainer');

        const row = document.createElement('div');
        row.classList.add('fee-row', 'row', 'mb-3', 'align-items-end');
        row.innerHTML = `
            <div class="col-lg-6 col-md-12 mb-2">
                <label class="form-label fw-semibold">Fee Name</label>
                <input type="text" class="form-control fee-name" placeholder="Search fee..." autocomplete="off">
                <input type="hidden" class="fee-id" name="fee_ids[]">
            </div>
            <div class="col-lg-3 col-md-6 mb-2">
                <label class="form-label fw-semibold">Amount</label>
                <input type="number" class="form-control fee-amount" step="0.01" readonly>
            </div>
            <div class="col-lg-2 col-md-4 mb-2">
                <label class="form-label fw-semibold">Quantity</label>
                <input type="number" class="form-control fee-quantity" name="quantities_temp[]" value="1" min="1">
            </div>
            <div class="col-lg-1 col-md-2 text-center mb-2">
                <button type="button" class="btn btn-danger btn-sm remove-row">X</button>
            </div>
        `;

        container.appendChild(row);

        const feeNameInput = row.querySelector('.fee-name');
        const amountInput = row.querySelector('.fee-amount');
        const quantityInput = row.querySelector('.fee-quantity');
        const feeIdInput = row.querySelector('.fee-id');

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
            const query = feeNameInput.value.trim().toLowerCase();
            const matches = query.length > 0
                ? feesData.filter(fee => fee.fee_name.toLowerCase().includes(query)).slice(0, 10)
                : feesData.slice(0, 10);
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
                items[currentIndex].scrollIntoView({ block: 'nearest' });
            }
        });

        document.addEventListener('click', (e) => {
            if (!suggestionsList.contains(e.target) && e.target !== feeNameInput) {
                suggestionsList.style.display = 'none';
            }
        });

        quantityInput.addEventListener('input', updateTotal);
        row.querySelector('.remove-row').addEventListener('click', () => {
            row.remove();
            updateTotal();
        });
    }

    const addBtn = document.getElementById('addFeeRow');
    if (addBtn) addBtn.addEventListener('click', createRow);

    const confirmBtn = document.getElementById('confirmPaymentButton');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function(e) {
            const inputs = document.querySelectorAll('.fee-name');
            let hasInvalid = false;

            inputs.forEach(input => {
                const feeIdInput = input.closest('.fee-row').querySelector('.fee-id');
                if (!feeIdInput.value) {
                    input.classList.add('is-invalid');
                    hasInvalid = true;
                } else {
                    input.classList.remove('is-invalid');
                }
            });

            if (hasInvalid) {
                e.preventDefault();
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
        });
    }
</script>


@endsection
