@extends('layout.main-user')

@section('content')
<main style="background-image:url('/bgpup3.jpg'); background-repeat:no-repeat; background-size:cover; min-height: 85vh; padding: 5%;">
    <div class="container" style="width:50%">
        <div class="bg-light" style="padding:5%">
            <div class="card">
                <div class="card-header">
                    <h1 class="card-title">Reset Password</h1>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('forgot.password.form') }}">
                        @csrf
                        <div class="form-group">
                            <label>Enter OTP</label>
                            <input type="text" name="otp" class="form-control" required>
                        </div>
                        {{-- New Password with Eye Toggle --}}
                        <div class="mb-4 position-relative mt-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="new_password" tabindex="-1">
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Confirm New Password with Eye Toggle --}}
                        <div class="mb-4 position-relative mt-3">
                            <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required>
                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="new_password_confirmation" tabindex="-1">
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                            </div>
                        </div>

                        <button class="btn btn-success mt-3">Reset Password</button>
                    </form>

                    {{-- Optional: Resend OTP --}}
                    <form method="POST" action="{{ route('forgot.password.resendOtp') }}" class="mt-3">
                        @csrf
                        <input type="hidden" name="email" value="{{ session('otp_email') }}">
                        <button type="submit" class="btn btn-link p-0">Resend OTP</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

{{-- Eye toggle script --}}
<script>
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            }
        });
    });
</script>

@endsection
