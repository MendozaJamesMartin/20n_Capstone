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

    <div class="container" style="max-width: 420px;">
        <div class="shadow-lg rounded-4 p-4"
             style="backdrop-filter: blur(10px); background: rgba(255,255,255,0.8);">

            <h2 class="fw-bold text-center mb-4" style="color:#8b0000;">Login</h2>

            {{-- Alerts --}}
            @if(Session::has('success'))
                <div class="alert alert-success rounded-3">{{ Session::get('success') }}</div>
            @elseif(Session::has('error'))
                <div class="alert alert-danger rounded-3">{{ Session::get('error') }}</div>
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

            {{-- Login Form --}}
            <form method="POST" action="{{ route('login.submit') }}">
                @csrf

                {{-- Email --}}
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Username / Email</label>
                    <input type="text"
                           id="email"
                           name="email"
                           class="form-control rounded-3 p-3 shadow-sm"
                           placeholder="Enter your email or username"
                           required>
                </div>

                {{-- Password --}}
                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">Password</label>
                    <div class="input-group">
                        <input type="password"
                               id="password"
                               name="password"
                               class="form-control rounded-start-3 p-3 shadow-sm">
                        <button type="button"
                                class="btn btn-outline-secondary rounded-end-3 toggle-password px-3"
                                tabindex="-1">
                            <i class="bi bi-eye-slash" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                </div>

                {{-- Remember Me --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <input type="checkbox" id="remember" name="remember" class="form-check-input">
                        <label for="remember" class="ms-1">Remember me</label>
                    </div>
                    <a href="{{ route('forgot.password.email') }}" class="text-decoration-none"
                       style="color:#8b0000; font-weight:600;">
                       Forgot Password?
                    </a>
                </div>

                {{-- Login Button --}}
                <button type="submit"
                        class="btn w-100 text-light fw-semibold rounded-3 py-2 shadow-sm"
                        style="background:#8b0000;">
                    Login
                </button>

            </form>

        </div>
    </div>

</main>

{{-- Password Toggle Script --}}
<script>
    document.querySelector('.toggle-password').addEventListener('click', function() {
        const password = document.getElementById('password');
        const icon = document.getElementById('togglePasswordIcon');

        const isHidden = password.type === 'password';
        password.type = isHidden ? 'text' : 'password';

        icon.classList.toggle('bi-eye', isHidden);
        icon.classList.toggle('bi-eye-slash', !isHidden);
    });
</script>

@endsection
