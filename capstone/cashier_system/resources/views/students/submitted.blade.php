@extends('layout.main-user')

@section('content')
<main style="background-image:url('/bgpup1.jpg'); background-repeat:no-repeat; background-size:cover; min-height: 85vh; display: flex; justify-content: center; align-items: center; padding: 1rem;">
    <div class="text-center bg-light p-4 p-md-5 rounded w-100" style="max-width: 600px;">
        <h1 class="text-success fs-3 fs-md-2">Transaction Submitted Successfully!</h1>
        <p class="mb-2">Your Transaction Number is:</p>
        <h1 class="fw-bold" style="font-size: 4rem; font-size: clamp(3rem, 10vw, 8rem);">{{ $transaction_num }}</h1>
        <p class="mt-3">Please Proceed inside the Cashier for Payment and Receipt</p>
    </div>
</main>
@endsection
