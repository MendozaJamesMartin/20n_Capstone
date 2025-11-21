@extends('layout.main-master')
@section('content')

<style>
    .card:hover {
        transform: scale(1.02);
    }
    .card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .summary-card h5 {
        font-weight: 500;
        font-size: 0.95rem;
    }
    .summary-card h3 {
        font-weight: 700;
        font-size: 1.5rem;
    }
    .table thead th {
        vertical-align: middle;
        text-align: center;
    }
    .table tbody td {
        vertical-align: middle;
        text-align: center;
    }
    .badge-status {
        font-size: 0.85rem;
        padding: 0.4em 0.6em;
    }
</style>

<div class="container py-4">
    <h2 class="mb-4 fw-bold">Cashier Analytics Dashboard</h2>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card summary-card bg-success text-white rounded-4 shadow-sm">
                <div class="card-body text-center">
                    <h5>Total Collection</h5>
                    <h3>₱{{ number_format($totalRevenue, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card summary-card bg-primary text-white rounded-4 shadow-sm">
                <div class="card-body text-center">
                    <h5>This Month's Collection</h5>
                    <h3>₱{{ number_format($monthlyRevenue, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card summary-card bg-warning text-white rounded-4 shadow-sm">
                <div class="card-body text-center">
                    <h5>Unpaid Amount</h5>
                    <h3>₱{{ number_format($unpaidRevenue, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card summary-card bg-danger text-white rounded-4 shadow-sm">
                <div class="card-body text-center">
                    <h5>Cancelled Receipts</h5>
                    <h3>{{ $cancelledReceipts }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Collection Trend + Report Export -->
    <div class="card mb-4 rounded-4 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Monthly Collection Report</h5>
            </div>
            <canvas id="revenueChart" height="100"></canvas>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <!-- Top Fees Chart -->
        <div class="col-md-6">
            <div class="card h-100 rounded-4 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Top Fees (Pie Chart)</h5>
                    <canvas id="feeChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Fees List -->
        <div class="col-md-6">
            <div class="card h-100 rounded-4 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Top Fees Paid</h5>
                    <ul class="list-group list-group-flush">
                        @foreach ($topFees as $fee)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $fee->fee_name }}
                                <span class="badge bg-success rounded-pill">₱{{ number_format($fee->total, 2) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Receipts Analytics -->
    <div class="card mb-4 rounded-4 shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">Receipt Batches</h5>
            <div class="row mb-3">
                <div class="col-md-6"><strong>Total Receipts Issued:</strong> {{ $totalReceiptsIssued }}</div>
                <div class="col-md-6"><strong>Total Remaining:</strong> {{ $totalReceiptsRemaining }}</div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th>Batch</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Next</th>
                            <th>Used</th>
                            <th>Remaining</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($receiptBatches as $batch)
                        <tr>
                            <td>#{{ $batch->id }}</td>
                            <td>{{ $batch->start_number }}</td>
                            <td>{{ $batch->end_number }}</td>
                            <td>{{ $batch->next_number > $batch->end_number ? '—' : $batch->next_number }}</td>
                            <td>{{ $batch->used_count }}</td>
                            <td>{{ $batch->remaining_count }}</td>
                            <td>
                                @if($batch->exhausted_at)
                                    <span class="badge bg-danger badge-status">Exhausted</span>
                                @else
                                    <span class="badge bg-success badge-status">Active</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Concessionaire Billing -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card bg-light border-primary rounded-4 shadow-sm">
                <div class="card-body">
                    <h5>Water Bill Payments</h5>
                    <p class="mb-1">Paid: ₱{{ number_format($waterPayments, 2) }}</p>
                    <p class="text-danger mb-0">Overdue Amount: ₱{{ number_format($overdueWaterAmount, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-light border-warning rounded-4 shadow-sm">
                <div class="card-body">
                    <h5>Electricity Bill Payments</h5>
                    <p class="mb-1">Paid: ₱{{ number_format($electricityPayments, 2) }}</p>
                    <p class="text-danger mb-0">Overdue Amount: ₱{{ number_format($overdueElectricityAmount, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4 rounded-4 shadow-sm text-danger">
        <div class="card-body text-center">
            <h5>Total Overdue Concessionaire Bills</h5>
            <h3>₱{{ number_format($totalOverdueAmount, 2) }}</h3>
            <p>Total Payments (Water + Electricity): ₱{{ number_format($totalBillingPayments, 2) }}</p>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Revenue Chart
    const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctxRevenue, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                label: 'Monthly Collection',
                data: {!! json_encode($chartData) !!},
                borderColor: 'rgba(40,167,69,1)',
                backgroundColor: 'rgba(40,167,69,0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: true } },
            scales: { y: { beginAtZero: true } }
        }
    });

    // Top Fees Chart
    const ctxFee = document.getElementById('feeChart').getContext('2d');
    new Chart(ctxFee, {
        type: 'pie',
        data: {
            labels: {!! json_encode($topFees->pluck('fee_name')) !!},
            datasets: [{
                label: 'Top Fees',
                data: {!! json_encode($topFees->pluck('total')) !!},
                backgroundColor: [
                    '#007bff', '#ffc107', '#28a745', '#dc3545', '#6610f2', '#6c757d',
                    '#20c997', '#fd7e14', '#17a2b8', '#6f42c1'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });
</script>

@endsection
