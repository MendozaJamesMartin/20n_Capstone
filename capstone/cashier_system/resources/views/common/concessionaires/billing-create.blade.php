@extends('layout.main-master')

@section('content')
<main style="background-image:url('/bgpup3.jpg'); background-repeat:no-repeat; background-size:cover; min-height: 85vh; padding: 5%;">
    <div class="container" style="width:75%">
        <div class="bg-light p-5 rounded shadow-sm">
            <h2 class="mb-4">Create Concessionaire Billing</h2>

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
                <div id="waterFields" class="d-none">
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
                <div id="electricityFields" class="d-none">
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
                    <button type="submit" class="btn btn-primary">Create Bill</button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
const concessionaires = @json($concessionaires->pluck('name'));
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
    const matches = query
        ? concessionaires.filter(c => c.toLowerCase().includes(query)).slice(0, 10)
        : concessionaires.slice(0, 10);
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
