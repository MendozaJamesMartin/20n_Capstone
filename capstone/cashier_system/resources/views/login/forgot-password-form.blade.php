@extends('layout.main-user')

@section('content')

<main style="
    background-image:url('/bgpup3.jpg');
    background-repeat:no-repeat;
    background-size:cover;
    background-position:top;
    min-height: 85vh;
    padding: 5%;
    display:flex;
    justify-content:center;
    align-items:center;
">

    <div class="container" style="max-width: 450px;">
        <div class="shadow-lg rounded-4 p-4"
             style="backdrop-filter: blur(10px); background: rgba(255,255,255,0.85);">

            <h2 class="fw-bold text-center mb-4" style="color:#8b0000;">Reset Password</h2>

            {{-- Success --}}
            @if(session('success'))
                <div class="alert alert-success rounded-3">{{ session('success') }}</div>
            @endif

            {{-- Errors --}}
            @if($errors->any())
                <div class="alert alert-danger rounded-3">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- RESET PASSWORD FORM --}}
            <form method="POST" action="{{ route('forgot.password.form') }}">
                @csrf

                {{-- OTP 6 BOXES --}}
                <label class="form-label fw-semibold">Enter OTP</label>
                <div class="d-flex gap-2 justify-content-center mb-4">
                    @for($i=1; $i<=6; $i++)
                        <input type="text"
                               maxlength="1"
                               class="otp-box form-control text-center fw-bold"
                               style="width:45px; height:55px; font-size:1.5rem; border-radius:12px;"
                               inputmode="numeric"
                               autocomplete="one-time-code"
                               data-index="{{ $i }}">
                    @endfor
                </div>

                <input type="hidden" name="otp" id="otp_full">

                {{-- NEW PASSWORD --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">New Password</label>
                    <div class="input-group">
                        <input type="password"
                               class="form-control rounded-start-3 p-3 shadow-sm"
                               id="new_password"
                               name="new_password"
                               required>
                        <button type="button"
                                class="btn btn-outline-secondary toggle-password rounded-end-3"
                                data-target="new_password">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                </div>

                {{-- CONFIRM PASSWORD --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Confirm New Password</label>
                    <div class="input-group">
                        <input type="password"
                               class="form-control rounded-start-3 p-3 shadow-sm"
                               id="new_password_confirmation"
                               name="new_password_confirmation"
                               required>
                        <button type="button"
                                class="btn btn-outline-secondary toggle-password rounded-end-3"
                                data-target="new_password_confirmation">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <button class="btn w-100 text-light fw-semibold rounded-3 py-2 shadow-sm mt-3"
                        style="background:#8b0000;">
                    Reset Password
                </button>
            </form>

            {{-- RESEND OTP --}}
            <form method="POST" action="{{ route('forgot.password.resendOtp') }}" class="text-center mt-3">
                @csrf
                <input type="hidden" name="email" value="{{ session('otp_email') }}">
                <button class="btn btn-link fw-semibold" style="color:#8b0000;">Resend OTP</button>
            </form>

        </div>
    </div>

</main>

{{-- OTP SCRIPT --}}
<script>
document.querySelectorAll('.otp-box').forEach((box, index, boxes) => {
    box.addEventListener('input', () => {
        box.value = box.value.replace(/\D/g, '');

        // Auto move forward
        if (box.value && index < boxes.length - 1) {
            boxes[index + 1].focus();
        }

        // Update hidden input
        document.getElementById('otp_full').value =
            Array.from(boxes).map(b => b.value).join('');
    });

    box.addEventListener('keydown', (e) => {
        // Backspace moves back
        if (e.key === 'Backspace' && !box.value && index > 0) {
            boxes[index - 1].focus();
        }
    });
});
</script>

{{-- Toggle Password Script --}}
<script>
document.querySelectorAll('.toggle-password').forEach(button => {
    button.addEventListener('click', function () {
        const targetId = this.getAttribute('data-target');
        const input = document.getElementById(targetId);
        const icon = this.querySelector('i');

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        } else {
            input.type = 'password';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        }
    });
});
</script>

@endsection
