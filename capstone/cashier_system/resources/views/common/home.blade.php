@extends('layout.main-user')
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
                    <a href=" {{ route('student.dashboard') }} " class="text-decoration-none text-reset">
                        <div class="card text-center p-4 bg-danger">
                            <h4>Students</h4>
                            <p>Students go here</p>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href=" {{ route('login') }} " class="text-decoration-none text-reset">
                        <div class="card text-center p-4 bg-primary">
                            <h4>Cashier Personnel</h4>
                            <p>Cashier personnel go here</p>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="#" class="text-decoration-none text-reset">
                        <div class="card text-center p-4 bg-warning">
                            <h4>Concessionaires</h4>
                            <p>Settle tuition and other university fees conveniently.</p>
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
