@extends('layout.main-user')
@section('content')

<main style="background-image: url('/bgpup4.jpg'); background-repeat: no-repeat; background-size: cover; min-height: 85vh; padding: 5%;">

    <div class="container" style="width:75%">

        <div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <td colspan="8">
                            <h2>TRANSACTIONS HISTORY</h2>
                        </td>
                    </tr>
                </thead>
                <tbody>
                <tr>
                    <th colspan="8" class="text-center">
                        <div class="d-flex justify-content-center align-items-center flex-wrap">
                            <form action="{{ url()->current() }}" method="GET">
                                <input type="text" id="search" style="width:15%" class="border p-2 w-1/3 rounded" placeholder="🔍 Search " onkeyup="filterTable()">

                                <label class="block mb-2">Timeframe</label>
                                <select name="timeframe" class="w-full p-2 border rounded mb-4">
                                    <option value="">All Timeframes</option>
                                    <option value="today" {{ request('timeframe') == 'today' ? 'selected' : '' }}>Today</option>
                                    <option value="this_week" {{ request('timeframe') == 'this_week' ? 'selected' : '' }}>This Week</option>
                                    <option value="this_month" {{ request('timeframe') == 'this_month' ? 'selected' : '' }}>This Month</option>
                                </select>

                                <label class="block mb-2">Payment Status</label>
                                <select name="entity_type" class="w-full p-2 border rounded mb-4">
                                    <option value="student" {{ request('entity_type') == 'student' ? 'selected' : '' }}>Paid</option>
                                    <option value="concessionaire" {{ request('entity_type') == 'concessionaire' ? 'selected' : '' }}>Unpaid</option>
                                </select>

                                <label class="block mb-2">Sort By</label>
                                <select name="sort_by" class="w-full p-2 border rounded mb-4">
                                    <option value="transaction_date" {{ request('sort_by') == 'transaction_date' ? 'selected' : '' }}>Date</option>
                                    <option value="entity_name" {{ request('sort_by') == 'entity_name' ? 'selected' : '' }}>Name</option>
                                    <option value="total_amount" {{ request('sort_by') == 'total_amount' ? 'selected' : '' }}>Total Amount</option>
                                </select>

                                <button type="button" class="btn btn-secondary ms-2" id="sortToggleBtn">
                                    <span id="sortIcon">
                                        {{ request('sort_order', 'desc') == 'desc' ? '🔽' : '🔼' }}
                                    </span>
                                </button>

                                <input type="hidden" name="sort_order" id="sortOrderInput" value="{{ request('sort_order', 'desc') }}">

                                <button type="submit" class="btn btn-danger">Apply</button>
                                <a href="{{ url()->current() }}" class="btn btn-danger">Reset</a>
                            </form>
                        </div>
                    </th>
                </tr>

                    <tr>
                        <th>Transaction ID</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Total Amount</th>
                        <th>Amount Paid</th>
                        <th>Balance Due</th>
                        <th>Transaction Date</th>
                        <th>Action</th>
                    </tr>
                </tbody>
                <tbody>
                @forelse($result as $transaction)
                    <tr>
                        <td>{{ $transaction->id }}</td>
                        <td>{{ $transaction->entity_name }}</td>
                        <td>{{ $transaction->entity_type }}</td>
                        <td>{{ $transaction->total_amount }}</td>
                        <td>{{ $transaction->amount_paid }}</td>
                        <td>{{ $transaction->balance_due }}</td>
                        <td>{{ $transaction->transaction_date }}</td>
                        <td>
                            <a href="{{ url('student/transactions/details/' . $transaction->id) }}" class="btn btn-danger btn-sm">View Details</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No transactions found</td>
                    </tr>
                @endforelse

                <!-- Add empty rows to fill up to 10 rows -->
                @for ($i = $result->count(); $i < 10; $i++)
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                @endfor
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <div class="d-flex justify-content-center align-items-center mt-3 gap-2">
            
            <!-- Go to First Page Button -->
            @if ($result->onFirstPage())
                <button class="btn btn-secondary" disabled>« First</button>
            @else
                <a href="{{ $result->url(1) }}" class="btn btn-secondary">« First</a>
            @endif

            <!-- Previous Page Button -->
            @if ($result->onFirstPage())
                <button class="btn btn-secondary" disabled>‹ Prev</button>
            @else
                <a href="{{ $result->previousPageUrl() }}" class="btn btn-secondary">‹ Prev</a>
            @endif

            <!-- Editable Page Number -->
            <form action="{{ url()->current() }}" method="GET" class="d-flex align-items-center">
                <span>Page</span>
                <input type="number" name="page" class="form-control mx-2 text-center" 
                    value="{{ $result->currentPage() }}" 
                    min="1" max="{{ $result->lastPage() }}" 
                    style="width: 60px;" 
                    onkeydown="if(event.key === 'Enter') this.form.submit();">
                <span>of {{ $result->lastPage() }}</span>
            </form>

            <!-- Next Page Button -->
            @if ($result->hasMorePages())
                <a href="{{ $result->nextPageUrl() }}" class="btn btn-secondary">Next ›</a>
            @else
                <button class="btn btn-secondary" disabled>Next ›</button>
            @endif

            <!-- Go to Last Page Button -->
            @if ($result->currentPage() == $result->lastPage())
                <button class="btn btn-secondary" disabled>Last »</button>
            @else
                <a href="{{ $result->url($result->lastPage()) }}" class="btn btn-secondary">Last »</a>
            @endif

        </div>

    </div>

</main>

<script>
    document.getElementById('sortToggleBtn').addEventListener('click', function () {
        let sortOrderInput = document.getElementById('sortOrderInput');
        let sortIcon = document.getElementById('sortIcon');

        // Toggle between 'asc' and 'desc'
        if (sortOrderInput.value === 'asc') {
            sortOrderInput.value = 'desc';
            sortIcon.innerHTML = '🔽'; // Descending
        } else {
            sortOrderInput.value = 'asc';
            sortIcon.innerHTML = '🔼'; // Ascending
        }

        // Submit the form
        this.closest('form').submit();
    });
</script>


@endsection