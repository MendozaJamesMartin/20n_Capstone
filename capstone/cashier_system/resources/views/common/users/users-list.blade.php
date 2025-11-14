@extends('layout.main-master')

@section('content')
<main style="min-height:85vh; padding:2%; background: linear-gradient(to bottom, #f5f7fa, #eef1f5);">

    <div class="container" style="width:60%">

        <!-- Header: Title, Search, Registration Button -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <h2 class="mb-0 page-title">Registered Users</h2>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <!-- Search Box -->
                <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="🔍 Search users...">

                <!-- Registration Button -->
                <a href="{{ route('register') }}" class="btn btn-danger btn-sm">
                    <i class="fa-solid fa-user-plus me-1"></i> Registration
                </a>
            </div>
        </div>

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

        <!-- Table Card -->
        <div class="card modern-card p-4 mb-4">
            <div class="table-responsive">
                <table class="table table-striped align-middle text-center mb-0" id="usersTable">
                    <thead class="table-dark">
                        <tr>
                            <th onclick="sortTable(0)">ID</th>
                            <th onclick="sortTable(1)">Email</th>
                            <th onclick="sortTable(2)">Full Name</th>
                            <th onclick="sortTable(3)">Role</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                {{ $user->first_name }}
                                {{ $user->middle_name ? strtoupper(substr(trim($user->middle_name), 0, 1)) . '.' : '' }}
                                {{ $user->last_name }}
                                {{ $user->suffix ? ' ' . $user->suffix : '' }}
                            </td>
                            <td>{{ $user->role }}</td>
                            <td>
                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#updateUserModal{{ $user->id }}">
                                    <i class="fa-solid fa-pen-to-square"></i> Update
                                </button>

                                <button class="btn btn-info btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#permissionsModal{{ $user->id }}">
                                    <i class="fa-solid fa-shield-halved"></i> Permissions
                                </button>
                            </td>
                        </tr>

                        <!-- Update User Modal -->
                        <div class="modal fade" id="updateUserModal{{ $user->id }}" tabindex="-1"
                            aria-labelledby="updateUserModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content p-3">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="updateUserModalLabel">
                                            <i class="fa-solid fa-pen-to-square me-2"></i>Update User
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('users.update.role', $user->id) }}" method="POST">
                                            @csrf
                                            <div class="mb-3">
                                                <label class="form-label">First Name</label>
                                                <input type="text" class="form-control" value="{{ $user->first_name }}" disabled>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Middle Name</label>
                                                <input type="text" class="form-control" value="{{ $user->middle_name }}" disabled>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Last Name</label>
                                                <input type="text" class="form-control" value="{{ $user->last_name }}" disabled>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Suffix</label>
                                                <input type="text" class="form-control" value="{{ $user->suffix }}" disabled>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="text" class="form-control" value="{{ $user->email }}" disabled>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Role</label>
                                                <select name="role" class="form-select" required>
                                                    <option value="Superadmin" {{ $user->role == 'Superadmin' ? 'selected' : '' }}>Superadmin</option>
                                                    <option value="Admin" {{ $user->role == 'Admin' ? 'selected' : '' }}>Admin</option>
                                                </select>
                                            </div>
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fa-solid fa-save me-1"></i> Update User
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Permissions Modal -->
                        <div class="modal fade" id="permissionsModal{{ $user->id ?? '' }}" tabindex="-1"
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
                                        @if($user->role === 'Superadmin')
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

                        @empty
                        <tr>
                            <td colspan="5" class="text-center">No users found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<style>
    .page-title {
        font-family: 'Inter', sans-serif;
        font-weight: 700;
        letter-spacing: -0.4px;
        color: #333;
    }

    #searchInput {
        border-radius: 10px;
        padding: 8px 12px;
        border: 1px solid #d5d5d5;
    }

    #searchInput:focus {
        border-color: #0d6efd !important;
        box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.15) !important;
    }

    .btn-danger.btn-sm {
        border-radius: 10px !important;
        padding: 8px 14px !important;
        font-weight: 600;
    }

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

    .table thead th {
        cursor: pointer;
        user-select: none;
        font-weight: 600;
        letter-spacing: 0.3px;
    }

    .table-striped>tbody>tr:nth-child(odd) {
        background-color: #f9fbfc !important;
    }

    .table-striped>tbody>tr:hover {
        background-color: #eef4ff !important;
    }

    td,
    th {
        vertical-align: middle;
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

    .modal-header {
        border-bottom: none;
    }

    .modal-footer {
        border-top: none;
    }

    .form-control,
    .form-select {
        border-radius: 10px;
        border: 1px solid #dadada;
    }

    .form-control:disabled {
        background: #f4f4f4;
    }

    .btn-warning.btn-sm,
    .btn-info.btn-sm {
        border-radius: 10px !important;
        padding: 6px 12px !important;
        font-weight: 600;
    }
</style>

<!-- Inline Search & Sort Script -->
<script>
    let table = document.getElementById("usersTable");
    let originalRows = Array.from(table.tBodies[0].rows);
    let sortState = {}; // default, asc, desc

    document.getElementById('searchInput').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll("#usersTable tbody tr");
        rows.forEach(row => {
            let text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });

    function sortTable(n) {
        let rows = Array.from(table.tBodies[0].rows);
        let state = sortState[n] || 'default';

        // Reset all header arrows
        Array.from(table.tHead.rows[0].cells).forEach((cell) => {
            cell.innerText = cell.innerText.replace(/ ↑| ↓/g, '');
        });

        if (state === 'default') {
            rows.sort((a, b) => a.cells[n].innerText.localeCompare(b.cells[n].innerText, undefined, {
                numeric: true
            }));
            sortState[n] = 'asc';
            table.tHead.rows[0].cells[n].innerText += ' ↑';
        } else if (state === 'asc') {
            rows.sort((a, b) => b.cells[n].innerText.localeCompare(a.cells[n].innerText, undefined, {
                numeric: true
            }));
            sortState[n] = 'desc';
            table.tHead.rows[0].cells[n].innerText += ' ↓';
        } else {
            rows = [...originalRows];
            sortState[n] = 'default';
        }

        table.tBodies[0].innerHTML = '';
        rows.forEach(row => table.tBodies[0].appendChild(row));
    }
</script>
@endsection