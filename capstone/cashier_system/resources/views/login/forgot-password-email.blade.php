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
             style="backdrop-filter: blur(10px); background: rgba(255,255,255,0.85);">

            <h2 class="fw-bold text-center mb-4" style="color:#8b0000;">Forgot Password</h2>

            {{-- Success Alert --}}
            @if(session('success'))
                <div class="alert alert-success rounded-3">{{ session('success') }}</div>
            @endif

            {{-- Error Alerts --}}
            @if($errors->any())
                <div class="alert alert-danger rounded-3">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Forgot Password Form --}}
            <form method="POST" action="{{ route('forgot.password.email') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold">Email Address</label>
                    <input type="email"
                           name="email"
                           class="form-control rounded-3 p-3 shadow-sm"
                           placeholder="Enter your email"
                           value="{{ old('email') }}"
                           required>
                </div>

                <button class="btn w-100 text-light fw-semibold rounded-3 py-2 shadow-sm"
                        style="background:#8b0000;">
                    Send OTP
                </button>
            </form>

            {{-- Back to login --}}
            <div class="text-center mt-3">
                <a href="{{ route('login') }}" 
                   class="text-decoration-none"
                   style="color:#8b0000; font-weight:600;">
                    Back to Login
                </a>
            </div>

        </div>
    </div>

</main>

@endsection
