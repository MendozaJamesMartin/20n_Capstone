@extends('layout.main-master')
@section('content')

<main style="background-image: url('/bgpup3.jpg'); background-repeat: no-repeat; background-size: cover; min-height: 100vh;">
    <div class="flex-grow-1 p-4">

        <!-- Hero Section -->
        <div class="hero text-white position-relative mb-5">
            <div class="overlay"></div>
            <div class="content position-relative">
                <h1 class="fw-bold">Welcome to PUP-T Cashier System</h1>
                <p class="lead">Secure, fast, and convenient payment solutions for students.</p>
                <a href="#quick-actions" class="btn btn-lg btn-light shadow-sm mt-3">🚀 Get Started</a>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="row g-3 mb-5" id="quick-actions">
            <div class="col-md-3">
                <div class="card shadow-sm kpi-card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-wrapper bg-success text-white me-3">
                            <i class="bi bi-cash-stack fs-3"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-1">Today’s Collection</p>
                            <h4 class="fw-bold text-success">₱{{ number_format($todaysRevenue, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <a href="{{ route('payments.pending') }}" class="text-decoration-none text-reset">
                    <div class="card shadow-sm kpi-card h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-wrapper bg-warning text-white me-3">
                                <i class="bi bi-hourglass-split fs-3"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-1">Pending Payments</p>
                                <h4 class="fw-bold">{{ $unpaidCount }}</h4>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('receipts.list', ['timeframe' => 'today', 'show' => 'active']) }}" class="text-decoration-none text-reset">
                    <div class="card shadow-sm kpi-card h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-wrapper bg-primary text-white me-3">
                                <i class="bi bi-receipt fs-3"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-1">Transactions Today</p>
                                <h4 class="fw-bold">{{ $paidCount }}</h4>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('payments.pending') }}" class="text-decoration-none text-reset">
                    <div class="card shadow-sm kpi-card h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-wrapper bg-danger text-white me-3">
                                <i class="bi bi-exclamation-triangle fs-3"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-1">Bills Due This Week</p>
                                <h4 class="fw-bold text-danger">{{ $billsDue }}</h4>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Tables Section -->
        <div class="row g-4">
            <!-- Recent Payments -->
            <div class="col-lg-6">
                <a href="{{ route('receipts.list') }}" class="text-decoration-none text-reset">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="fw-bold mb-3">💳 Recent Payments</h5>
                            <div class="table-responsive">
                                <table class="table table-hover table-sm align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Customer</th>
                                            <th class="text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recentPayments as $payment)
                                            <tr>
                                                <td>{{ $payment->receipt_print_date }}</td>
                                                <td>{{ $payment->customer_name }}</td>
                                                <td class="text-end">₱{{ number_format($payment->total_amount, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted">No recent payments.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Current Receipt Batch -->
            <div class="col-lg-6">
                <a href="{{ route('receipts.manage') }}" class="text-decoration-none text-reset">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="fw-bold mb-3">🧾 Current Receipt Batch</h5>
                            @if ($currentBatch)
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item"><strong>Start Number:</strong> {{ $currentBatch->start_number ?? 0 }}</li>
                                    <li class="list-group-item"><strong>Current Receipt:</strong> {{ $currentBatch->display_next_number }}</li>
                                    <li class="list-group-item"><strong>End Number:</strong> {{ $currentBatch->end_number ?? 0 }}</li>
                                    <li class="list-group-item"><strong>Receipts Used:</strong> {{ $currentBatch->used_count }}</li>
                                    <li class="list-group-item"><strong>Receipts Left:</strong> {{ $currentBatch->remaining_count }}</li>
                                </ul>
                            @else
                                <div class="alert alert-warning mb-0">⚠️ No active receipt batch available.</div>
                            @endif
                        </div>
                    </div>
                </a>
            </div>
        </div>

    </div>
</main>

<style>
    .hero {
        background-image: url('/bgpup2.jpg');
        background-size: cover;
        background-position: center;
        padding: 100px 20px;
        border-radius: 12px;
        position: relative;
    }

    .hero .overlay {
        background: rgba(0,0,0,0.5);
        position: absolute;
        top:0; left:0; right:0; bottom:0;
        border-radius: 12px;
    }

    .hero .content {
        position: relative;
        z-index: 1;
    }

    .kpi-card {
        transition: transform .2s ease, box-shadow .2s ease;
        border-left: 4px solid #0d6efd;
    }

    .kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 18px rgba(0,0,0,0.1);
    }

    .icon-wrapper {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        flex-shrink: 0;
    }
</style>

@endsection
