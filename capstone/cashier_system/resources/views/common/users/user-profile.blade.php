@extends('layout.main-master')
@section('content')

<main style="min-height:85vh; padding:2%; background: linear-gradient(to bottom, #f5f7fa, #eef1f5);">
    <div class="container" style="max-width:600px;">

        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="card modern-card p-4 mb-4">

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
                            <h5 class="modal-title" id="updatePasswordModalLabel">Update Password</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <form action="{{ route('new.password.save') }}" method="POST" id="passwordForm">
                                @csrf
                                @method('POST')

                                <div class="mb-4">
                                    <label for="password" class="form-label">New Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password" name="password" required>
                                        <button type="button" class="btn btn-outline-secondary toggle-password" data-target="password" tabindex="-1">
                                            <i class="bi bi-eye-slash"></i>
                                        </button>
                                    </div>
                                </div>

                                <ul id="passwordRequirements" class="text-muted small mb-3">
                                    <li id="length">❌ At least 8 characters</li>
                                    <li id="uppercase">❌ Uppercase letter</li>
                                    <li id="lowercase">❌ Lowercase letter</li>
                                    <li id="number">❌ Number</li>
                                    <li id="special">❌ Special character (!@#$%^&*)</li>
                                </ul>

                                <div class="mb-4">
                                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                                        <button type="button" class="btn btn-outline-secondary toggle-password" data-target="password_confirmation" tabindex="-1">
                                            <i class="bi bi-eye-slash"></i>
                                        </button>
                                    </div>
                                    <small id="matchMessage" class="text-danger d-none">❌ Passwords do not match</small>
                                </div>

                                <button type="submit" class="btn btn-danger w-100" id="submitBtn" disabled>Save</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<style>
    .modern-card {
        background: #ffffff;
        border-radius: 18px;
        border: 1px solid #e7e7e7;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
        transition: box-shadow .2s ease-in-out;
    }

    .modern-card:hover {
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.10);
    }

    .form-label {
        font-weight: 600;
        color: #333;
    }

    .form-control:disabled {
        background: #f4f4f4;
        border-radius: 10px;
    }

    .form-control,
    .form-select {
        border-radius: 10px;
        border: 1px solid #dadada;
    }

    .btn-danger,
    .btn-secondary {
        border-radius: 10px;
        padding: 8px 14px;
        font-weight: 600;
        transition: 0.2s;
    }

    .btn-danger:hover,
    .btn-secondary:hover {
        opacity: 0.9;
    }

    .alert {
        border-radius: 12px;
        padding: 14px 18px;
        font-size: 15px;
    }

    .modal-content {
        border-radius: 16px !important;
        border: 1px solid #e5e5e5;
    }

    .modal-header,
    .modal-footer {
        border: none;
    }

    .input-group .form-control {
        border-radius: 10px 0 0 10px;
    }

    .input-group .btn {
        border-radius: 0 10px 10px 0;
    }
</style>

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

    const password = document.getElementById("password");
    const confirmPassword = document.getElementById("password_confirmation");
    const requirements = {
        length: /.{8,}/,
        uppercase: /[A-Z]/,
        lowercase: /[a-z]/,
        number: /[0-9]/,
        special: /[!@#$%^&*(),.?":{}|<>]/
    };

    const reqElements = {
        length: document.getElementById("length"),
        uppercase: document.getElementById("uppercase"),
        lowercase: document.getElementById("lowercase"),
        number: document.getElementById("number"),
        special: document.getElementById("special")
    };

    const matchMessage = document.getElementById("matchMessage");
    const submitBtn = document.getElementById("submitBtn");

    function validatePassword() {
        let valid = true;

        for (let rule in requirements) {
            if (requirements[rule].test(password.value)) {
                reqElements[rule].textContent = "✅ " + reqElements[rule].textContent.slice(2);
                reqElements[rule].classList.remove("text-danger");
                reqElements[rule].classList.add("text-success");
            } else {
                reqElements[rule].textContent = "❌ " + reqElements[rule].textContent.slice(2);
                reqElements[rule].classList.remove("text-success");
                reqElements[rule].classList.add("text-danger");
                valid = false;
            }
        }

        // Check confirm password
        if (password.value && password.value === confirmPassword.value) {
            matchMessage.classList.add("d-none");
        } else {
            matchMessage.classList.remove("d-none");
            valid = false;
        }

        submitBtn.disabled = !valid;
    }

    password.addEventListener("input", validatePassword);
    confirmPassword.addEventListener("input", validatePassword);
</script>

@endsection