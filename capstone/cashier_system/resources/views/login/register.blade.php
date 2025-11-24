@extends('layout.main-master')
@section('content')

<main style="min-height: 85vh; padding: 5%;
    background: linear-gradient(135deg, #eef2f7, #f8f9fc);">

    <div class="container" style="max-width:600px;">
        <div class="shadow-lg rounded-4 p-4"
             style="backdrop-filter: blur(10px); background: rgba(255,255,255,0.85);">

            <h2 class="fw-bold text-center mb-4" style="color:#8b0000;">
                Create Your Account
            </h2>

            {{-- FLASH MESSAGES --}}
            @if(Session::has('success'))
                <div class="alert alert-success rounded-3 text-center">
                    {{ Session::get('success') }}
                </div>
            @elseif(Session::has('error'))
                <div class="alert alert-danger rounded-3 text-center">
                    {{ Session::get('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger rounded-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- REGISTRATION FORM --}}
            <form action="{{ route('register') }}" method="POST">
                @csrf

                {{-- NAME FIELDS --}}
                <label class="form-label fw-semibold">Full Name</label>
                <div class="d-flex gap-2 mb-3">
                    <input type="text" class="form-control p-3" name="first_name" placeholder="First" value="{{ old('first_name') }}">
                    <input type="text" class="form-control p-3" name="middle_name" placeholder="Middle" value="{{ old('middle_name') }}">
                </div>

                <div class="d-flex gap-2 mb-3">
                    <input type="text" class="form-control p-3" name="last_name" placeholder="Last" value="{{ old('last_name') }}">
                    <input type="text" class="form-control p-3" name="suffix" placeholder="Suffix" value="{{ old('suffix') }}">
                </div>

                {{-- EMAIL --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email Address</label>
                    <input type="email" class="form-control p-3" name="email" placeholder="name@example.com" required>
                </div>

                {{-- PASSWORD --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control p-3" id="password" name="password" required>
                        <button type="button" class="btn btn-outline-secondary toggle-password" data-target="password">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>

                    <small class="text-muted">Your password must meet all rules:</small>

                    <ul class="list-unstyled small mt-2" id="passwordRules">
                        <li id="rule-length" class="text-danger">❌ At least 8 characters</li>
                        <li id="rule-upper" class="text-danger">❌ At least 1 uppercase letter</li>
                        <li id="rule-lower" class="text-danger">❌ At least 1 lowercase letter</li>
                        <li id="rule-number" class="text-danger">❌ At least 1 number</li>
                        <li id="rule-special" class="text-danger">❌ At least 1 special character</li>
                    </ul>
                </div>

                {{-- CONFIRM PASSWORD --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">Confirm Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control p-3" id="password_confirmation" name="password_confirmation" required>
                        <button type="button" class="btn btn-outline-secondary toggle-password" data-target="password_confirmation">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                    <small id="confirmMessage" class="text-danger d-none">❌ Passwords do not match</small>
                </div>

                {{-- REGISTER BUTTON --}}
                <button id="registerBtn"
                        type="submit"
                        class="btn w-100 text-light fw-semibold rounded-3 py-2 shadow-sm"
                        style="background:#8b0000;"
                        disabled>
                    Register
                </button>

            </form>
        </div>
    </div>

</main>

<style>
    .form-control {
        border-radius: 12px;
        border: 1px solid #ccc;
    }
    .form-control:focus {
        border-color: #8b0000;
        box-shadow: 0 0 0 0.15rem rgba(139,0,0,0.25);
    }
    .input-group .btn {
        border-radius: 0 12px 12px 0;
    }
    .alert {
        border-radius: 12px;
    }
</style>

{{-- PASSWORD TOGGLE --}}
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

{{-- PASSWORD LIVE VALIDATION --}}
<script>
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirmation');
    const registerBtn = document.getElementById('registerBtn');
    const confirmMessage = document.getElementById('confirmMessage');

    const rules = {
        length: document.getElementById('rule-length'),
        upper: document.getElementById('rule-upper'),
        lower: document.getElementById('rule-lower'),
        number: document.getElementById('rule-number'),
        special: document.getElementById('rule-special'),
    };

    function validatePassword() {
        const value = passwordInput.value;
        let valid = true;

        if (value.length >= 8) { rules.length.textContent = "✅ At least 8 characters"; rules.length.classList.replace('text-danger','text-success'); }
        else { rules.length.textContent = "❌ At least 8 characters"; rules.length.classList.replace('text-success','text-danger'); valid = false; }

        if (/[A-Z]/.test(value)) { rules.upper.textContent = "✅ At least 1 uppercase letter"; rules.upper.classList.replace('text-danger','text-success'); }
        else { rules.upper.textContent = "❌ At least 1 uppercase letter"; rules.upper.classList.replace('text-success','text-danger'); valid = false; }

        if (/[a-z]/.test(value)) { rules.lower.textContent = "✅ At least 1 lowercase letter"; rules.lower.classList.replace('text-danger','text-success'); }
        else { rules.lower.textContent = "❌ At least 1 lowercase letter"; rules.lower.classList.replace('text-success','text-danger'); valid = false; }

        if (/[0-9]/.test(value)) { rules.number.textContent = "✅ At least 1 number"; rules.number.classList.replace('text-danger','text-success'); }
        else { rules.number.textContent = "❌ At least 1 number"; rules.number.classList.replace('text-success','text-danger'); valid = false; }

        if (/[^A-Za-z0-9]/.test(value)) { rules.special.textContent = "✅ At least 1 special character"; rules.special.classList.replace('text-danger','text-success'); }
        else { rules.special.textContent = "❌ At least 1 special character"; rules.special.classList.replace('text-success','text-danger'); valid = false; }

        if (value && confirmInput.value && value !== confirmInput.value) {
            confirmMessage.classList.remove('d-none');
            valid = false;
        } else {
            confirmMessage.classList.add('d-none');
        }

        registerBtn.disabled = !valid;
    }

    passwordInput.addEventListener('input', validatePassword);
    confirmInput.addEventListener('input', validatePassword);
</script>

@endsection
