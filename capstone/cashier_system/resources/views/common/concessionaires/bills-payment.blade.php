@extends('layout.main-master')

@section('content')

<main style="background-image:url('/bgpup3.jpg'); background-repeat:no-repeat; background-size:cover; min-height: 85vh; padding: 5%;">
    <div class="container" style="width: 600px;">
        <div class="bg-light p-4 rounded shadow-sm">

            <h2>Concessionaire Bill Payment</h2>

            @if(session('success'))
                <div class="alert alert-success mt-3">{{ session('success') }}</div>
            @elseif(session('error'))
                <div class="alert alert-danger mt-3">{{ session('error') }}</div>
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

                <div class="mb-3">
                    <label for="utility_type" class="form-label">Utility Type</label>
                    <select name="utility_type" id="utility_type" class="form-select" required>
                        <option value="">-- Choose --</option>
                        <option value="Water">Water</option>
                        <option value="Electricity">Electricity</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="amount_paid" class="form-label">Amount to Pay (₱)</label>
                    <input type="number" step="0.01" min="0" name="amount_paid" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary w-100 mt-3" {{ !$hasActiveBatch ? 'disabled' : '' }}>
                    Submit Payment
                </button>
            </form>

        </div>
    </div>
</main>

@endsection
