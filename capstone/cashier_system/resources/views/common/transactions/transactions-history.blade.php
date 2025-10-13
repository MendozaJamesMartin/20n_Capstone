@extends('layout.main-master')

@section('content')
<main style="background-image: url('/bgpup3.jpg'); background-repeat: no-repeat; background-size:auto; background-position: right center; min-height: 85vh; padding: 2%;">
    <div class="container">

        <!-- Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <h2 class="mb-0">
                {{ $show === 'cancelled' ? 'Cancelled Receipts' : 'Transactions History' }}
            </h2>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <!-- Search Input -->
                <form action="{{ url()->current() }}" method="GET" class="d-flex">
                    <input type="hidden" name="show" value="{{ $show }}">
                    <input type="text" id="searchInput" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search...">
                </form>

                <!-- Toggle Active / Cancelled -->
                <div class="btn-group">
                    <a href="{{ request()->fullUrlWithQuery(['show' => 'active', 'page' => 1]) }}"
                        class="btn btn-sm {{ $show === 'active' ? 'btn-primary' : 'btn-outline-secondary' }}">
                        Issued
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['show' => 'cancelled', 'page' => 1]) }}"
                        class="btn btn-sm {{ $show === 'cancelled' ? 'btn-primary' : 'btn-outline-secondary' }}">
                        Cancelled
                    </a>
                </div>

                <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="fa-solid fa-filter me-1"></i> Filters
                </button>

            </div>
        </div>

        <!-- Table -->
        <div class="card shadow-sm p-3 mb-4 bg-light rounded">
            <div class="table-responsive">
                <table class="table table-striped align-middle text-center mb-0" id="historyTable">
                    <thead class="table-dark">
                        <tr>
                            <th onclick="sortTable(0)">Transaction ID</th>
                            <th onclick="sortTable(1)">Receipt Number</th>
                            <th onclick="sortTable(2)">Customer Name</th>
                            <th onclick="sortTable(3)">Customer Type</th>
                            <th onclick="sortTable(4)">Total Amount</th>
                            @if($show === 'active')
                            <th onclick="sortTable(5)">Transaction Date</th>
                            <th onclick="sortTable(6)">Receipt Date</th>
                            <th>Action</th>
                            @else
                            <th onclick="sortTable(5)">Cancelled At</th>
                            <th>Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($result as $transaction)
                        <tr>
                            <td>{{ $transaction->transaction_id }}</td>
                            <td>{{ $transaction->receipt_number }}</td>
                            <td>{{ $transaction->customer_name }}</td>
                            <td>{{ $transaction->customer_type }}</td>
                            <td>{{ number_format($transaction->total_amount, 2) }}</td>

                            @if($show === 'active')
                            <td>{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('Y-m-d') }}</td>
                            <td>{{ \Carbon\Carbon::parse($transaction->receipt_print_date)->format('Y-m-d') }}</td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('customer.transaction.details', ['id' => $transaction->transaction_id]) }}" class="btn btn-sm btn-outline-danger" title="Details"><i class="fa-solid fa-pen-to-square"></i></a>
                                </div>
                            </td>
                            @else
                            <td>{{ \Carbon\Carbon::parse($transaction->cancelled_at)->format('Y-m-d H:i') }}</td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('customer.transaction.details', ['id' => $transaction->transaction_id]) }}" class="btn btn-sm btn-outline-danger" title="Details"><i class="fa-solid fa-pen-to-square"></i></a>
                                </div>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center">
                                No {{ $show === 'cancelled' ? 'cancelled receipts' : 'transactions' }} found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination Controls -->
        <div class="d-flex justify-content-center align-items-center flex-wrap gap-2 mt-3">
            <!-- Go to First Page -->
            @if ($result->onFirstPage())
            <button class="btn btn-outline-dark rounded-pill px-3" disabled>« First</button>
            @else
            <a href="{{ $result->url(1) }}" class="btn btn-outline-secondary rounded-pill px-3">« First</a>
            @endif

            <!-- Previous Page -->
            @if ($result->onFirstPage())
            <button class="btn btn-outline-dark rounded-pill px-3" disabled>‹ Prev</button>
            @else <a href="{{ $result->previousPageUrl() }}" class="btn btn-outline-secondary rounded-pill px-3">‹ Prev</a>
            @endif

            <!-- Editable Page Input -->
            <form action="{{ url()->current() }}" method="GET" class="d-flex align-items-center">
                <span class="me-2">Page</span>
                <input type="number" name="page" value="{{ $result->currentPage() }}" min="1" max="{{ $result->lastPage() }}" class="form-control form-control-sm text-center me-2" style="width: 70px;" onkeydown="if(event.key === 'Enter') this.form.submit();">
                <span>of {{ $result->lastPage() }}</span>
                {{-- Preserve filters --}} @foreach(request()->except('page') as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}"> @endforeach
            </form>

            <!-- Next Page -->
            @if ($result->hasMorePages())
            <a href="{{ $result->nextPageUrl() }}" class="btn btn-outline-secondary rounded-pill px-3">Next ›</a>
            @else
            <button class="btn btn-outline-dark rounded-pill px-3" disabled>Next ›</button>
            @endif

            <!-- Go to Last Page -->
            @if ($result->currentPage() == $result->lastPage())
            <button class="btn btn-outline-dark rounded-pill px-3" disabled>Last »</button>
            @else
            <a href="{{ $result->url($result->lastPage()) }}" class="btn btn-outline-secondary rounded-pill px-3">Last »</a>
            @endif
        </div>

        <!-- Filter Modal -->
        <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <form action="{{ url()->current() }}" method="GET" class="modal-content p-4">
                    <input type="hidden" name="show" value="{{ $show }}"> {{-- Preserve active/cancelled --}}
                    <div class="modal-header">
                        <h5 class="modal-title" id="filterModalLabel">
                            <i class="fa-solid fa-filter me-2"></i>
                            {{ $show === 'cancelled' ? 'Filter Cancelled Receipts' : 'Filter Transactions' }}
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
                        <div class="col-md-6">
                            <label for="customer_type" class="form-label">Payor Type</label>
                            <select name="customer_type" class="form-select">
                                <option value="">All</option>
                                <option value="Customer" {{ request('customer_type') == 'Customer' ? 'selected' : '' }}>Customer</option>
                                <option value="Concessionaire" {{ request('customer_type') == 'Concessionaire' ? 'selected' : '' }}>Concessionaire</option>
                            </select>
                        </div>
                        <div class="col-md-12 d-flex justify-content-between align-items-center">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                                <a href="{{ url()->current() }}?show={{ $show }}" class="btn btn-outline-secondary">Reset</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>

</main>

<script>
    let table = document.getElementById("historyTable");
    let originalRows = Array.from(table.tBodies[0].rows);
    let sortState = {};

    document.getElementById('searchInput').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll("#historyTable tbody tr");
        rows.forEach(row => {
            let text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });

    function sortTable(n) {
        let rows = Array.from(table.tBodies[0].rows);
        let state = sortState[n] || 'default';

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