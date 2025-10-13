@extends('layout.main-master')

@section('content')
<main style="background-image: url('/bgpup3.jpg'); background-repeat: no-repeat; background-size: cover; min-height: 85vh; padding: 2%;">
    <div class="container" style="width:60%">

        @if(session('success'))
        <div class="alert alert-success mt-3" style="white-space: pre-line;">{{ session('success') }}</div>
        @elseif(session('error'))
        <div class="alert alert-danger mt-3">{{ session('error') }}</div>
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

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Concessionaires Management</h3>
            @if($status === 'active')
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addConcessionaireModal">
                    <i class="fa-solid fa-plus me-1"></i> New
                </button>
            @endif
        </div>

        <!-- Toggle Buttons -->
        <div class="mb-3">
            <a href="{{ route('concessionaires.list', ['status' => 'active']) }}"
               class="btn {{ $status === 'active' ? 'btn-dark' : 'btn-outline-dark' }}">
               Active Concessionaires
            </a>
            <a href="{{ route('concessionaires.list', ['status' => 'deleted']) }}"
               class="btn {{ $status === 'deleted' ? 'btn-dark' : 'btn-outline-dark' }}">
               Inactive Concessionaires
            </a>
        </div>

        <!-- Search -->
        <div class="mb-3">
            <input type="text" id="searchInput" class="form-control" placeholder="🔍 Search...">
        </div>

        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <!-- Table -->
        <div class="card shadow-sm p-3 mb-4 bg-light rounded">
            <div class="table-responsive">
                <table class="table table-striped align-middle text-center mb-0" id="feesTable">
                    <thead class="table-dark">
                        <tr>
                            <th onclick="sortTable(0)" style="cursor:pointer">ID</th>
                            <th onclick="sortTable(1)" style="cursor:pointer">Name</th>
                            <th onclick="sortTable(2)" style="cursor:pointer">Email</th>
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
                                @if($status === 'active')
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#updateConcessionaireModal{{ $concessionaire->id }}">
                                        <i class="fa-solid fa-pen-to-square"></i> Update
                                    </button>

                                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#deleteConcessionaireModal{{ $concessionaire->id }}">
                                        <i class="fa-solid fa-trash"></i> Delete
                                    </button>
                                @else
                                    <button class="btn btn-success btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#restoreConcessionaireModal{{ $concessionaire->id }}">
                                        <i class="fa-solid fa-rotate-left"></i> Restore
                                    </button>
                                @endif
                            </td>
                        </tr>

                        <!-- Update Modal -->
                        <div class="modal fade" id="updateConcessionaireModal{{ $concessionaire->id }}" tabindex="-1">
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
                                            @csrf @method('POST')
                                            <div class="mb-3">
                                                <label class="form-label">Name</label>
                                                <input type="text" name="name" class="form-control" value="{{ $concessionaire->name }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="text" name="contact" class="form-control" value="{{ $concessionaire->contact }}" required>
                                            </div>
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fa-solid fa-save me-1"></i> Save
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Delete Modal -->
                        <div class="modal fade" id="deleteConcessionaireModal{{ $concessionaire->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <form action="{{ route('concessionaires.delete', $concessionaire->id) }}" method="POST">
                                    @csrf @method('GET')
                                    <div class="modal-content">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title">Delete Concessionaire</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            Are you sure you want to delete "{{ $concessionaire->name }}"?
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger">Delete</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Restore Modal -->
                        <div class="modal fade" id="restoreConcessionaireModal{{ $concessionaire->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <form action="{{ route('concessionaires.restore', $concessionaire->id) }}" method="POST">
                                    @csrf @method('GET')
                                    <div class="modal-content">
                                        <div class="modal-header bg-success text-white">
                                            <h5 class="modal-title">Restore Concessionaire</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            Are you sure you want to restore "{{ $concessionaire->name }}"?
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-success">Restore</button>
                                        </div>
                                    </div>
                                </form>
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
<div class="modal fade" id="addConcessionaireModal" tabindex="-1">
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

<!-- Sorting & Searching Script -->
<script>
    let table = document.getElementById("feesTable");
    let originalRows = Array.from(table.tBodies[0].rows);
    let sortState = {};

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

        Array.from(table.tHead.rows[0].cells).forEach(cell => {
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
