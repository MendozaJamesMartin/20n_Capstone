@extends('layout.main-master')

@section('content')

<main style="background-image: url('/bgpup3.jpg'); background-repeat: no-repeat; background-size:auto; background-position: right center; min-height: 85vh; padding: 2%;">
    <div class="container">

        <!-- Header & Search -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h2 class="fw-bold mb-0">Audit Logs</h2>
            <form method="GET" action="{{ url()->current() }}" class="d-flex gap-2 flex-wrap">
                <input type="text" id="searchInput" class="form-control" placeholder="🔍 Search user/event/model...">
                <button type="button" class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="fa-solid fa-filter me-1"></i> Filters
                </button>
                <a href="{{ url()->current() }}" class="btn btn-sm btn-outline-dark">Reset</a>
            </form>
        </div>

        <!-- Table -->
        <div class="card shadow-sm p-3 mb-4 bg-light rounded">
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center mb-0" id="auditTable">
                    <thead class="table-dark">
                        <tr>
                            <th onclick="sortTable(0)">Date</th>
                            <th onclick="sortTable(1)">User</th>
                            <th onclick="sortTable(2)">Event</th>
                            <th onclick="sortTable(3)">Model</th>
                            <th class="text-start" onclick="sortTable(4)">Old Values</th>
                            <th class="text-start" onclick="sortTable(5)">New Values</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($audits as $audit)
                        <tr>
                            <td class="fw-semibold">{{ $audit->created_at->format('Y-m-d H:i:s') }}</td>
                            <td>{{ $audit->user->name ?? 'System' }}</td>
                            <td class="text-capitalize">
                                <span class="badge bg-dark">{{ str_replace('_', ' ', $audit->event) }}</span>
                            </td>
                            <td>{{ class_basename($audit->auditable_type) }} <small class="text-muted">(ID: {{ $audit->auditable_id }})</small></td>
                            <td class="text-start small" style="max-width: 250px; white-space: normal;">
                                @include('layout.audit-values', ['data' => $audit->old_values, 'audit' => $audit])
                            </td>
                            <td class="text-start small" style="max-width: 250px; white-space: normal;">
                                @include('layout.audit-values', ['data' => $audit->new_values, 'audit' => $audit])
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No audit logs found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination Controls -->
        <div class="d-flex justify-content-center align-items-center flex-wrap gap-2 mt-3">

            <!-- Go to First Page -->
            @if ($audits->onFirstPage())
            <button class="btn btn-outline-dark rounded-pill px-3" disabled>« First</button>
            @else
            <a href="{{ $audits->appends(request()->except('page'))->url(1) }}"
                class="btn btn-outline-dark rounded-pill px-3">« First</a>
            @endif

            <!-- Previous Page -->
            @if ($audits->onFirstPage())
            <button class="btn btn-outline-dark rounded-pill px-3" disabled>‹ Prev</button>
            @else
            <a href="{{ $audits->appends(request()->except('page'))->previousPageUrl() }}"
                class="btn btn-outline-dark rounded-pill px-3">‹ Prev</a>
            @endif

            <!-- Editable Page Input -->
            <form action="{{ url()->current() }}" method="GET" class="d-flex align-items-center">
                <span class="me-2">Page</span>
                <input
                    type="number"
                    name="page"
                    value="{{ $audits->currentPage() }}"
                    min="1"
                    max="{{ $audits->lastPage() }}"
                    class="form-control form-control-sm text-center me-2"
                    style="width: 70px;">
                <span>of {{ $audits->lastPage() }}</span>

                {{-- Preserve filters --}}
                @foreach(request()->except('page') as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
            </form>

            <!-- Next Page -->
            @if ($audits->hasMorePages())
            <a href="{{ $audits->appends(request()->except('page'))->nextPageUrl() }}"
                class="btn btn-outline-dark rounded-pill px-3">Next ›</a>
            @else
            <button class="btn btn-outline-dark rounded-pill px-3" disabled>Next ›</button>
            @endif

            <!-- Go to Last Page -->
            @if ($audits->currentPage() == $audits->lastPage())
            <button class="btn btn-outline-dark rounded-pill px-3" disabled>Last »</button>
            @else
            <a href="{{ $audits->appends(request()->except('page'))->url($audits->lastPage()) }}"
                class="btn btn-outline-dark rounded-pill px-3">Last »</a>
            @endif

        </div>
    </div>

    <!-- Filter Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form action="{{ url()->current() }}" method="GET" class="modal-content border-0 shadow-sm rounded">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="filterModalLabel"><i class="fa-solid fa-filter me-2"></i> Filter Audit Logs</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label for="event" class="form-label">Event Type</label>
                        <select name="event" class="form-select">
                            <option value="">All Events</option>
                            <option value="created" {{ request('event') == 'created' ? 'selected' : '' }}>Created</option>
                            <option value="updated" {{ request('event') == 'updated' ? 'selected' : '' }}>Updated</option>
                            <option value="deleted" {{ request('event') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                            <option value="restored" {{ request('event') == 'restored' ? 'selected' : '' }}>Restored</option>
                            <option value="finalized_payment" {{ request('event') == 'finalized_payment' ? 'selected' : '' }}>Finalized Payment</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="model" class="form-label">Model</label>
                        <select name="model" class="form-select">
                            <option value="">All Models</option>
                            <option value="User" {{ request('model') == 'User' ? 'selected' : '' }}>User</option>
                            <option value="Fee" {{ request('model') == 'Fee' ? 'selected' : '' }}>Fee</option>
                            <option value="Concessionaire" {{ request('model') == 'Concessionaire' ? 'selected' : '' }}>Concessionaire</option>
                            <option value="Transaction" {{ request('model') == 'Transaction' ? 'selected' : '' }}>Transaction</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="user" class="form-label">User</label>
                        <input type="text" name="user" class="form-control" placeholder="Exact username" value="{{ request('user') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date Range</label>
                        <div class="input-group">
                            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                            <span class="input-group-text">to</span>
                            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <a href="{{ url()->current() }}" class="btn btn-outline-dark">Reset All</a>
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
    let table = document.getElementById("auditTable");
    let originalRows = Array.from(table.tBodies[0].rows);
    let sortState = {}; // default, asc, desc

    document.getElementById('searchInput').addEventListener('keyup', function () {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll("#auditTable tbody tr");
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