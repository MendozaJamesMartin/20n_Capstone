@extends('layout.main-master')

@section('content')
<main style="min-height:85vh; padding:5% 5% 8% 5%; background: linear-gradient(to bottom, #f5f7fa, #eef1f5);">
    <div class="container">

        <!-- Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <h2 class="mb-0 fw-bold">Latest Concessionaire Bills</h2>
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
                    <table class="table table-striped align-middle text-center mb-0" id="electricityTable">
                        <thead class="table-dark">
                            <tr>
                                <th onclick="sortTable('electricityTable', 0)">Concessionaire</th>
                                <th onclick="sortTable('electricityTable', 1)">Billing Period</th>
                                <th onclick="sortTable('electricityTable', 2)">Previous Reading</th>
                                <th onclick="sortTable('electricityTable', 3)">Current Reading</th>
                                <th onclick="sortTable('electricityTable', 4)">kWh Used</th>
                                <th onclick="sortTable('electricityTable', 5)">₱/kWh</th>
                                <th onclick="sortTable('electricityTable', 6)">Current Charges</th>
                                <th onclick="sortTable('electricityTable', 7)">Previous Unpaid</th>
                                <th onclick="sortTable('electricityTable', 8)">Total</th>
                                <th onclick="sortTable('electricityTable', 9)">Amount Paid</th>
                                <th onclick="sortTable('electricityTable', 10)">Due Date</th>
                                <th onclick="sortTable('electricityTable', 11)">Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($electricityBills as $bill)
                            <tr>
                                <td class="text-uppercase fw-semibold">{{ $bill->concessionaire_name }}</td>
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
                            <tr><td colspan="13" class="text-center">No electricity bills available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Water Bills -->
            <div class="tab-pane fade" id="water" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-striped align-middle text-center mb-0" id="waterTable">
                        <thead class="table-dark">
                            <tr>
                                <th onclick="sortTable('waterTable', 0)">Concessionaire</th>
                                <th onclick="sortTable('waterTable', 1)">Billing Period</th>
                                <th onclick="sortTable('waterTable', 2)">Current Charges</th>
                                <th onclick="sortTable('waterTable', 3)">Previous Unpaid</th>
                                <th onclick="sortTable('waterTable', 4)">Total</th>
                                <th onclick="sortTable('waterTable', 5)">Amount Paid</th>
                                <th onclick="sortTable('waterTable', 6)">Due Date</th>
                                <th onclick="sortTable('waterTable', 7)">Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($waterBills as $bill)
                            <tr>
                                <td class="text-uppercase fw-semibold">{{ $bill->concessionaire_name }}</td>
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
                            <tr><td colspan="9" class="text-center">No water bills available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    let sortState = {}; // default, asc, desc
    let originalRows = {}; // store original order per table

    function sortTable(tableId, columnIndex) {
        let table = document.getElementById(tableId);
        let tbody = table.tBodies[0];

        // Store original rows ONCE
        if (!originalRows[tableId]) {
            originalRows[tableId] = Array.from(tbody.rows);
        }

        let rows = Array.from(tbody.rows);

        let key = tableId + '_' + columnIndex;
        let state = sortState[key] || 'default';

        // Reset header arrows on THIS table
        Array.from(table.tHead.rows[0].cells).forEach(cell => {
            cell.innerText = cell.innerText.replace(/ ↑| ↓/g, '');
        });

        if (state === 'default') {
            rows.sort((a, b) =>
                a.cells[columnIndex].innerText.localeCompare(
                    b.cells[columnIndex].innerText,
                    undefined,
                    { numeric: true }
                )
            );
            sortState[key] = 'asc';
            table.tHead.rows[0].cells[columnIndex].innerText += ' ↑';

        } else if (state === 'asc') {
            rows.sort((a, b) =>
                b.cells[columnIndex].innerText.localeCompare(
                    a.cells[columnIndex].innerText,
                    undefined,
                    { numeric: true }
                )
            );
            sortState[key] = 'desc';
            table.tHead.rows[0].cells[columnIndex].innerText += ' ↓';

        } else {
            // Restore original order (true default reset)
            rows = [...originalRows[tableId]];
            sortState[key] = 'default';
        }

        // Re-render rows
        tbody.innerHTML = '';
        rows.forEach(row => tbody.appendChild(row));
    }
</script>


@endsection
