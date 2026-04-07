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

            <div class="mt-5"></div>

            <h5 class="text-center mt-3 mb-4 fw-bold">MONTHLY STATEMENT OF ACCOUNT</h5>

            <!-- BASIC INFO -->
            <div class="mb-3">
                <label>TO:</label>
                    <input type="text" id="concessionaire_name" name="concessionaire_name"
                        class="form-control" placeholder="Type or select concessionaire" required autocomplete="off">
                    <div id="suggestionBox" class="bg-white border rounded shadow-sm position-absolute w-100 mt-1"
                        style="z-index:1000; display:none; max-height:180px; overflow-y:auto;">
                    </div>
            </div>

            <div class="mb-3">
                <label>RE:</label>
                <select name="utility_type" class="form-select border-0 border-bottom rounded-0">
                    <option value="Water" selected>Water</option>
                </select>
            </div>

            <div class="mb-3">
                <label>Bill Date:</label>
                <input type="date" name="bill_date" class="form-control border-0 border-bottom rounded-0">
            </div>

            <div class="mb-3">
                <label>Billing Period:</label>
                <select name="billing_period" class="form-select border-0 border-bottom rounded-0">
                    <option value="">Select Month</option>
                    @foreach(range(1,12) as $month)
                        <option value="{{ $month }}">
                            {{ \Carbon\Carbon::create()->month($month)->format('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mt-5"></div>

            <!-- BILL TABLE -->
            <div class="d-flex justify-content-center">
                <table style="width:70%; font-size:14px;">
                    <tr>
                        <td class="text-end pe-3 py-2">Current Charges:</td>
                        <td>
                            <input type="number" step="0.01" name="current_charges" id="current_charges" class="form-control">
                        </td>
                    </tr>
                    <tr>
                        <td class="text-end pe-3 py-2">Previous Unpaid Amount:</td>
                        <td>
                            <input type="number" step="0.01" name="water_previous_unpaid" id="water_previous_unpaid" class="form-control">
                        </td>
                    </tr>
                    <tr>
                        <td class="text-end pe-3 py-2 fw-bold">Total Amount Due:</td>
                        <td>
                            <input type="number" step="0.01" name="total_due" id="total_due" class="form-control fw-bold" readonly>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- NOTE -->
            <div class="mt-4">
                <p>
                    Your usual prompt payment of the above stated amount to the Cashier’s Office will be highly appreciated.
                    Payment Due Date:
                    <input type="date" name="due_date" class="form-control d-inline-block w-auto">
                </p>

                <p>
                    Present this billing to the Cashier when paying. Disregard this notice if payment has been made.
                </p>
            </div>

            <div class="mt-5"></div>

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
                    Generate Water Bill
                </button>
            </div>

        </form>
    </div>
</main>

<script>
    const concessionaires = @json($concessionaires -> pluck('name'));
    const input = document.getElementById('concessionaire_name');
    const box = document.getElementById('suggestionBox');

    const currentCharges = document.getElementById('current_charges');
    const previousUnpaid = document.getElementById('water_previous_unpaid');
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

    function computeTotal() {
        const current = parseFloat(currentCharges.value) || 0;
        const previous = parseFloat(previousUnpaid.value) || 0;
        totalDue.value = (current + previous).toFixed(2);
    }

    currentCharges.addEventListener('input', computeTotal);
    previousUnpaid.addEventListener('input', computeTotal);

    function handleSubmit() {
        // wait a bit so the request is sent to new tab first
        setTimeout(() => {
            document.querySelector('form').reset();
        }, 300);
    }

</script>

@endsection