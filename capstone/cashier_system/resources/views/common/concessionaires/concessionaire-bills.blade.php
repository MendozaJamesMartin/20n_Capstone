@extends('layout.main-master')
@section('content')

<main style="background-image: url('/bgpup4.jpg'); background-repeat: no-repeat; background-size: cover; min-height: 85vh; padding: 5%;">

    <div class="container" style="width:75%">

        <div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <td colspan="8">
                            <h2>CONCESSIONAIRE BILLING LIST</h2>
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th colspan="8">
                        <div>
                        <form action="{{ url()->current() }}" method="GET">

                            <input type="text" id="search" class="border p-2 w-1/3 rounded" placeholder="🔍 Search Billing" onkeyup="filterTable()">

                                <label class="block mb-2">Utility Type</label>
                                <select name="utility_type" class="w-full p-2 border rounded mb-4">
                                    <option value="">All Utilities</option>
                                    <option value="Water" {{ request('utility_type') == 'Water' ? 'selected' : '' }}>Water</option>
                                    <option value="Electricity" {{ request('utility_type') == 'Electricity' ? 'selected' : '' }}>Electricity</option>
                                </select>

                                <label class="block mb-2">Payment Status</label>
                                <select name="status" class="w-full p-2 border rounded mb-4">
                                    <option value="">All</option>
                                    <option value="Unpaid" {{ request('status') == 'Unpaid' ? 'selected' : '' }}>Unpaid</option>
                                    <option value="Partially Paid" {{ request('status') == 'Partially Paid' ? 'selected' : '' }}>Partially Paid</option>
                                    <option value="Paid" {{ request('status') == 'Paid' ? 'selected' : '' }}>Paid</option>
                                </select>

                                <label class="block mb-2">Sort By</label>
                                <select name="sort_by" class="w-full p-2 border rounded mb-4">
                                    <option value="due_date" {{ request('sort_by') == 'due_date' ? 'selected' : '' }}>Due Date</option>
                                    <option value="name" {{ request('sort_by') == 'concessionaire_name' ? 'selected' : '' }}>Name</option>
                                    <option value="bill_amount" {{ request('sort_by') == 'bill_amount' ? 'selected' : '' }}>Bill Amount</option>
                                    <option value="balance_due" {{ request('sort_by') == 'balance_due' ? 'selected' : '' }}>Balance Due</option>
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
                        <th>ID</th>
                        <th>Concessionaire</th>
                        <th>Utility Type</th>
                        <th>Bill Amount</th>
                        <th>Balance Due</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </tbody>
                <tbody>
                @forelse($result as $billings)
                    <tr>
                        <td>{{ $billings->id }}</td>
                        <td>{{ optional($billings->concessionaire)->name ?? 'N/A' }}</td>
                        <td>{{ $billings->utility_type }}</td>
                        <td>{{ $billings->bill_amount }}</td>
                        <td>{{ $billings->balance_due }}</td>
                        <td>{{ $billings->due_date }}</td>
                        <td>{{ $billings->status }}</td>
                        <td></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No Concessionaire Billing found</td>
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