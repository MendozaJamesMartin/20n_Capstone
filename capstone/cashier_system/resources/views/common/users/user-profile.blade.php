@extends('layout.main-master')
@section('content')

<main style="background-image: url('/bgpup4.jpg'); background-repeat: no-repeat; background-size: cover; min-height: 85vh; padding: 5%;">
    <div class="container" style="width:50%;">

        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
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
                    <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#permissionsModal">View Permissions</button>
                </div>
            </div>

            <!-- Permissions Modal -->
            <div class="modal fade" id="permissionsModal" tabindex="-1"
                aria-labelledby="permissionsModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content p-3">
                        <div class="modal-header">
                            <h5 class="modal-title" id="permissionsModalLabel">
                                <i class="fa-solid fa-lock me-2"></i>Permissions for {{ $user->role ?? $userProfile->role }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <!-- Shared Permissions -->
                            <h6 class="fw-bold text-danger mb-3">✅ Shared (Admin + Superadmin)</h6>
                            <div class="accordion mb-4" id="sharedPermissions">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePayments">
                                            Customer Payments
                                        </button>
                                    </h2>
                                    <div id="collapsePayments" class="accordion-collapse collapse" data-bs-parent="#sharedPermissions">
                                        <div class="accordion-body">
                                            <ul class="mb-0">
                                                <li>View pending payments</li>
                                                <li>Edit & approve pending payments</li>
                                                <li>Delete unpaid payments</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTransactions">
                                            Transactions
                                        </button>
                                    </h2>
                                    <div id="collapseTransactions" class="accordion-collapse collapse" data-bs-parent="#sharedPermissions">
                                        <div class="accordion-body">
                                            <ul class="mb-0">
                                                <li>View transaction & receipt details</li>
                                                <li>Finalize transactions</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReceipts">
                                            Receipts
                                        </button>
                                    </h2>
                                    <div id="collapseReceipts" class="accordion-collapse collapse" data-bs-parent="#sharedPermissions">
                                        <div class="accordion-body">
                                            <ul class="mb-0">
                                                <li>View & print receipt PDFs</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBills">
                                            Concessionaire Bills
                                        </button>
                                    </h2>
                                    <div id="collapseBills" class="accordion-collapse collapse" data-bs-parent="#sharedPermissions">
                                        <div class="accordion-body">
                                            <ul class="mb-0">
                                                <li>View bills</li>
                                                <li>Create bills</li>
                                                <li>Process payments</li>
                                                <li>Print billing statements</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOwnAccount">
                                            User (Own Account Only)
                                        </button>
                                    </h2>
                                    <div id="collapseOwnAccount" class="accordion-collapse collapse" data-bs-parent="#sharedPermissions">
                                        <div class="accordion-body">
                                            <ul class="mb-0">
                                                <li>View profile</li>
                                                <li>Update details</li>
                                                <li>Change password</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Superadmin Permissions -->
                            @if($userProfile->role === 'Superadmin')
                            <h6 class="fw-bold text-danger mb-3">🔒 Superadmin Only</h6>
                            <div class="accordion" id="superadminPermissions">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed text-danger" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFees">
                                            Fees Management
                                        </button>
                                    </h2>
                                    <div id="collapseFees" class="accordion-collapse collapse" data-bs-parent="#superadminPermissions">
                                        <div class="accordion-body">
                                            <ul class="mb-0">
                                                <li>View</li>
                                                <li>Add</li>
                                                <li>Update</li>
                                                <li>Delete</li>
                                                <li>Restore</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed text-danger" type="button" data-bs-toggle="collapse" data-bs-target="#collapseUsers">
                                            User Management
                                        </button>
                                    </h2>
                                    <div id="collapseUsers" class="accordion-collapse collapse" data-bs-parent="#superadminPermissions">
                                        <div class="accordion-body">
                                            <ul class="mb-0">
                                                <li>View all users</li>
                                                <li>Change roles</li>
                                                <li>Register new users</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed text-danger" type="button" data-bs-toggle="collapse" data-bs-target="#collapseConcessionaires">
                                            Concessionaires Management
                                        </button>
                                    </h2>
                                    <div id="collapseConcessionaires" class="accordion-collapse collapse" data-bs-parent="#superadminPermissions">
                                        <div class="accordion-body">
                                            <ul class="mb-0">
                                                <li>View</li>
                                                <li>Add</li>
                                                <li>Update</li>
                                                <li>Delete</li>
                                                <li>Restore</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed text-danger" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReceiptsBatch">
                                            Receipts Management
                                        </button>
                                    </h2>
                                    <div id="collapseReceiptsBatch" class="accordion-collapse collapse" data-bs-parent="#superadminPermissions">
                                        <div class="accordion-body">
                                            <ul class="mb-0">
                                                <li>View batches</li>
                                                <li>Load new batch</li>
                                                <li>Edit batch</li>
                                                <li>Delete batch</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed text-danger" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAudit">
                                            Audit Logs
                                        </button>
                                    </h2>
                                    <div id="collapseAudit" class="accordion-collapse collapse" data-bs-parent="#superadminPermissions">
                                        <div class="accordion-body">
                                            <ul class="mb-0">
                                                <li>View all logs</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed text-danger" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBackup">
                                            Backup & Restore
                                        </button>
                                    </h2>
                                    <div id="collapseBackup" class="accordion-collapse collapse" data-bs-parent="#superadminPermissions">
                                        <div class="accordion-body">
                                            <ul class="mb-0">
                                                <li>Export</li>
                                                <li>Restore</li>
                                                <li>Delete</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed text-danger" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReports">
                                            Transactions Monthly Report
                                        </button>
                                    </h2>
                                    <div id="collapseReports" class="accordion-collapse collapse" data-bs-parent="#superadminPermissions">
                                        <div class="accordion-body">
                                            <ul class="mb-0">
                                                <li>View analytics</li>
                                                <li>Generate report</li>
                                                <li>Export report</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Update User Profile Modal -->
            <div class="modal fade" id="updateUserProfileModal" tabindex="-1" aria-labelledby="updateUserProfileModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="updateUserProfileModalLabel">Update User Details</h5> <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">

                            <form action="{{ route('user.update') }}" method="POST"> @csrf @method('POST')

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
                            <h5 class="modal-title" id="updatePasswordModalLabel">Update User Details</h5> <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <form action="{{ route('new.password.save') }}" method="POST"> @csrf @method('POST')
                                <div class="mb-3"> {{-- Password with toggle --}}
                                    <div class="mb-4 position-relative">
                                        <label for="password" class="form-label">Password</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="password" name="password" required>
                                            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="password" tabindex="-1">
                                                <i class="bi bi-eye-slash"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted"> Password must be at least 8 characters and include uppercase, lowercase, number, and special character. </small>
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

{{-- Eye toggle script remains --}}
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