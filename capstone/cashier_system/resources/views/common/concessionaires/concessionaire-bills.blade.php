@extends('layout.main-master')

@section('content')
<main style="background-image:url('/bgpup3.jpg'); background-repeat:no-repeat; background-size:auto; background-position: right center; min-height: 85vh; padding: 2%;">
    <div class="container">

        <!-- Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <h2 class="mb-0">Latest Concessionaire Bills</h2>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-3" id="billTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="electricity-tab" data-bs-toggle="tab" data-bs-target="#electricity" type="button" role="tab">Electricity</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="water-tab" data-bs-toggle="tab" data-bs-target="#water" type="button" role="tab">Water</button>
            </li>
        </ul>

        <div class="card shadow-sm p-3 mb-4 bg-light rounded tab-content" id="billTabsContent">
            <!-- Electricity Bills -->
            <div class="tab-pane fade show active" id="electricity" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-striped align-middle text-center mb-0" id="billsTable">
                        <thead class="table-dark">
                            <tr>
                                <th onclick="sortTable(0)">Concessionaire</th>
                                <th onclick="sortTable(1)">Billing Period</th>
                                <th onclick="sortTable(2)">Previous Reading</th>
                                <th onclick="sortTable(3)">Current Reading</th>
                                <th onclick="sortTable(4)">kWh Used</th>
                                <th onclick="sortTable(5)">₱/kWh</th>
                                <th onclick="sortTable(6)">Current Charges</th>
                                <th onclick="sortTable(7)">Previous Unpaid</th>
                                <th onclick="sortTable(8)">Total</th>
                                <th onclick="sortTable(9)">Amount Paid</th>
                                <th onclick="sortTable(10)">Due Date</th>
                                <th onclick="sortTable(11)">Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($electricityBills as $bill)
                            <tr>
                                <td>{{ $bill->concessionaire_name }}</td>
                                <td>{{ $bill->billing_period }}</td>
                                <td>{{ $bill->previous_reading_kwh }}</td>
                                <td>{{ $bill->current_reading_kwh }}</td>
                                <td>{{ number_format($bill->concessionaire_kwh_used, 2) }}</td>
                                <td>₱{{ number_format($bill->cost_per_kwh, 2) }}</td>
                                <td>₱{{ number_format($bill->current_charges, 2) }}</td>
                                <td>₱{{ number_format($bill->previous_unpaid, 2) }}</td>
                                <td>₱{{ number_format($bill->total_due, 2) }}</td>
                                <td>₱{{ number_format($bill->amount_paid, 2) }}</td>
                                <td>{{ $bill->due_date }}</td>
                                <td><span class="badge bg-{{ $bill->status === 'Fully Paid' ? 'success' : ($bill->status === 'Partially Paid' ? 'warning' : 'danger') }}">{{ $bill->status }}</span></td>
                                <td>
                                    <a href="{{ route('concessionaire.bill.electricity.pdf', ['id' => $bill->bill_id]) }}" target="_blank" class="btn btn-sm btn-outline-danger" title="Billing Statement"><i class="fa-solid fa-receipt"></i></a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="12" class="text-center">No electricity bills available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Water Bills -->
            <div class="tab-pane fade" id="water" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-striped align-middle text-center mb-0" id="billsTable">
                        <thead class="table-dark">
                            <tr>
                                <th onclick="sortTable(0)">Concessionaire</th>
                                <th onclick="sortTable(1)">Billing Period</th>
                                <th onclick="sortTable(2)">Current Charges</th>
                                <th onclick="sortTable(3)">Previous Unpaid</th>
                                <th onclick="sortTable(4)">Total</th>
                                <th onclick="sortTable(5)">Amount Paid</th>
                                <th onclick="sortTable(6)">Due Date</th>
                                <th onclick="sortTable(7)">Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($waterBills as $bill)
                            <tr>
                                <td>{{ $bill->concessionaire_name }}</td>
                                <td>{{ $bill->billing_period }}</td>
                                <td>₱{{ number_format($bill->current_charges, 2) }}</td>
                                <td>₱{{ number_format($bill->previous_unpaid, 2) }}</td>
                                <td>₱{{ number_format($bill->total_due, 2) }}</td>
                                <td>₱{{ number_format($bill->amount_paid, 2) }}</td>
                                <td>{{ $bill->due_date }}</td>
                                <td><span class="badge bg-{{ $bill->status === 'Fully Paid' ? 'success' : ($bill->status === 'Partially Paid' ? 'warning' : 'danger') }}">{{ $bill->status }}</span></td>
                                <td>
                                    <a href="{{ route('concessionaire.bill.water.pdf', ['id' => $bill->bill_id]) }}" target="_blank" class="btn btn-sm btn-outline-danger" title="Billing Statement"><i class="fa-solid fa-receipt"></i></a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="text-center">No water bills available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    let table = document.getElementById("billsTable");
    let originalRows = Array.from(table.tBodies[0].rows);
    let sortState = {}; // default, asc, desc

    document.getElementById('searchInput').addEventListener('keyup', function () {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll("#billsTable tbody tr");
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
