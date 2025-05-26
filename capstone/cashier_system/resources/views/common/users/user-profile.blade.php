@extends('layout.main-master')
@section('content')

<div style="background-image: url('/bgpup4.jpg'); background-repeat: no-repeat; background-size: cover; min-height: 85vh; padding: 5%;">
    <main class="container" style="width:50%;">

        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
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

                                <label for="last_name" class="form-label">New Password: </label>
                                <div class="mb-3 d-flex gap-2">
                                    <input type="password" name="password" class="form-control" id="password" required>
                                </div>
                                
                                <label for="last_name" class="form-label">Confirm Password: </label>
                                <div class="mb-3 d-flex gap-2">
                                    <input type="password" name="password" class="form-control" id="password" required>
                                </div>

                                <button type="submit" class="btn btn-danger">Save</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

@endsection