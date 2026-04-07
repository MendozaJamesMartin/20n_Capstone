@extends('layout.main-master')

@section('content')
<main style="min-height:85vh; padding:40px; background:#f5f7fa;">
    <div class="container bg-white p-5 shadow" style="max-width:900px; font-family:'Times New Roman', serif;">

        <form method="POST" action="{{ url()->current() }}" target="_blank" onsubmit="handleSubmit()">
            @csrf

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

            <!-- HEADER -->
            <div class="text-center">
                <h4 class="mb-0">POLYTECHNIC UNIVERSITY OF THE PHILIPPINES</h4>
                <small>Taguig Campus</small>
            </div>

            <div class="text-end mt-3">
                <input type="date" name="bill_date" class="form-control d-inline-block" style="width:200px;">
            </div>

            <h5 class="text-center mt-3 mb-4 fw-bold">MONTHLY STATEMENT OF ACCOUNT</h5>

            <!-- TO + RE -->
            <div class="mb-3">
                <label>TO:</label>
                    <input type="text" id="concessionaire_name" name="concessionaire_name"
                        class="form-control" placeholder="Type or select concessionaire" required autocomplete="off">
                    <div id="suggestionBox" class="bg-white border rounded shadow-sm position-absolute w-100 mt-1"
                        style="z-index:1000; display:none; max-height:180px; overflow-y:auto;">
                    </div>
            </div>

            <div class="mb-3 d-flex align-items-center gap-2">
                <label class="mb-0">RE:</label>

                <select name="utility_type" class="form-select w-auto">
                    <option value="Electricity">Electricity</option>
                </select>

                <span>Bill for the Month of</span>

                <select name="billing_period" class="form-select w-auto" >
                    @foreach(range(1,12) as $month)
                        <option value="{{ $month }}">
                            {{ \Carbon\Carbon::create()->month($month)->format('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- DATE RANGE -->
            <p>
                As per reading of your electric meter, the total kilowatt hours for the period from
                <input type="date" name="bill_start_date" class="form-control d-inline-block w-auto">
                to
                <input type="date" name="bill_end_date" class="form-control d-inline-block w-auto">
                are as follows:
            </p>

            <!-- READING TABLE -->
            <div class="text-center my-3">
                <table class="mx-auto" style="width:65%;">
                    <tr>
                        <td class="text-end pe-3">Current Reading:</td>
                        <td><input type="number" name="current_reading" id="current_reading" class="form-control"></td>
                    </tr>
                    <tr>
                        <td class="text-end pe-3">Previous Reading:</td>
                        <td><input type="number" name="previous_reading" id="previous_reading" class="form-control"></td>
                    </tr>
                    <tr>
                        <td class="text-end pe-3">Total:</td>
                        <td><input type="number" name="kwh_used" id="kwh_used" class="form-control" readonly></td>
                    </tr>
                </table>
            </div>

            <p>Hence, the computation of your electric bill is as follows:</p>

            <!-- COMPUTATION TABLE -->
            <div class="text-center my-3">
                <table class="mx-auto" style="width:65%;">
                    <tr>
                        <td class="text-end pe-3">Total Bill (PUPT):</td>
                        <td><input type="number" name="university_total_bill" id="university_total_bill" class="form-control"></td>
                    </tr>
                    <tr>
                        <td class="text-end pe-3">Total kWh Used:</td>
                        <td><input type="number" name="university_total_kwh" id="university_total_kwh" class="form-control"></td>
                    </tr>
                    <tr>
                        <td class="text-end pe-3">Cost per kWh:</td>
                        <td><input type="number" step="0.0001" name="cost_per_kwh" id="cost_per_kwh" class="form-control"></td>
                    </tr>

                    <tr><td colspan="2">&nbsp;</td></tr>

                    <tr>
                        <td class="text-end pe-3">Concessionaire’s Consumption =</td>
                        <td><input type="number" name="kwh_used_display" id="kwh_used_display" class="form-control" readonly></td>
                    </tr>
                    <tr>
                        <td class="text-end pe-3">x Cost per kWh =</td>
                        <td><input type="number" name="cost_per_kwh_display" id="cost_per_kwh_display" class="form-control" readonly></td>
                    </tr>
                    <tr>
                        <td class="text-end pe-3 fw-bold">Total Amount:</td>
                        <td><input type="number" name="current_charges" id="current_charges" class="form-control" readonly></td>
                    </tr>

                    <tr><td colspan="2">&nbsp;</td></tr>

                    <tr>
                        <td class="text-end pe-3">Previous Unpaid Amount:</td>
                        <td><input type="number" name="electricity_previous_unpaid" id="electricity_previous_unpaid" class="form-control"></td>
                    </tr>
                    <tr>
                        <td class="text-end pe-3 fw-bold text-decoration-underline">Total Amount Due:</td>
                        <td><input type="number" name="total_due" id="total_due" class="form-control" readonly></td>
                    </tr>
                </table>
            </div>

            <!-- NOTE -->
            <div class="mt-4">
                <p>
                    Your usual prompt payment of the above stated amount to the Cashier’s Office will be highly appreciated.
                    <br>
                    Payment Due Date:
                    <input type="date" name="due_date" class="form-control d-inline-block w-auto">
                    <br><br>
                    Present this billing to the Cashier when paying. Disregard this notice if payment has been made.
                </p>
            </div>

            <!-- SIGNATURE -->
            <div class="text-end mt-5">
                <div style="display:inline-block; text-align:left;">
                    @if(auth()->check())
                        {{ auth()->user()->first_name }}
                        @if(!empty(auth()->user()->middle_name))
                            {{ ' ' . substr(auth()->user()->middle_name, 0, 1) . '.' }}
                        @endif
                        {{ ' ' . auth()->user()->last_name }}
                    @else
                        Collecting Officer
                    @endif
                    <br>
                    Collecting Officer
                </div>
            </div>

            <!-- NOTED -->
            <div class="mt-5">
                <p>Noted by:</p>
                <p>Engr. Michael Zarco
                    <br>
                    Administrative Officer
                </p>
            </div>

            <!-- SUBMIT -->
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary px-4">
                    Generate Bill
                </button>
            </div>

        </form>
    </div>
</main>

<script>
    const concessionaires = @json($concessionaires -> pluck('name'));
    const input = document.getElementById('concessionaire_name');
    const box = document.getElementById('suggestionBox');

    const currentReading = document.getElementById('current_reading');
    const previousReading = document.getElementById('previous_reading');
    const kwhUsed = document.getElementById('kwh_used');
    const kwhUsedDisplay = document.getElementById('kwh_used_display');

    const costPerKwh = document.getElementById('cost_per_kwh');
    const costPerKwhDisplay = document.getElementById('cost_per_kwh_display');

    const currentCharges = document.getElementById('current_charges');
    const previousUnpaid = document.getElementById('electricity_previous_unpaid');
    const totalDue = document.getElementById('total_due');

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

    function computeAll() {
        // 1. kWh used = current - previous
        const current = parseFloat(currentReading.value) || 0;
        const previous = parseFloat(previousReading.value) || 0;
        const kwh = current - previous;

        kwhUsed.value = kwh >= 0 ? kwh.toFixed(2) : 0;
        kwhUsedDisplay.value = kwhUsed.value;

        // 2. cost per kWh display mirror
        const cost = parseFloat(costPerKwh.value) || 0;
        costPerKwhDisplay.value = cost.toFixed(4);

        // 3. current charges = kWh * cost
        const charges = kwh * cost;
        currentCharges.value = charges.toFixed(2);

        // 4. total due = previous unpaid + current charges
        const unpaid = parseFloat(previousUnpaid.value) || 0;
        const total = charges + unpaid;
        totalDue.value = total.toFixed(2);
    }

    // Attach listeners to ALL relevant inputs
    currentReading.addEventListener('input', computeAll);
    previousReading.addEventListener('input', computeAll);
    costPerKwh.addEventListener('input', computeAll);
    previousUnpaid.addEventListener('input', computeAll);

    function handleSubmit() {
        // wait a bit so the request is sent to new tab first
        setTimeout(() => {
            document.querySelector('form').reset();
        }, 300);
    }

</script>

@endsection