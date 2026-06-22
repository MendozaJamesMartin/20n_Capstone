@extends('layout.main-master')

@section('content')

<main style="min-height: 85vh; padding: 3%; 
    background: linear-gradient(135deg, #eef2f7, #f8f9fc);">

    <div class="container mt-4" style="width: 75%;">

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
                <h3 class="fw-bold mb-0">Deposits Management</h3>

                <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addDepositModal">
                    <i class="bi bi-plus-circle"></i> Add Deposit
                </button>

            </div>

            <!-- Search -->
            <div class="mb-4">
                <input type="text" id="searchInput" class="form-control shadow-sm"
                    placeholder="Search Deposits..."
                    style="border-radius: 15px;">
            </div>

            <!-- Table Wrapper -->
            <div class="table-responsive shadow-sm rounded-3"
                style="border: 1px solid #e2e3e5; background: #ffffff;">

                <table class="table table-hover mb-0" id="depositsTable">
                    <thead class="table-light">
                        <tr style="cursor: pointer;">
                            <th onclick="sortTable(0)">Deposit Date</th>
                            <th onclick="sortTable(1)">Reference No.</th>
                            <th onclick="sortTable(2)">Bank</th>
                            <th onclick="sortTable(3)">Account No.</th>
                            <th onclick="sortTable(4)">Amount</th>
                            <th style="cursor: default;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deposits as $deposit)
                        <tr class="align-middle">
                            <td>{{ \Carbon\Carbon::parse($deposit->deposit_date)->format('Y-m-d') }}</td>
                            <td>{{ $deposit->reference_number }}</td>
                            <td>{{ $deposit->bank_name }}</td>
                            <td>{{ $deposit->account_number }}</td>
                            <td class="fw-semibold">
                                ₱{{ number_format($deposit->amount,2) }}
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning shadow-sm" data-bs-toggle="modal"
                                    data-bs-target="#editDepositModal{{ $deposit->id }}">
                                    Edit
                                </button>
                                <button class="btn btn-sm btn-danger shadow-sm" data-bs-toggle="modal"
                                    data-bs-target="#deleteDepositModal{{ $deposit->id }}">
                                    Delete
                                </button>
                            </td>
                        </tr>

                        <!-- Modals stay exactly the same -->
                        <!-- Edit Deposit Modal -->
                        <div class="modal fade" id="editDepositModal{{ $deposit->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <form action="{{ route('deposits.edit', $deposit->id) }}" method="POST">
                                    @csrf @method('PUT')
                                    <div class="modal-content shadow">
                                        <div class="modal-header bg-warning">
                                            <h5 class="modal-title">Edit Deposit</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label>Deposit Date</label>
                                                <input type="date" name="deposit_date" class="form-control" value="{{ $deposit->deposit_date }}" required>
                                            </div>
                                            <div class="mb-3 position-relative">
                                                <label>Reference Number</label>
                                                <input type="text" name="reference_number" class="form-control" value="{{ $deposit->reference_number }}" required>
                                            </div>
                                            <div class="mb-3 position-relative">
                                                <label>Bank</label>
                                                <select class="form-select" name="bank_name" aria-label="Default select example">
                                                    <option value="Land Bank of the Philippines">Land Bank of the Philippines</option>
                                                    <option value="BDO">BDO Unibank, Inc</option>
                                                    <option value="Metrobank">Metrobank</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                            </div>
                                            <div class="mb-3 position-relative">
                                                <label>Account Number</label>
                                                <input type="text" name="account_number" class="form-control" value="{{ $deposit->account_number }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Amount</label>
                                                <input type="number" step="0.01" name="amount" class="form-control" value="{{ $deposit->amount }}" required>
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

                        <!-- Delete Deposit Modal -->
                        <div class="modal fade" id="deleteDepositModal{{ $deposit->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <form action="{{ route('deposits.delete', $deposit->id) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <div class="modal-content shadow">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title">Delete Deposit</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            Are you sure you want to delete deposit "{{ $deposit->reference_number }}"?
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger">Delete</button>
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

    <!-- Add Deposit Modal (unchanged logic, only visuals enhanced) -->
    <div class="modal fade" id="addDepositModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('deposits.add') }}" method="POST">
                @csrf
                <div class="modal-content shadow">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title">Add Deposit</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Deposit Date</label>
                            <input type="date" name="deposit_date" class="form-control" required>
                        </div>
                        <div class="mb-3 position-relative">
                            <label>Reference Number</label>
                            <input type="text" name="reference_number" class="form-control" required>
                        </div>
                        <div class="mb-3 position-relative">
                            <label>Bank</label>
                            <select class="form-select" name="bank_name" aria-label="Default select example">
                                <option value="Land Bank of the Philippines">Land Bank of the Philippines</option>
                                <option value="BDO">BDO Unibank, Inc</option>
                                <option value="Metrobank">Metrobank</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="mb-3 position-relative">
                            <label>Account Number</label>
                            <input type="text" name="account_number" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Amount</label>
                            <input type="number" step="0.01" name="amount" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Deposit</button>
                    </div>
                </div>
        </div>
        </form>
    </div>
    </div>

</main>

<!-- Fade-in animation -->
<style>
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .table-hover tbody tr:hover {
        background: #f3f7ff !important;
        transition: 0.2s;
    }

    .btn,
    .form-control {
        transition: 0.2s;
    }

    .form-control:focus {
        box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.25);
    }

    .modal-content {
        border-radius: 15px;
    }
</style>

<script>
    let table = document.getElementById("depositsTable");
    let originalRows = Array.from(table.tBodies[0].rows);
    let sortState = {};

    document.getElementById('searchInput').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();

        let rows =
            document.querySelectorAll(
                "#depositsTable tbody tr"
            );

        rows.forEach(row => {

            let text =
                row.textContent.toLowerCase();

            row.style.display =
                text.includes(filter) ?
                '' :
                '';

            if (!text.includes(filter)) {
                row.style.display = 'none';
            }

        });

    });

    function sortTable(n) {

        let rows =
            Array.from(
                table.tBodies[0].rows
            );

        let state =
            sortState[n] ||
            'default';

        Array.from(
                table.tHead.rows[0].cells
            )
            .forEach(cell => {

                cell.innerText =
                    cell.innerText.replace(
                        / ↑| ↓/g,
                        ''
                    );

            });

        if (state === 'default') {

            rows.sort(
                (a, b) =>
                a.cells[n]
                .innerText
                .localeCompare(
                    b.cells[n].innerText,
                    undefined, {
                        numeric: true
                    }
                )
            );

            sortState[n] = 'asc';

            table.tHead.rows[0]
                .cells[n]
                .innerText += ' ↑';

        } else if (state === 'asc') {

            rows.sort(
                (a, b) =>
                b.cells[n]
                .innerText
                .localeCompare(
                    a.cells[n].innerText,
                    undefined, {
                        numeric: true
                    }
                )
            );

            sortState[n] = 'desc';

            table.tHead.rows[0]
                .cells[n]
                .innerText += ' ↓';

        } else {

            rows = [...originalRows];

            sortState[n] =
                'default';

        }

        table.tBodies[0].innerHTML =
            '';

        rows.forEach(
            row =>
            table.tBodies[0]
            .appendChild(row)
        );
    }
</script>

@endsection