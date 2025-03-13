@extends('layout.main-master')

@section('content')

<main style="background-image:url('/bgpup3.jpg'); background-repeat:no-repeat; background-size:cover; min-height: 85vh; padding: 5%;">

    <div class="container" style="width:50%">
        <div class="bg-light" style="padding:5%">
            <h1>New Concessionaire Billing Form</h1>

            @if(session('success'))
                <p style="color: green;">{{ session('success') }}</p>
            @elseif(session('error'))
                <p style="color: red;">{{ session('error') }}</p>
            @endif

            <form method="POST" action="{{ route('AddConcessionaireBilling') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Concessionaire</label>
                    <select name="concessionaire_id" class="form-select" required>
                        <option value="">Select Concessionaire</option>
                        @foreach($concessionaires as $concessionaire)
                            <option value="{{ $concessionaire->id }}">{{ $concessionaire->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Billing Type</label>
                    <select name="utility_type" class="form-select" required>
                        <option value="Water">Water</option>
                        <option value="Electricity">Electricity</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Amount</label>
                    <input type="number" name="bill_amount" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-control" required>
                </div>

                <div style="padding: 2%">
                    <button class="btn btn-danger btn-lg" type="submit">Submit</button>
                </div>

            </form>
        </div>
    </div>

</main>


@endsection
