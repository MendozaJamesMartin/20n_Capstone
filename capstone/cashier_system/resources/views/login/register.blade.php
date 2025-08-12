@extends('layout.main-master')
@section('content')

<main style="background-image:url('/bgpup3.jpg'); background-repeat:no-repeat; background-size:cover; min-height: 85vh; padding: 5%;">
    <div class="container" style="width:50%">
        <div class="bg-light" style="padding:5%">
            <div class="card">
                <div class="card-header">
                    <h1 class="card-title">Register</h1>
                </div>
                <div class="card-body">
                    @if(Session::has('success'))
                        <div class="alert alert-success" role="alert">
                            {{ Session::get('success') }}
                        </div>
                    @elseif (Session::has('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ Session::get('error') }}
                        </div>
                    @endif
                    <form action="{{ route('register') }}" method="POST">
                        @csrf
                        <label for="full_name" class="form-label">Full Name:</label>
                        <div class="mb-3 d-flex gap-2">
                            <input type="text" class="form-control" id="first_name" name="first_name" placeholder="First Name">
                            <input type="text" class="form-control" id="middle_name" name="middle_name" placeholder="Middle Name">
                            <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Last Name">
                            <input type="text" class="form-control" id="suffix" name="suffix" placeholder="Suffix">
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" name="email" class="form-control" id="email" placeholder="name@example.com" required>
                        </div>

                        {{-- Password with toggle --}}
                        <div class="mb-4 position-relative">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="password" tabindex="-1">
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                            </div>
                            <small class="text-muted">
                                Password must be at least 8 characters and include uppercase, lowercase, number, and special character.
                            </small>
                        </div>

                        {{-- Confirm Password with toggle --}}
                        <div class="mb-4 position-relative">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="password_confirmation" tabindex="-1">
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-grid">
                                <button class="btn btn-primary">Register</button>
                            </div>
                        </div>
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
