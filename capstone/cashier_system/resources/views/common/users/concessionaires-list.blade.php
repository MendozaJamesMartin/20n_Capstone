@extends('layout.main-master')

@section('content')
<main style="background-image: url('/bgpup3.jpg'); background-repeat: no-repeat; background-size: cover; min-height: 85vh; padding: 2%;">
    <div class="container" style="width:50%">

        <!-- Header: Title, Search, and Add Button -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <h2 class="mb-0">Concessionaires List</h2>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <!-- Search Input -->
                <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="🔍 Search...">

                <!-- Add New Button -->
                <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#addConcessionaireModal">
                    <i class="fa-solid fa-plus me-1"></i> New
                </button>
            </div>
        </div>

        <!-- Responsive Table -->
        <div class="card shadow-sm p-3 mb-4 bg-light rounded">
            <div class="table-responsive">
                <table class="table table-striped align-middle text-center mb-0" id="feesTable">
                    <thead class="table-dark">
                        <tr>
                            <th onclick="sortTable(0)" style="cursor:pointer">Concessionaire ID</th>
                            <th onclick="sortTable(1)" style="cursor:pointer">Name</th>
                            <th onclick="sortTable(2)" style="cursor:pointer">Email</th>
                            <th onclick="sortTable(3)" style="cursor:pointer">Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($concessionaires as $concessionaire)
                        <tr>
                            <td>{{ $concessionaire->id }}</td>
                            <td>{{ $concessionaire->name }}</td>
                            <td>{{ $concessionaire->contact }}</td>
                            <td>
                                @if($concessionaire->status === 'Active')
                                    <span class="badge bg-success">{{ $concessionaire->status }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ $concessionaire->status }}</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#updateConcessionaireModal{{ $concessionaire->id }}">
                                    <i class="fa-solid fa-pen-to-square"></i> Update
                                </button>
                            </td>
                        </tr>

                        <!-- Update Modal -->
                        <div class="modal fade" id="updateConcessionaireModal{{ $concessionaire->id }}" tabindex="-1"
                            aria-labelledby="updateConcessionaireModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content p-3">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            <i class="fa-solid fa-pen-to-square me-2"></i>Update Concessionaire
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('concessionaires.update', $concessionaire->id) }}" method="POST">
                                            @csrf
                                            @method('POST')
                                            <div class="mb-3">
                                                <label class="form-label">Name</label>
                                                <input type="text" name="name" class="form-control" value="{{ $concessionaire->name }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="text" name="contact" class="form-control" value="{{ $concessionaire->contact }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Status</label>
                                                <select class="form-select" name="status" required>
                                                    <option value="Active" {{ $concessionaire->status == 'Active' ? 'selected' : '' }}>Active</option>
                                                    <option value="Inactive" {{ $concessionaire->status == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                                </select>
                                            </div>
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fa-solid fa-save me-1"></i> Save
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">No concessionaires found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<!-- Add Concessionaire Modal -->
<div class="modal fade" id="addConcessionaireModal" tabindex="-1" aria-labelledby="addConcessionaireModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-3">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-plus me-2"></i>Add New Concessionaire
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('concessionaires.add') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Concessionaire Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="text" name="contact" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-plus me-1"></i> Add Concessionaire
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Saved Script for Sorting & Searching -->
<script>
    let table = document.getElementById("feesTable");
    let originalRows = Array.from(table.tBodies[0].rows);
    let sortState = {}; // default, asc, desc

    document.getElementById('searchInput').addEventListener('keyup', function () {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll("#feesTable tbody tr");
        rows.forEach(row => {
            let text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });

    function sortTable(n) {
        let rows = Array.from(table.tBodies[0].rows);
        let state = sortState[n] || 'default';

        // Reset all header arrows
        Array.from(table.tHead.rows[0].cells).forEach((cell, idx) => {
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
