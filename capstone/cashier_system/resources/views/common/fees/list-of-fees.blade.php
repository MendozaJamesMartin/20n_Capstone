@extends('layout.main-master')

@section('content')

<main style="min-height: 85vh; padding: 3%; 
    background: linear-gradient(135deg, #eef2f7, #f8f9fc);">

    <div class="container mt-4" style="width: 65%;">
        
        <!-- Alerts -->
        @if(session('success'))
        <div class="alert alert-success mt-3 shadow-sm" style="white-space: pre-line;">{{ session('success') }}</div>
        @elseif(session('error'))
        <div class="alert alert-danger mt-3 shadow-sm">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
        <div class="alert alert-danger shadow-sm">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Outer Card -->
        <div class="bg-white rounded-4 shadow p-4" 
            style="border: 1px solid #e5e7eb; animation: fadeIn 0.4s ease;">

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                <h3 class="fw-bold mb-0">Fees Management</h3>

                @if($status === 'active')
                <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addFeeModal">
                    <i class="bi bi-plus-circle"></i> Add Fee
                </button>
                @endif
            </div>

            <!-- Toggle Buttons -->
            <div class="mb-3">
                <a href="{{ route('fees.list', ['status' => 'active']) }}"
                    class="btn {{ $status === 'active' ? 'btn-dark' : 'btn-outline-dark' }} me-2 px-4 rounded-pill shadow-sm">
                    Active Fees
                </a>
                <a href="{{ route('fees.list', ['status' => 'deleted']) }}"
                    class="btn {{ $status === 'deleted' ? 'btn-dark' : 'btn-outline-dark' }} px-4 rounded-pill shadow-sm">
                    Inactive Fees
                </a>
            </div>

            <!-- Search -->
            <div class="mb-4">
                <input type="text" id="searchInput" class="form-control shadow-sm"
                    placeholder="Search fees..."
                    style="border-radius: 15px;">
            </div>

            <!-- Table Wrapper -->
            <div class="table-responsive shadow-sm rounded-3" 
                style="border: 1px solid #e2e3e5; background: #ffffff;">
                
                <table class="table table-hover mb-0" id="feesTable">
                    <thead class="table-light">
                        <tr style="cursor: pointer;">
                            <th onclick="sortTable(0)">Fee Name</th>
                            <th onclick="sortTable(1)">Amount</th>
                            <th style="cursor: default;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fees as $fee)
                        <tr class="align-middle">
                            <td>{{ $fee->fee_name }}</td>
                            <td class="fw-semibold">₱{{ number_format($fee->amount, 2) }}</td>
                            <td>
                                @if($status === 'active')
                                <button class="btn btn-sm btn-warning shadow-sm" data-bs-toggle="modal"
                                    data-bs-target="#editFeeModal{{ $fee->id }}">
                                    Edit
                                </button>
                                <button class="btn btn-sm btn-danger shadow-sm" data-bs-toggle="modal"
                                    data-bs-target="#deleteFeeModal{{ $fee->id }}">
                                    Delete
                                </button>
                                @else
                                <button class="btn btn-sm btn-success shadow-sm" data-bs-toggle="modal"
                                    data-bs-target="#restoreFeeModal{{ $fee->id }}">
                                    Restore
                                </button>
                                @endif
                            </td>
                        </tr>

                        <!-- Modals stay exactly the same -->
                        <!-- Edit Fee Modal -->
                        <div class="modal fade" id="editFeeModal{{ $fee->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <form action="{{ route('fees.update', $fee->id) }}" method="POST">
                                    @csrf @method('PUT')
                                    <div class="modal-content shadow">
                                        <div class="modal-header bg-warning">
                                            <h5 class="modal-title">Edit Fee</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label>Fee Name</label>
                                                <input type="text" name="fee_name" class="form-control" value="{{ $fee->fee_name }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Amount</label>
                                                <input type="number" step="0.01" name="amount" class="form-control" value="{{ $fee->amount }}" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-warning">Update</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Delete Fee Modal -->
                        <div class="modal fade" id="deleteFeeModal{{ $fee->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <form action="{{ route('fees.delete', $fee->id) }}" method="POST">
                                    @csrf @method('GET')
                                    <div class="modal-content shadow">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title">Delete Fee</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            Are you sure you want to delete "{{ $fee->fee_name }}"?
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger">Delete</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Restore Fee Modal -->
                        <div class="modal fade" id="restoreFeeModal{{ $fee->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <form action="{{ route('fees.restore', $fee->id) }}" method="POST">
                                    @csrf @method('GET')
                                    <div class="modal-content shadow">
                                        <div class="modal-header bg-success text-white">
                                            <h5 class="modal-title">Restore Fee</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            Are you sure you want to restore "{{ $fee->fee_name }}"?
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-success">Restore</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        @endforeach
                    </tbody>
                </table>
            </div>

        </div> <!-- Card end -->
    </div>

    <!-- Add Fee Modal (unchanged logic, only visuals enhanced) -->
    <div class="modal fade" id="addFeeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('fees.add') }}" method="POST">
                @csrf
                <div class="modal-content shadow-lg">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Add Fees</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body bg-light">

                        <div id="fees-container">
                            <div class="row g-3 mb-3 fee-row">
                                <div class="col-md-6">
                                    <label>Fee Name</label>
                                    <input type="text" name="fees[0][fee_name]" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label>Amount</label>
                                    <input type="number" step="0.01" name="fees[0][amount]" class="form-control" required>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-danger remove-row w-100">Remove</button>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-success" id="add-row">+ Add Another Fee</button>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Fees</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</main>

<!-- Fade-in animation -->
<style>
    @keyframes fadeIn {
        from {opacity: 0; transform: translateY(10px);}
        to {opacity: 1; transform: translateY(0);}
    }

    .table-hover tbody tr:hover {
        background: #f3f7ff !important;
        transition: 0.2s;
    }

    .btn, .form-control {
        transition: 0.2s;
    }

    .form-control:focus {
        box-shadow: 0 0 0 0.15rem rgba(13,110,253,0.25);
    }

    .modal-content {
        border-radius: 15px;
    }
</style>


<script>
    let table = document.getElementById("feesTable");
    let originalRows = Array.from(table.tBodies[0].rows);
    let sortState = {}; // default, asc, desc

    document.getElementById('searchInput').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll("#feesTable tbody tr");
        rows.forEach(row => {
            let text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });

    document.addEventListener("DOMContentLoaded", function() {
        let index = 1;

        document.getElementById("add-row").addEventListener("click", function() {
            const container = document.getElementById("fees-container");

            const newRow = document.createElement("div");
            newRow.classList.add("row", "g-3", "mb-3", "fee-row");
            newRow.innerHTML = `
                <div class="col-md-6">
                    <label>Fee Name</label>
                    <input type="text" name="fees[${index}][fee_name]" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label>Amount</label>
                    <input type="number" step="0.01" name="fees[${index}][amount]" class="form-control" required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger remove-row w-100">Remove</button>
                </div>
            `;
            container.appendChild(newRow);
            index++;
        });

        // Remove row handler
        document.addEventListener("click", function(e) {
            if (e.target.classList.contains("remove-row")) {
                e.target.closest(".fee-row").remove();
            }
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