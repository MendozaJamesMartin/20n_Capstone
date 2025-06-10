@extends('layout.main-user')

@section('content')
    <main style="background-image:url('/bgpup1.jpg'); background-repeat:no-repeat; background-size:cover; min-height: 85vh; display: flex; justify-content: center; align-items: center;">
        <div class="text-center bg-light p-5 rounded" style="width: 50%;">
            <h1 class="text-success">Transaction Submitted Successfully!</h1>
            <p>Your Transaction Number is:</p>
            <h1 style="font-size: 8rem;">{{ $transactionId }}</h1>
            <p>Please Proceed inside the Cashier for Payment and Receipt</p>
        </div>
    </main>
@endsection
