@extends('layout.main-master')

@section('content')
<main style="background-image: url('/bgpup3.jpg'); background-repeat: no-repeat; background-size:auto; background-position: right center; min-height: 85vh; padding: 2%;">
    <div class="container">

        <!-- Header: Title, Search, and Filter Button -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <h2 class="mb-0">Transactions History</h2>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <!-- Search Input -->
                <form action="{{ url()->current() }}" method="GET" class="d-flex">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="🔍 Search..."
                        value="{{ request('search') }}">
                    {{-- Preserve filters --}}
                    @foreach(request()->except('search', 'page') as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                </form>

                <!-- Filter Button -->
                <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="fa-solid fa-filter me-1"></i> Filters
                </button>
            </div>
        </div>

        <!-- Responsive Table -->
        <div class="card shadow-sm p-3 mb-4 bg-light rounded">
            <div class="table-responsive">
                <table class="table table-striped align-middle text-center mb-0">
                    <thead class="table-dark">
                        @php
                            // Get current sort state
                            $sortBy = request('sort_by');
                            $sortOrder = request('sort_order', 'default');

                            // Function to cycle sort state
                            function sortCycle($field) {
                                $current = request('sort_by') === $field ? request('sort_order', 'default') : 'default';
                                return match ($current) {
                                    'asc' => 'desc',
                                    'desc' => 'default',
                                    default => 'asc',
                                };
                            }

                            // Function to return icon
                            function sortIcon($field) {
                                if (request('sort_by') !== $field) return '';
                                return match (request('sort_order')) {
                                    'asc' => ' ▲',
                                    'desc' => ' ▼',
                                    default => '',
                                };
                            }
                        @endphp
                        <tr>
                            @foreach ([
                                'transaction_id' => 'Transaction ID',
                                'receipt_number' => 'Receipt No.',
                                'customer_name' => 'Customer Name',
                                'customer_type' => 'Type',
                                'total_amount' => 'Total',
                                'transaction_date' => 'Transaction Date',
                            ] as $field => $label)
                                @php
                                    $newOrder = sortCycle($field);
                                    $params = array_merge(request()->except('sort_by', 'sort_order', 'page'), [
                                        'sort_by' => $newOrder === 'default' ? null : $field,
                                        'sort_order' => $newOrder === 'default' ? null : $newOrder,
                                    ]);
                                    $url = url()->current() . '?' . http_build_query(array_filter($params));
                                @endphp
                                <th>
                                    <a href="{{ $url }}" class="text-white text-decoration-none">
                                        {{ $label }}{!! sortIcon($field) !!}
                                    </a>
                                </th>
                            @endforeach
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($result as $transaction)
                        <tr>
                            <td>{{ $transaction->transaction_id }}</td>
                            <td>{{ $transaction->receipt_number }}</td>
                            <td>{{ $transaction->customer_name }}</td>
                            <td>{{ $transaction->customer_type }}</td>
                            <td>{{ $transaction->total_amount }}</td>
                            <td>{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('Y-m-d') }}</td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    @if($transaction->customer_type === 'Concessionaire')
                                    <a href="{{ route('concessionaire.transaction.details', ['id' => $transaction->transaction_id]) }}" class="btn btn-sm btn-outline-danger" title="Details"><i class="fa-solid fa-pen-to-square"></i></a>
                                    <a href="{{ route('concessionaire.receipt.pdf', ['id' => $transaction->transaction_id]) }}" target="_blank" class="btn btn-sm btn-outline-danger" title="Receipt"><i class="fa-solid fa-receipt"></i></a>
                                    @else
                                    <a href="{{ route('customer.transaction.details', ['id' => $transaction->transaction_id]) }}" class="btn btn-sm btn-outline-danger" title="Details"><i class="fa-solid fa-pen-to-square"></i></a>
                                    <a href="{{ route('customer.receipt.pdf', ['id' => $transaction->transaction_id]) }}" target="_blank" class="btn btn-sm btn-outline-danger" title="Receipt"><i class="fa-solid fa-receipt"></i></a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="10" class="text-center">No transactions found</td></tr>
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
            @else
                <a href="{{ $result->previousPageUrl() }}" class="btn btn-outline-secondary rounded-pill px-3">‹ Prev</a>
            @endif

            <!-- Editable Page Input -->
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

                {{-- Preserve filters --}}
                @foreach(request()->except('page') as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
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
                    <div class="modal-header">
                        <h5 class="modal-title" id="filterModalLabel"><i class="fa-solid fa-filter me-2"></i> Filter Transactions</h5>
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
                                <option value="Student" {{ request('customer_type') == 'Student' ? 'selected' : '' }}>Student</option>
                                <option value="Outsider" {{ request('customer_type') == 'Outsider' ? 'selected' : '' }}>Outsider</option>
                                <option value="Concessionaire" {{ request('customer_type') == 'Concessionaire' ? 'selected' : '' }}>Concessionaire</option>
                            </select>
                        </div>

                        <div class="col-md-12 d-flex justify-content-between align-items-center">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                                <a href="{{ url()->current() }}" class="btn btn-outline-secondary">Reset</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</main>

<script>
    // Toggle sort order value on checkbox
    document.getElementById('toggleSort').addEventListener('change', function () {
        document.getElementById('sortOrderInput').value = this.checked ? 'asc' : 'desc';
    });
</script>
@endsection
