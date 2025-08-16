@extends('layout.main-master')

@section('content')
<main style="background-image: url('/bgpup3.jpg'); background-repeat: no-repeat; background-size: cover; min-height: 85vh; padding: 2%;">
    <div class="container" style="width:50%">

        <!-- Header: Title, Search, Registration Button -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <h2 class="mb-0">Registered Users</h2>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <!-- Search Box -->
                <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="🔍 Search users...">

                <!-- Registration Button -->
                <a href="{{ route('register') }}" class="btn btn-danger btn-sm">
                    <i class="fa-solid fa-user-plus me-1"></i> Registration
                </a>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card shadow-sm p-3 mb-4 bg-light rounded">
            <div class="table-responsive">
                <table class="table table-striped align-middle text-center mb-0" id="usersTable">
                    <thead class="table-dark">
                        <tr>
                            <th onclick="sortTable(0)">ID</th>
                            <th onclick="sortTable(1)">Email</th>
                            <th>Password</th>
                            <th onclick="sortTable(3)">Role</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->email }}</td>
                            <td>********</td>
                            <td>{{ $user->role }}</td>
                            <td>
                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#updateUserModal{{ $user->id }}">
                                    <i class="fa-solid fa-pen-to-square"></i> Update
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

<!-- Inline Search & Sort Script -->
<script>
    let table = document.getElementById("usersTable");
    let originalRows = Array.from(table.tBodies[0].rows);
    let sortState = {}; // default, asc, desc

    document.getElementById('searchInput').addEventListener('keyup', function () {
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
            rows.sort((a, b) => a.cells[n].innerText.localeCompare(b.cells[n].innerText, undefined, {numeric: true}));
            sortState[n] = 'asc';
            table.tHead.rows[0].cells[n].innerText += ' ↑';
        } else if (state === 'asc') {
            rows.sort((a, b) => b.cells[n].innerText.localeCompare(a.cells[n].innerText, undefined, {numeric: true}));
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
