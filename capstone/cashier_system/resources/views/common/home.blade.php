@extends('layout.main-user')
@section('content')

<main style="min-height: 85vh; background: #f5f5f5;">

    <!-- HERO -->
    <div class="hero-modern d-flex flex-column justify-content-center align-items-center text-center">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1 class="hero-title">Welcome to PUP-T Cashier System</h1>
            <p class="hero-subtitle">Secure, fast, and convenient payment solutions for students.</p>
        </div>
    </div>

    <!-- QUICK ACTIONS -->
    <section class="quick-section">
        <div class="container">
            <h2 class="section-title text-center mb-4">Quick Actions</h2>

            <div class="row g-4">
                <!-- Students Card -->
                <div class="col-md-6">
                    <a href="{{ route('student.payment.form') }}" class="text-decoration-none">
                        <div class="quick-card">
                            <i class="bi bi-person-fill icon"></i>
                            <h4 class="text-white">Students</h4>
                            <p class="text-white mb-0">Access Student Payment Form</p>
                        </div>
                    </a>
                </div>

                <!-- Cashier Card -->
                <div class="col-md-6">
                    <a href="{{ route('login') }}" class="text-decoration-none">
                        <div class="quick-card bg-alt">
                            <i class="bi bi-shield-lock-fill icon"></i>
                            <h4 class="text-white">Cashier Personnel</h4>
                            <p class="text-white mb-0">Cashier personnel go here</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

</main>

<style>
    /* -----------------------------
            HERO (Modern Theme)
    ------------------------------ */
    .hero-modern {
        position: relative;
        height: 300px;
        background-image: url('/bgpup3.jpg');
        background-size: cover;
        background-position: center;
        border-bottom: 4px solid #7a0a0a;
    }

    .hero-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.45);
    }

    .hero-content {
        position: relative;
        z-index: 2;
        color: white;
        padding: 20px;
    }

    .hero-title {
        font-size: 2.7rem;
        font-weight: 700;
        letter-spacing: 0.5px;
    }

    .hero-subtitle {
        font-size: 1.15rem;
        opacity: 0.9;
    }

    /* -----------------------------
           QUICK ACTIONS SECTION
    ------------------------------ */
    .quick-section {
        padding: 50px 0;
    }

    .section-title {
        font-weight: 700;
        color: #7a0a0a;
    }

    /* -----------------------------
                CARDS
    ------------------------------ */
    .quick-card {
        background: #7a0a0a;
        padding: 35px 25px;
        border-radius: 18px;
        text-align: center;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        transition: transform .25s ease, box-shadow .25s ease;
        color: white;
    }

    .quick-card.bg-alt {
        background: #5a0000;
    }

    .quick-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 8px 18px rgba(0,0,0,0.25);
    }

    .icon {
        font-size: 2.4rem;
        margin-bottom: 10px;
        color: rgba(255,255,255,0.85);
    }
</style>

@endsection
