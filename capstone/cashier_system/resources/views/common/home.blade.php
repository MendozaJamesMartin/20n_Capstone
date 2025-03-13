@extends('layout.main-master')
@section('content')

<main>
    <!-- Hero Section -->
    <div class="hero">
        <h1>Welcome to PUP-T Cashier System</h1>
        <p>Secure, fast, and convenient payment solutions for students.</p>
        <a href="#quick-actions" class="btn btn-primary btn-lg mt-3">Get Started</a>
    </div>

    <!-- Quick Actions Section -->
    <div class="section" id="quick-actions">
        <div class="container">
            <h2 class="text-center mb-4">Quick Actions</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card text-center p-4">
                        <h4>Forms</h4>
                        <p>Settle tuition and other university fees conveniently.</p>
                        <a href="/insert-transaction-form" class="btn btn-success">Pay Now</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center p-4">
                        <h4>View List of Fees</h4>
                        <p>Access the list of fees available.</p>
                        <a href="fees/list" class="btn btn-info">View List of Fees</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center p-4">
                        <h4>View Data Analytics</h4>
                        <p>View Financial data analytics and charts</p>
                        <a href="items/item-list" class="btn btn-danger">View Data Analytics</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="section bg-light">
        <div class="container">
            <h2 class="text-center mb-4">Key Statistics</h2>
            <div class="row text-center">
                <div class="col-md-4">
                    <h3>Total Transactions</h3>
                    <p></p>
                </div>
                <div class="col-md-4">
                    <h3>Total Revenue</h3>
                    <p>₱</p>
                </div>
                <div class="col-md-4">
                    <h3>Pending Balances</h3>
                    <p>₱</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Announcements Section -->
    <div class="section">
        <div class="container">
            <h2 class="text-center mb-4">Announcements</h2>
            <ul class="list-group">
                <li class="list-group-item">Lorem ipsum dolor sit amet,</li>
                <li class="list-group-item">consectetur adipiscing elit,</li>
                <li class="list-group-item">sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</li>
            </ul>
        </div>
    </div>

</main>

<style>
        .hero {
            background-image: url('/bgpup3.jpg'); 
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
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
    
@endsection
