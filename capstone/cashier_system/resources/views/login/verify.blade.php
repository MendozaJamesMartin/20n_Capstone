@if(session('message'))
    <div class="alert alert-success">{{ session('message') }}</div>
@endif

<main style="background-image:url('/bgpup3.jpg'); background-repeat:no-repeat; background-size:cover; min-height: 85vh; padding: 5%;">

    <div class="container" style="width:50%">

        <form method="POST" action="{{ route('otp.verify') }}">
            @csrf
            <label>Enter OTP</label>
            <input type="text" name="otp_code" class="form-control" maxlength="6" required>
            @error('otp_code') <div class="text-danger">{{ $message }}</div> @enderror

            <button type="submit" class="btn btn-primary mt-3">Verify</button>
        </form>

        <form method="POST" action="{{ route('otp.resend') }}">
            @csrf
            <button type="submit" class="btn btn-link mt-2">Resend OTP</button>
        </form>

    </div>
</main>