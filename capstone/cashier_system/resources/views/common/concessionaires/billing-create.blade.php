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

                <div class="mb-3">
                    <label for="concessionaire_id" class="form-label">Concessionaire</label>
                    <select name="concessionaire_id" id="concessionaire_id" class="form-select" required>
                        <option value="">-- Select --</option>
                        @foreach($concessionaires as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
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
                        <div class="col-md-6" id="previousReadingField">
                            <label class="form-label">Previous Reading (First Bill Only)</label>
                            <input type="number" name="previous_reading" id="previous_reading" class="form-control" step="0.01">
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
                            <label class="form-label">University kWh (Total)</label>
                            <input type="number" name="university_total_kwh" class="form-control" step="0.01">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">University Bill Amount (Total)</label>
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
