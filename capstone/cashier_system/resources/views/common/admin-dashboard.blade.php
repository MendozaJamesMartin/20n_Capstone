@extends('layout.main-master')
@section('content')

<main style="background-image: url('/bgpup3.jpg'); background-repeat: no-repeat; background-size: cover;">
    <!-- Main Content -->
    <div class="flex-grow-1 p-4">

        <!-- Hero Section -->
        <div class="hero">
            <h1 class="text-white">Welcome to PUP-T Cashier System</h1>
            <p class="text-white">Secure, fast, and convenient payment solutions for students.</p>
            <a href="#quick-actions" class="btn btn-primary btn-lg mt-3">Get Started</a>
        </div>

        <div class="section">

        <!-- KPI Cards -->
        <div class="row mb-4 section" id="quick-actions">
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1">Today’s Revenue</p>
                        <h4>₱{{ number_format($todaysRevenue, 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <a href=" {{ route('payments.pending') }}" class="text-decoration-none text-reset">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <p class="text-muted mb-1">Pending Payments</p>
                            <h4>{{ $unpaidCount }}</h4>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href=" {{ route('receipts.list') }}" class="text-decoration-none text-reset">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <p class="text-muted mb-1">Transactions Conducted Today</p>
                            <h4>{{ $paidCount }}</h4>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href=" {{ route('payments.pending') }}" class="text-decoration-none text-reset">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <p class="text-muted mb-1">Bills Due this week</p>
                            <h4>{{ $billsDue }}</h4>
                        </div>
                    </div>
            </div>
        </div>

        <!-- Chart Placeholder -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title">Revenue Trends</h5>
                <div class="bg-light border rounded" style="height: 200px;">[Chart goes here]</div>
            </div>
        </div>

        <!-- Tables -->
        <div class="row">
            <div class="col-lg-6 mb-4">
                <a href=" {{ route('receipts.list') }}" class="text-decoration-none text-reset">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Recent Payments</h5>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentPayments as $payment)
                                    <tr>
                                        <td>{{ $payment->receipt_print_date }}</td>
                                        <td>{{ $payment->customer_name }}</td>
                                        <td>₱{{ number_format($payment->total_amount, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-lg-6 mb-4">
                <a href=" {{ route('payments.pending') }}" class="text-decoration-none text-reset">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">List of Bills Due</h5>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Due Date</th>
                                        <th>Concessionaire</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pendingPayments as $pendingPayment)
                                    <tr>
                                        <td>{{ $pendingPayment->due_date }}</td>
                                        <td>{{ $pendingPayment->concessionaire_name }}</td>
                                        <td>₱{{ number_format($pendingPayment->balance_due, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        </div>
        
</main>

<style>
    .card:hover {
        transform: scale(1.05);
    }

    .card {
        transition: transform 1;
    }

    .hero {
        background-image: url('/bgpup2.jpg');
        background-repeat: no-repeat;
        background-size: cover;
        text-align: center;
        padding: 80px 20px;
    }

    .hero h1 {
        font-size: 3rem;
    }

    .section {
        padding: 40px 20px;
    }
</style>

@endsection