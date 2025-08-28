@extends('layout.main-master')

@section('content')

<main style="background-image:url('/bgpup3.jpg'); background-repeat:no-repeat; background-size:cover; min-height: 85vh; padding: 5%;">
    <div class="container" style="width: 600px;">
        <div class="bg-light p-4 rounded shadow-sm">

            <h2>Concessionaire Bill Payment</h2>

            @if(session('success'))
                <div class="alert alert-success mt-3" style="white-space: pre-line;">{{ session('success') }}</div>
            @elseif(session('error'))
                <div class="alert alert-danger mt-3">{{ session('error') }}</div>
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
                <div class="alert alert-danger mt-3">
                    🚫 Cannot proceed with transaction. No receipt numbers available. Please load a new batch first.
                </div>
            @endif

            <form method="POST" action="{{ route('concessionaires.billing.payment') }}" id="paymentForm" class="mt-4">
                @csrf

                <div class="mb-3">
                    <label for="concessionaire_id" class="form-label">Select Concessionaire</label>
                    <select name="concessionaire_id" id="concessionaire_id" class="form-select" required>
                        <option value="">-- Choose --</option>
                        @foreach($concessionaires as $concessionaire)
                            <option value="{{ $concessionaire->id }}">{{ $concessionaire->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="utility-container">
                    <div class="utility-row row g-2 mb-2">
                        <div class="col-6">
                            <select name="utility_type[]" class="form-select utility-select" required>
                                <option value="">-- Choose --</option>
                                <option value="Water">Water</option>
                                <option value="Electricity">Electricity</option>
                            </select>
                        </div>
                        <div class="col-5">
                            <input type="number" step="0.01" min="0" name="amount_paid[]" class="form-control" placeholder="₱ Amount" required>
                        </div>
                        <div class="col-1 d-flex align-items-center">
                            <button type="button" class="btn btn-danger btn-sm remove-row" style="display:none;">&times;</button>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-secondary btn-sm mb-3" id="add-utility">+ Add Another Utility</button>

                <button type="submit" class="btn btn-primary w-100 mt-3" {{ !$hasActiveBatch ? 'disabled' : '' }}>
                    Submit Payment
                </button>
            </form>

        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('utility-container');
    const addBtn = document.getElementById('add-utility');

    addBtn.addEventListener('click', function() {
        if (container.querySelectorAll('.utility-row').length >= 2) return;

        const clone = container.querySelector('.utility-row').cloneNode(true);
        clone.querySelectorAll('input, select').forEach(el => el.value = '');
        clone.querySelector('.remove-row').style.display = 'inline-block';
        container.appendChild(clone);
        updateUtilityOptions();
    });

    container.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-row')) {
            e.target.closest('.utility-row').remove();
            updateUtilityOptions();
        }
    });

    container.addEventListener('change', function(e) {
        if (e.target.classList.contains('utility-select')) {
            updateUtilityOptions();
        }
    });

    function updateUtilityOptions() {
        const selectedValues = Array.from(container.querySelectorAll('.utility-select'))
            .map(s => s.value)
            .filter(v => v !== '');

        container.querySelectorAll('.utility-select').forEach(select => {
            Array.from(select.options).forEach(opt => {
                if (opt.value && selectedValues.includes(opt.value) && opt.value !== select.value) {
                    opt.disabled = true;
                } else {
                    opt.disabled = false;
                }
            });
        });
    }
});
</script>

@endsection
