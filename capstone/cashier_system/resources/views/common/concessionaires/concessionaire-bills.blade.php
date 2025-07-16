@extends('layout.main-master')

@section('content')
<main style="background-image: url('/bgpup3.jpg'); background-repeat: no-repeat; background-size:auto; background-position: right center; min-height: 85vh; padding: 2%;">
    <div class="container">

        <!-- Header and Filters -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <h2 class="mb-0">Concessionaire Billing List</h2>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <form action="{{ url()->current() }}" method="GET" class="d-flex">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="🔍 Search..." value="{{ request('search') }}">
                    @foreach(request()->except('search', 'page') as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                </form>

                <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="fa-solid fa-filter me-1"></i> Filters
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="card shadow-sm p-3 mb-4 bg-light rounded">
            <div class="table-responsive">
                <table class="table table-striped align-middle text-center mb-0">
                    <thead class="table-dark">
                        @php
                            function sortCycle($field) {
                                $current = request('sort_by') === $field ? request('sort_order', 'default') : 'default';
                                return match ($current) {
                                    'asc' => 'desc',
                                    'desc' => 'default',
                                    default => 'asc',
                                };
                            }
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
                            @foreach([
                                'id' => 'ID',
                                'concessionaire' => 'Concessionaire',
                                'utility_type' => 'Utility Type',
                                'bill_amount' => 'Bill Amount',
                                'balance_due' => 'Balance Due',
                                'due_date' => 'Due Date',
                                'status' => 'Status',
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
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($result as $billing)
                        <tr>
                            <td>{{ $billing->id }}</td>
                            <td>{{ optional($billing->concessionaire)->name ?? 'N/A' }}</td>
                            <td>{{ $billing->utility_type }}</td>
                            <td>{{ $billing->bill_amount }}</td>
                            <td>{{ $billing->balance_due }}</td>
                            <td>{{ $billing->due_date }}</td>
                            <td>{{ $billing->status }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center">No concessionaire billings found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center align-items-center flex-wrap gap-2 mt-3">
            @if ($result->onFirstPage())
                <button class="btn btn-outline-dark rounded-pill px-3" disabled>« First</button>
                <button class="btn btn-outline-dark rounded-pill px-3" disabled>‹ Prev</button>
            @else
                <a href="{{ $result->url(1) }}" class="btn btn-outline-secondary rounded-pill px-3">« First</a>
                <a href="{{ $result->previousPageUrl() }}" class="btn btn-outline-secondary rounded-pill px-3">‹ Prev</a>
            @endif

            <form action="{{ url()->current() }}" method="GET" class="d-flex align-items-center">
                <span class="me-2">Page</span>
                <input type="number" name="page" value="{{ $result->currentPage() }}" min="1" max="{{ $result->lastPage() }}"
                    class="form-control form-control-sm text-center me-2" style="width: 70px;" onkeydown="if(event.key === 'Enter') this.form.submit();">
                <span>of {{ $result->lastPage() }}</span>
                @foreach(request()->except('page') as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
            </form>

            @if ($result->hasMorePages())
                <a href="{{ $result->nextPageUrl() }}" class="btn btn-outline-secondary rounded-pill px-3">Next ›</a>
                <a href="{{ $result->url($result->lastPage()) }}" class="btn btn-outline-secondary rounded-pill px-3">Last »</a>
            @else
                <button class="btn btn-outline-dark rounded-pill px-3" disabled>Next ›</button>
                <button class="btn btn-outline-dark rounded-pill px-3" disabled>Last »</button>
            @endif
        </div>

        <!-- Filter Modal -->
        <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md">
                <form action="{{ url()->current() }}" method="GET" class="modal-content p-4">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fa-solid fa-filter me-2"></i>Filter Billings</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body row g-3">

                        <div class="col-md-6">
                            <label for="utility_type" class="form-label">Utility Type</label>
                            <select name="utility_type" class="form-select">
                                <option value="">All Utilities</option>
                                <option value="Water" {{ request('utility_type') == 'Water' ? 'selected' : '' }}>Water</option>
                                <option value="Electricity" {{ request('utility_type') == 'Electricity' ? 'selected' : '' }}>Electricity</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="status" class="form-label">Payment Status</label>
                            <select name="status" class="form-select">
                                <option value="">All</option>
                                <option value="Unpaid" {{ request('status') == 'Unpaid' ? 'selected' : '' }}>Unpaid</option>
                                <option value="Partially Paid" {{ request('status') == 'Partially Paid' ? 'selected' : '' }}>Partially Paid</option>
                                <option value="Paid" {{ request('status') == 'Paid' ? 'selected' : '' }}>Paid</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="sort_by" class="form-label">Sort By</label>
                            <select name="sort_by" class="form-select">
                                <option value="due_date" {{ request('sort_by') == 'due_date' ? 'selected' : '' }}>Due Date</option>
                                <option value="bill_amount" {{ request('sort_by') == 'bill_amount' ? 'selected' : '' }}>Bill Amount</option>
                                <option value="balance_due" {{ request('sort_by') == 'balance_due' ? 'selected' : '' }}>Balance Due</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Sort Order</label>
                            <select name="sort_order" class="form-select">
                                <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Ascending</option>
                                <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Descending</option>
                            </select>
                        </div>

                        <div class="col-12 d-flex justify-content-between align-items-center">
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
@endsection
