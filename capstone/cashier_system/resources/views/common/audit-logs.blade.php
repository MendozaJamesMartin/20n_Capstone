@extends('layout.main-master')
@section('content')

<main style="background-image: url('/bgpup3.jpg'); background-repeat: no-repeat; background-size:auto; background-position: right center; min-height: 85vh; padding: 2%;">
    <div class="container">

        <!-- Header & Search -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h2 class="mb-0">Audit Logs</h2>

            <form method="GET" action="{{ url()->current() }}" class="d-flex gap-2 flex-wrap">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="🔍 Search user/event/model...">
                <button type="submit" class="btn btn-sm btn-outline-dark">Search</button>
                <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="fa-solid fa-filter me-1"></i> Filters
                </button>
                <a href="{{ url()->current() }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </form>
        </div>

        <!-- Table -->
        <div class="card shadow-sm p-3 bg-light rounded">
            <div class="table-responsive">
                <table class="table table-striped align-middle text-center mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>User</th>
                            <th>Event</th>
                            <th>Model</th>
                            <th>Old Values</th>
                            <th>New Values</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($audits as $audit)
                            <tr>
                                <td>{{ $audit->created_at->format('Y-m-d H:i:s') }}</td>
                                <td>{{ $audit->user->name ?? 'System' }}</td>
                                <td class="text-capitalize">{{ str_replace('_', ' ', $audit->event) }}</td>
                                <td>{{ class_basename($audit->auditable_type) }} (ID: {{ $audit->auditable_id }})</td>
                                <td class="text-start">
                                    @forelse ($audit->old_values as $key => $value)
                                        <div><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ is_bool($value) ? ($value ? 'Yes' : 'No') : $value }}</div>
                                    @empty
                                        <em class="text-muted">None</em>
                                    @endforelse
                                </td>
                                <td class="text-start">
                                    @forelse ($audit->new_values as $key => $value)
                                        <div><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ is_bool($value) ? ($value ? 'Yes' : 'No') : $value }}</div>
                                    @empty
                                        <em class="text-muted">None</em>
                                    @endforelse
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No audit logs found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-3">
            {{ $audits->appends(request()->query())->links() }}
        </div>
    </div>

    <!-- Filter Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form action="{{ url()->current() }}" method="GET" class="modal-content p-4">
                <div class="modal-header">
                    <h5 class="modal-title" id="filterModalLabel"><i class="fa-solid fa-filter me-2"></i> Filter Audit Logs</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                            <!-- Add more as needed -->
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
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="{{ url()->current() }}" class="btn btn-outline-secondary">Reset All</a>
                </div>
            </form>
        </div>
    </div>
</main>

@endsection
