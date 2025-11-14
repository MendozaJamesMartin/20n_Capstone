@extends('layout.main-master')
@section('content')

<main style="min-height:85vh; padding:5% 5% 8% 5%; background: linear-gradient(to bottom, #f5f7fa, #eef1f5);">
    <div class="container">

        <!-- Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <h2 class="mb-0 fw-bold">Pending Payments</h2>

            <div class="d-flex flex-wrap align-items-center gap-2">

                <!-- Search -->
                <form action="{{ url()->current() }}" method="GET" class="d-flex position-relative">
                    <input 
                        type="text" 
                        id="searchInput" 
                        class="form-control pe-5" 
                        placeholder="Search transactions..."
                    >
                </form>

                <!-- Filter Button -->
                <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="fa-solid fa-filter me-1"></i> Filters
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="card shadow-sm p-3 mb-4 bg-light rounded">
            <div class="table-responsive">
                <table class="table table-striped align-middle text-center mb-0" id="pendingTable">
                    <thead class="table-dark">
                        <tr>
                            <th onclick="sortTable(0)">Transaction #</th>
                            <th onclick="sortTable(1)">Customer Name</th>
                            <th onclick="sortTable(2)">Total Amount</th>
                            <th onclick="sortTable(3)">Amount Paid</th>
                            <th onclick="sortTable(4)">Balance Due</th>
                            <th onclick="sortTable(5)">Submitted At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($result as $transaction)
                        <tr>
                            <td>{{ $transaction->transaction_number }}</td>
                            <td class="text-uppercase fw-semibold">{{ $transaction->customer_name }}</td>
                            <td>{{ $transaction->total_amount }}</td>
                            <td>{{ $transaction->amount_paid }}</td>
                            <td>{{ $transaction->balance_due }}</td>
                            <td>{{ \Carbon\Carbon::parse($transaction->created_at)->format('Y-m-d') }}</td>
                            <td>
                                <a href="{{ route('payments.update', ['transactionId' => $transaction->transaction_id]) }}" 
                                   class="btn btn-sm btn-outline-danger" 
                                   title="View and Edit Payment">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('payments.disapprove', ['id' => $transaction->transaction_id]) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Are you sure you want to disapprove and delete this transaction?');" 
                                      style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="btn btn-sm btn-outline-danger" 
                                            title="Disapprove Payment">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No pending payments found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination Controls -->
        <div class="d-flex justify-content-center align-items-center flex-wrap gap-2 mt-3">

            @if ($result->onFirstPage())
                <button class="btn btn-outline-dark rounded-pill px-3" disabled>« First</button>
                <button class="btn btn-outline-dark rounded-pill px-3" disabled>‹ Prev</button>
            @else
                <a href="{{ $result->url(1) }}" class="btn btn-outline-dark rounded-pill px-3">« First</a>
                <a href="{{ $result->previousPageUrl() }}" class="btn btn-outline-dark rounded-pill px-3">‹ Prev</a>
            @endif

            <form action="{{ url()->current() }}" method="GET" class="d-flex align-items-center">
                <span class="me-2">Page</span>
                <input 
                    type="number" 
                    name="page" 
                    value="{{ $result->currentPage() }}" 
                    min="1" 
                    max="{{ $result->lastPage() }}"
                    class="form-control form-control-sm text-center me-2" 
                    style="width: 70px;"
                    onkeydown="if(event.key === 'Enter') this.form.submit();"
                >
                <span>of {{ $result->lastPage() }}</span>

                @foreach(request()->except('page') as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
            </form>

            @if ($result->hasMorePages())
                <a href="{{ $result->nextPageUrl() }}" class="btn btn-outline-dark rounded-pill px-3">Next ›</a>
                <a href="{{ $result->url($result->lastPage()) }}" class="btn btn-outline-dark rounded-pill px-3">Last »</a>
            @else
                <button class="btn btn-outline-dark rounded-pill px-3" disabled>Next ›</button>
                <button class="btn btn-outline-dark rounded-pill px-3" disabled>Last »</button>
            @endif
        </div>

        <!-- Filter Modal -->
        <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <form action="{{ url()->current() }}" method="GET" class="modal-content p-4">
                    <div class="modal-header">
                        <h5 class="modal-title" id="filterModalLabel">
                            <i class="fa-solid fa-filter me-2"></i> Filter Payments
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body row g-3">

                        <div class="col-md-6">
                            <label for="timeframe" class="form-label">Timeframe</label>
                            <select name="timeframe" class="form-select">
                                <option value="">All Timeframes</option>
                                <option value="today" {{ request('timeframe') == 'today' ? 'selected' : '' }}>Today</option>
                                <option value="this_week" {{ request('timeframe') == 'this_week' ? 'selected' : '' }}>This Week</option>
                                <option value="this_month" {{ request('timeframe') == 'this_month' ? 'selected' : '' }}>This Month</option>
                            </select>
                        </div>

                        <input type="hidden" name="sort_order" id="sortOrderInput" value="{{ request('sort_order', 'desc') }}">

                        <div class="col-md-12 d-flex justify-content-between align-items-center">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                                <a href="{{ url()->current() }}" class="btn btn-outline-dark">Reset</a>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>

    </div>
</main>

<script>
    let table = document.getElementById("pendingTable");
    let originalRows = Array.from(table.tBodies[0].rows);
    let sortState = {}; // default, asc, desc

    document.getElementById('searchInput').addEventListener('keyup', function () {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll("#pendingTable tbody tr");
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
