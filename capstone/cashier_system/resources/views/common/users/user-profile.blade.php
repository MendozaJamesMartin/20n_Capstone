@extends('layout.main-master')
@section('content')

<main style="background-image: url('/bgpup4.jpg'); background-repeat: no-repeat; background-size: cover; min-height: 85vh; padding: 5%;">
    <div class="container" style="width:50%;">

        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @elseif(session('error'))
        <div class="alert alert-success">{{ session('error') }}</div>
        @endif

        <div class="bg-light border" style="padding:2%">

            <h3><strong>User Profile</strong></h3>

            <div class="mb-3">

                <label for="first_name" class="form-label">First Name</label>
                <div class="mb-3 d-flex gap-2">
                    <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $userProfile->first_name) }}" disabled>
                </div>

                <label for="middle_name" class="form-label">Middle Name</label>
                <div class="mb-3 d-flex gap-2">
                    <input type="text" name="middle_name" class="form-control" value="{{ old('middle_name', $userProfile->middle_name) }}" disabled>
                </div>

                <label for="last_name" class="form-label">Last Name</label>
                <div class="mb-3 d-flex gap-2">
                    <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $userProfile->last_name) }}" disabled>
                </div>

                <label for="suffix" class="form-label">Suffix</label>
                <div class="mb-3 d-flex gap-2">
                    <input type="text" name="suffix" class="form-control" value="{{ old('suffix', $userProfile->suffix) }}" disabled>
                </div>

                <label for="email" class="form-label">Email</label>
                <div class="mb-3 d-flex gap-2">
                    <input type="text" name="email" class="form-control" value="{{ $userProfile->email }}" disabled>
                </div>

                <label for="email" class="form-label">Password</label>
                <div class="mb-3 d-flex gap-2">
                    <input type="text" name="email" class="form-control" value=" ******** " disabled>
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#updatePasswordModal"><i class="fas fa-edit"></i></button>
                </div>

                <label for="role" class="form-label">Role</label>
                <div class="mb-3 d-flex gap-2">
                    <input type="text" name="role" class="form-control" value="{{ $userProfile->role }}" disabled>
                </div>

                <div class="mb-3 d-flex gap-2">
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#updateUserProfileModal">Update User</button>
                </div>
            </div>

            <!-- Update User Profile Modal -->
            <div class="modal fade" id="updateUserProfileModal" tabindex="-1" aria-labelledby="updateUserProfileModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="updateUserProfileModalLabel">Update User Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="{{ route('user.update') }}" method="POST">
                                @csrf
                                @method('POST')

                                <label for="first_name" class="form-label">First Name</label>
                                <div class="mb-3 d-flex gap-2">
                                    <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $userProfile->first_name) }}">
                                </div>

                                <label for="middle_name" class="form-label">Middle Name</label>
                                <div class="mb-3 d-flex gap-2">
                                    <input type="text" name="middle_name" class="form-control" value="{{ old('middle_name', $userProfile->middle_name) }}">
                                </div>

                                <label for="last_name" class="form-label">Last Name</label>
                                <div class="mb-3 d-flex gap-2">
                                    <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $userProfile->last_name) }}">
                                </div>

                                <label for="suffix" class="form-label">Suffix</label>
                                <div class="mb-3 d-flex gap-2">
                                    <input type="text" name="suffix" class="form-control" value="{{ old('suffix', $userProfile->suffix) }}">
                                </div>

                                <button type="submit" class="btn btn-danger">Save</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Change Password Modal -->
            <div class="modal fade" id="updatePasswordModal" tabindex="-1" aria-labelledby="updatePasswordModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="updatePasswordModalLabel">Update User Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="{{ route('new.password.save') }}" method="POST">
                                @csrf
                                @method('POST')

                                <div class="mb-3">
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
                                </div>

                                <button type="submit" class="btn btn-danger">Save</button>
                            </form>
                        </div>
                    </div>
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