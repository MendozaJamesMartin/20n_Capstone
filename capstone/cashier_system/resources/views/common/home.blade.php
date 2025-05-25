@extends('layout.main-user')
@section('content')

<main>
    <!-- Hero Section -->
    <div class="hero">
        <h1>Welcome to PUP-T Cashier System</h1>
        <p>Secure, fast, and convenient payment solutions for students.</p>
    </div>

    <!-- Quick Actions Section -->
    <div class="section" id="quick-actions">
        <div class="container">
            <h2 class="text-center mb-4">Quick Actions</h2>
            <div class="row g-4">
                <div class="col-md-6">
                    <a href=" {{ route('student.payment.form') }} " class="text-decoration-none text-reset">
                        <div class="card text-center p-4 bg-danger text-light">
                            <h4>Students</h4>
                            <p>Access Student Payment Form</p>
                        </div>
                    </a>
                </div>
                <div class="col-md-6">
                    <a href=" {{ route('login') }} " class="text-decoration-none text-reset">
                        <div class="card text-center p-4 bg-primary text-light">
                            <h4>Cashier Personnel</h4>
                            <p>Cashier personnel go here</p>
                        </div>
                    </a>
                </div>
            </div>
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
            transition: transform 1;
        }
        .card:hover {
        transform: scale(1.05);
        }
    </style>
    
@endsection
