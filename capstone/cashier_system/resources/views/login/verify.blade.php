@extends('layout.main-user')
@section('content')

<main style="background-image:url('/bgpup3.jpg'); background-repeat:no-repeat; background-size:cover; min-height: 85vh; padding: 5%;">
    <div class="container" style="width:50%">
        <div class="bg-light" style="padding:5%">
            <div class="card">
                <div class="card-header">
                    <h1 class="card-title">Email Verification</h1>
                </div>
                <div class="card-body">
                    {{-- Flash messages --}}
                    @if(Session::has('message'))
                        <div class="alert alert-success" role="alert">
                            {{ Session::get('message') }}
                        </div>
                    @elseif(Session::has('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ Session::get('error') }}
                        </div>
                    @endif

                    {{-- OTP Input Form --}}
                    <form method="POST" action="{{ route('otp.verify') }}">
                        @csrf
                        <div class="mb-4">
                            <label for="otp_code" class="form-label">Enter OTP</label>
                            <input type="text" name="otp_code" id="otp_code" class="form-control" maxlength="6" required>
                            @error('otp_code') 
                                <div class="text-danger">{{ $message }}</div> 
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary text-light">Verify</button>
                        </div>
                    </form>

                    {{-- Resend OTP --}}
                    <form method="POST" action="{{ route('otp.resend') }}" class="mt-3">
                        @csrf
                        <button type="submit" class="btn btn-link">Resend OTP</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

@endsection
