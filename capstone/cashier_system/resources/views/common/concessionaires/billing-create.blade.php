@extends('layout.main-master')

@section('content')
<main style="min-height:85vh; padding:5% 5% 8% 5%; background: linear-gradient(to bottom, #f5f7fa, #eef1f5);">
    <div class="container" style="width:75%">
        <div class="p-5 rounded bill-card">

            <div class="accent-bar"></div>

            <h2 class="mb-4 fw-bold">Create Concessionaire Billing</h2>

            @if(session('success'))
                <div class="alert alert-success rounded-3">{{ session('success') }}</div>
            @elseif(session('error'))
                <div class="alert alert-danger rounded-3">{{ session('error') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger rounded-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

                <form method="POST" action="{{ url()->current() }}">
                    @csrf

                    <!-- Concessionaire Searchable Input -->
                    <div class="mb-3 position-relative">
                        <label for="concessionaire_name" class="form-label">Concessionaire</label>
                        <input type="text" id="concessionaire_name" name="concessionaire_name"
                            class="form-control" placeholder="Type or select concessionaire" required autocomplete="off">
                        <div id="suggestionBox" class="bg-white border rounded shadow-sm position-absolute w-100 mt-1"
                            style="z-index:1000; display:none; max-height:180px; overflow-y:auto;"></div>
                    </div>

                    <div class="mb-3">
                        <label for="utility_type" class="form-label">Utility Type</label>
                        <select name="utility_type" id="utility_type" class="form-select" required>
                            <option value="">-- Select --</option>
                            <option value="Water">Water</option>
                            <option value="Electricity">Electricity</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="billing_period" class="form-label">Billing Period (Month)</label>
                        <select name="billing_period" class="form-select" required>
                            <option value="">-- Select Month --</option>
                            @foreach(range(1, 12) as $month)
                            <option value="{{ $month }}">{{ \Carbon\Carbon::create()->month($month)->format('F') }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="due_date" class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control" required>
                    </div>

                    <!-- Water Fields -->
                    <div id="waterFields" class="d-none utility-box">
                    <div class="section-title">💧 Water Billing Details</div>
                        <div class="mb-3">
                            <label for="current_charges" class="form-label">Current Charges (₱)</label>
                            <input type="number" name="current_charges" class="form-control" step="0.01">
                        </div>
                        <div class="mb-3">
                            <label for="previous_unpaid" class="form-label">Previous Unpaid (₱)</label>
                            <input type="number" name="water_previous_unpaid" class="form-control" step="0.01">
                        </div>
                    </div>

                    <!-- Electricity Fields -->
                    <div id="electricityFields" class="d-none utility-box">
                    <div class="section-title">⚡ Electricity Billing Details</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Bill Start Date</label>
                                <input type="date" name="bill_start_date" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Bill End Date</label>
                                <input type="date" name="bill_end_date" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Previous Reading</label>
                                <input type="number" name="previous_reading" class="form-control" step="0.01">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Previous Unpaid (₱)</label>
                                <input type="number" name="electricity_previous_unpaid" class="form-control" step="0.01">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Current Reading</label>
                                <input type="number" name="current_reading" class="form-control" step="0.01">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cost per kWh</label>
                                <input type="number" name="cost_per_kwh" class="form-control" step="0.0001">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">University Total kWh</label>
                                <input type="number" name="university_total_kwh" class="form-control" step="0.01">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">University Total Bill (₱)</label>
                                <input type="number" name="university_total_bill" class="form-control" step="0.01">
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary px-4 py-2" style="border-radius:10px; font-weight:600;">
                            Create Bill
                        </button>
                    </div>

                </form>
        </div>
    </div>
</main>

<style>
    /* Card styling */
    .bill-card {
        background: #ffffff;
        border-radius: 18px;
        border: 1px solid #e6e6e6;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
        transition: box-shadow .2s ease-in-out;
    }

    .bill-card:hover {
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
    }

    /* Accent bar on top of card */
    .accent-bar {
        height: 6px;
        width: 100%;
        background: linear-gradient(to right, #0d6efd, #4e9cff);
        border-radius: 6px 6px 0 0;
        margin-top: -20px;
        margin-bottom: 20px;
    }

    /* Icons on section labels */
    .form-label {
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Improve inputs */
    .form-control,
    .form-select {
        border-radius: 10px;
        padding: 10px 12px;
        border: 1px solid #d4d8dd;
        transition: all .15s ease-in-out;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #0d6efd !important;
        box-shadow: 0 0 0 0.15rem rgba(13, 110, 255, .18) !important;
    }

    /* Suggestion box hover */
    .suggestion-item:hover {
        background: #f0f4ff;
    }

    /* Section titles */
    .section-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #333;
        margin-top: 20px;
        margin-bottom: 10px;
        letter-spacing: -.3px;
    }

    /* Utility section box */
    .utility-box {
        border: 1px solid #e7e7e7;
        background: #fafbfc;
        padding: 18px;
        border-radius: 14px;
        margin-bottom: 20px;
    }
</style>


<script>
    const concessionaires = @json($concessionaires -> pluck('name'));
    const input = document.getElementById('concessionaire_name');
    const box = document.getElementById('suggestionBox');

    function showSuggestions(matches) {
        box.innerHTML = '';
        matches.forEach(name => {
            const div = document.createElement('div');
            div.textContent = name;
            div.className = 'p-2 suggestion-item';
            div.style.cursor = 'pointer';
            div.addEventListener('mousedown', () => {
                input.value = name;
                box.style.display = 'none';
            });
            box.appendChild(div);
        });
        box.style.display = matches.length ? 'block' : 'none';
    }

    input.addEventListener('input', () => {
        const query = input.value.toLowerCase().trim();
        const matches = query ?
            concessionaires.filter(c => c.toLowerCase().includes(query)).slice(0, 10) :
            concessionaires.slice(0, 10);
        showSuggestions(matches);
    });

    input.addEventListener('blur', () => setTimeout(() => box.style.display = 'none', 150));

    const utilitySelect = document.getElementById('utility_type');
    const waterFields = document.getElementById('waterFields');
    const electricityFields = document.getElementById('electricityFields');
    utilitySelect.addEventListener('change', () => {
        const selected = utilitySelect.value;
        waterFields.classList.toggle('d-none', selected !== 'Water');
        electricityFields.classList.toggle('d-none', selected !== 'Electricity');
    });
</script>
@endsection