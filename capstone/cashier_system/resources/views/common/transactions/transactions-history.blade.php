@extends('layout.main-master')

@section('content')
<main style="min-height:85vh; padding:5% 5% 8% 5%; background: linear-gradient(to bottom, #f5f7fa, #eef1f5);">
    <div class="container">

        <!-- Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <h2 class="mb-0 fw-bold">
                {{ $show === 'cancelled' ? 'Cancelled Receipts' : 'Transactions History' }}
            </h2>

            <div class="d-flex flex-wrap align-items-center gap-2">

                <!-- Search -->
                <form action="{{ url()->current() }}" method="GET" class="position-relative">
                    <input type="hidden" name="show" value="{{ $show }}">
                    <input 
                        type="text"
                        id="searchInput"
                        name="search"
                        value="{{ request('search') }}"
                        class="form-control form-control-sm pe-5 shadow-sm"
                        placeholder="Search..."
                        style="min-width: 200px;"
                    >
                    @if(request('search'))
                        <button 
                            type="button"
                            id="clearSearchBtn"
                            class="btn btn-sm btn-light position-absolute end-0 top-50 translate-middle-y me-2 px-2 shadow"
                            title="Clear search"
                        >
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    @endif
                </form>

                <!-- Toggle -->
                <div class="btn-group shadow-sm">
                    <a href="{{ request()->fullUrlWithQuery(['show' => 'active', 'page' => 1]) }}"
                        class="btn btn-sm {{ $show === 'active' ? 'btn-primary' : 'btn-outline-dark' }}">
                        Issued
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['show' => 'cancelled', 'page' => 1]) }}"
                        class="btn btn-sm {{ $show === 'cancelled' ? 'btn-danger' : 'btn-outline-dark' }}">
                        Cancelled
                    </a>
                </div>

                <!-- Filter Button -->
                <button class="btn btn-light btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="fa-solid fa-filter me-1"></i> Filters
                </button>

            </div>
        </div>

        <!-- Table Card -->
        <div class="card shadow-sm border-0 p-3 mb-4 bg-white rounded-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center mb-0" id="historyTable">
                    <thead class="table-dark">
                        <tr>
                            <th onclick="sortTable(0)">Transaction ID</th>
                            <th onclick="sortTable(1)">Receipt Number</th>
                            <th onclick="sortTable(2)">Customer Name</th>
                            <th onclick="sortTable(3)">Total Amount</th>
                            <th onclick="sortTable(4)">Transaction Date</th>
                            @if($show === 'active')
                                <th onclick="sortTable(5)">Receipt Date</th>
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
                            <td class="text-uppercase fw-semibold">{{ $transaction->customer_name }}</td>
                            <td>₱{{ number_format($transaction->total_amount, 2) }}</td>
                            <td>{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('Y-m-d') }}</td>

                            @if($show === 'active')
                                <td>{{ \Carbon\Carbon::parse($transaction->receipt_print_date)->format('Y-m-d') }}</td>
                                <td>
                                    <a href="{{ route('customer.transaction.details', ['id' => $transaction->transaction_id]) }}" 
                                       class="btn btn-sm btn-outline-danger shadow-sm"
                                       title="View Details">
                                       <i class="fa-solid fa-receipt"></i>
                                    </a>
                                </td>
                            @else
                                <td>{{ \Carbon\Carbon::parse($transaction->cancelled_at)->format('Y-m-d H:i') }}</td>
                                <td>
                                    <a href="{{ route('customer.transaction.details', ['id' => $transaction->transaction_id]) }}" 
                                       class="btn btn-sm btn-outline-danger shadow-sm"
                                       title="View Details">
                                       <i class="fa-solid fa-receipt"></i>
                                    </a>
                                </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-3 text-muted">
                                No {{ $show === 'cancelled' ? 'cancelled receipts' : 'transactions' }} found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center align-items-center flex-wrap gap-2 mt-4">

            @if ($result->onFirstPage())
                <button class="btn btn-outline-dark rounded-pill px-3" disabled>« First</button>
                <button class="btn btn-outline-dark rounded-pill px-3" disabled>‹ Prev</button>
            @else
                <a href="{{ $result->url(1) }}" class="btn btn-outline-dark rounded-pill px-3">« First</a>
                <a href="{{ $result->previousPageUrl() }}" class="btn btn-outline-dark rounded-pill px-3">‹ Prev</a>
            @endif

            <!-- Page input -->
            <form action="{{ url()->current() }}" method="GET" class="d-flex align-items-center shadow-sm p-2 rounded-pill bg-white">
                <span class="me-2">Page</span>
                <input 
                    type="number" 
                    name="page" 
                    value="{{ $result->currentPage() }}" 
                    min="1" 
                    max="{{ $result->lastPage() }}" 
                    class="form-control form-control-sm text-center me-2"
                    style="width:70px;"
                    onkeydown="if(event.key === 'Enter') this.form.submit();"
                >
                <span class="me-3">of {{ $result->lastPage() }}</span>

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
        <div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md">
                <form action="{{ url()->current() }}" method="GET" class="modal-content p-4 shadow">
                    <input type="hidden" name="show" value="{{ $show }}">

                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">
                            <i class="fa-solid fa-filter me-2"></i>
                            {{ $show === 'cancelled' ? 'Filter Cancelled Receipts' : 'Filter Transactions' }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Timeframe</label>
                            <select name="timeframe" class="form-select shadow-sm">
                                <option value="">All Timeframes</option>
                                <option value="today"      {{ request('timeframe') == 'today' ? 'selected' : '' }}>Today</option>
                                <option value="this_week"  {{ request('timeframe') == 'this_week' ? 'selected' : '' }}>This Week</option>
                                <option value="this_month" {{ request('timeframe') == 'this_month' ? 'selected' : '' }}>This Month</option>
                            </select>
                        </div>

                        <div class="col-md-12 d-flex justify-content-between align-items-center mt-3">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary shadow-sm">Apply Filters</button>
                                <a href="{{ url()->current() }}?show={{ $show }}" class="btn btn-outline-dark shadow-sm">Reset</a>
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

    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('searchInput');
        const clearBtn = document.getElementById('clearSearchBtn');

        // Submit search to server when typing Enter
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchInput.form.submit();
            }
        });

        // Optional: auto-search after a short delay (live server search)
        let typingTimer;
        searchInput.addEventListener('input', () => {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => {
                searchInput.form.submit();
            }, 600); // delay before auto-submit (optional)
        });

        // Clear search instantly
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                searchInput.value = '';
                searchInput.form.submit();
            });
        }
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

    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('searchInput');
        const clearBtn = document.getElementById('clearSearchBtn');

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                searchInput.value = '';
                searchInput.form.submit(); // reload results without search filter
            });
        }

        // Optional: submit form when pressing Enter
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchInput.form.submit();
            }
        });
    });
</script>
@endsection