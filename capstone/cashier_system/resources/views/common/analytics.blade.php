@extends('layout.main-master')
@section('content')

<style>
    .card:hover {
        transform: scale(1.01);
    }

    .card {
        transition: transform 0.2s;
    }
</style>

<div class="container">
    <h2 class="mb-4">Cashier Analytics Dashboard</h2>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Total Revenue</h5>
                    <h3>₱{{ number_format($totalRevenue, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>This Month's Revenue</h5>
                    <h3>₱{{ number_format($monthlyRevenue, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5>Unpaid Amount</h5>
                    <h3>₱{{ number_format($unpaidRevenue, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Revenue Trend + Report Export -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Monthly Revenue Trend</h5>
                <!-- Button to open modal -->
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exportModal">
                    Export Report
                </button>
            </div>
            <canvas id="revenueChart" height="100"></canvas>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <!-- Top Fees Chart -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5>Top Fees (Pie Chart)</h5>
                    <canvas id="feeChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Fees List -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5>Top Fees Paid</h5>
                    <ul class="list-group">
                        @foreach ($topFees as $fee)
                            <li class="list-group-item d-flex justify-content-between">
                                {{ $fee->fee_name }}
                                <span class="badge bg-success">₱{{ number_format($fee->total, 2) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Concessionaire Billing -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card bg-light border-primary">
                <div class="card-body">
                    <h5>Water Bills Issued</h5>
                    <p>Total: ₱{{ number_format($waterBills, 2) }}</p>
                    <p>Paid: ₱{{ number_format($paidWater, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-light border-warning">
                <div class="card-body">
                    <h5>Electricity Bills Issued</h5>
                    <p>Total: ₱{{ number_format($electricityBills, 2) }}</p>
                    <p>Paid: ₱{{ number_format($paidElectricity, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body text-danger">
            <h5>Overdue Concessionaire Bills</h5>
            <h3>{{ $overdueBills }} overdue</h3>
        </div>
    </div>

    <!-- Export Modal -->
    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="GET" action="{{ route('reports.monthly.export') }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exportModalLabel">Export Custom Report</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        @php
                            $now = \Carbon\Carbon::now();
                            $startOfMonth = $now->copy()->startOfMonth()->toDateString();
                            $endOfMonth = $now->copy()->endOfMonth()->toDateString();
                        @endphp

                        <!-- Start Date -->
                        <div class="mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startOfMonth }}" required>
                        </div>

                        <!-- End Date -->
                        <div class="mb-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endOfMonth }}" required>
                        </div>

                        <!-- Fee Selection -->
                        <div class="mb-3">
                            <label for="fee_ids" class="form-label">Select Fees</label>
                            <select name="fee_ids[]" id="fee_ids" class="form-select" multiple required>
                                @foreach ($fees as $fee)
                                    <option value="{{ $fee->id }}" selected>{{ $fee->fee_name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple</small>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Export</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctxRevenue, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                label: 'Monthly Revenue',
                data: {!! json_encode($chartData) !!},
                borderColor: 'rgba(40,167,69,1)',
                backgroundColor: 'rgba(40,167,69,0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: { responsive: true }
    });

    const ctxFee = document.getElementById('feeChart').getContext('2d');
    new Chart(ctxFee, {
        type: 'pie',
        data: {
            labels: {!! json_encode($topFees->pluck('fee_name')) !!},
            datasets: [{
                label: 'Top Fees',
                data: {!! json_encode($topFees->pluck('total')) !!},
                backgroundColor: [
                    '#007bff', '#ffc107', '#28a745', '#dc3545', '#6610f2', '#6c757d'
                ]
            }]
        },
        options: {
            responsive: true
        }
    });
</script>

@endsection