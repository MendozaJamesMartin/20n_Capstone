@extends('layout.main-user')
@section('content')

<div class="container py-5">
    <div class="text-center mb-5">
        <h2>Student Payment Portal</h2>
        <p class="text-muted">Submit payments or view your payment history below.</p>
    </div>

    <!-- Optional: Student ID or Name Search -->
    <form method="GET" action="#" class="mb-4">
        <div class="input-group">
            <input type="text" name="student_id" class="form-control" placeholder="Enter Student ID or Name">
            <button class="btn btn-outline-primary" type="submit">Search</button>
        </div>
    </form>

    <div class="row text-center">
        <!-- Submit Payment -->
        <div class="col-md-6 mb-3">
            <a href="{{ route('student.payment.form') }}" class="text-decoration-none text-reset">
                <div class="card bg-primary text-white shadow h-100">
                    <div class="card-body py-4">
                        <h5 class="card-title">Submit a Payment</h5>
                        <p class="card-text small">Pay for student records, ID, and more.</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- View History -->
        <div class="col-md-6 mb-3">
            <a href="#" class="text-decoration-none text-reset">
                <div class="card bg-info text-white shadow h-100">
                    <div class="card-body py-4">
                        <h5 class="card-title">List of Student Fees</h5>
                        <p class="card-text small">Check the list of all available student fees.</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

@endsection