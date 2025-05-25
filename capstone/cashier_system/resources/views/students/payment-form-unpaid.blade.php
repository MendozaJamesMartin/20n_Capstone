@extends('layout.main-master')

@section('content')

<main class="py-4 py-md-5" style="background-image: url('/bgpup3.jpg'); background-repeat: no-repeat; background-size: cover; min-height: 85vh;">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <div class="bg-light p-4 p-md-5 rounded shadow">
                    <h1 class="h3 h2-md">Student Payment Form</h1>

                    @if(session('success'))
                    <p style="color: green;">{{ session('success') }}</p>
                    @elseif(session('error'))
                    <p style="color: red;">{{ session('error') }}</p>
                    @endif

                    <form method="POST" action="{{ route('student.payment.form') }}" id="paymentForm">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="student_id" class="form-label">Student ID</label>
                                    <input type="student_id" class="form-control" id="student_id" name="student_id" placeholder="XXXX-XXXXX-XX-X">
                                </div>
                                <label for="student_details" class="form-label">Student Full Name</label>
                                <div class="row g-2">
                                    <div class="col-12 col-md">
                                        <input type="text" class="form-control" id="first_name" name="first_name" placeholder="First Name">
                                    </div>
                                    <div class="col-12 col-md">
                                        <input type="text" class="form-control" id="middle_name" name="middle_name" placeholder="Middle Name">
                                    </div>
                                    <div class="col-12 col-md">
                                        <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Last Name">
                                    </div>
                                    <div class="col-12 col-md">
                                        <input type="text" class="form-control" id="suffix" name="suffix" placeholder="Suffix">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Fees Table -->
                        <table class="table">
                            <tr>
                                <th colspan="3">
                                    <h3>Select Fees</h3>
                                </th>
                            </tr>
                        </table>

                        <div class="table-responsive" style="max-height: 220px; overflow-y: auto; border: 1px solid #ccc;">
                            <table class="table table-secondary table-striped">
                                <thead style="position: sticky; top: 0; background-color: #f8f9fa; z-index: 1;">
                                    <tr>
                                        <th>Fee Name</th>
                                        <th>Amount</th>
                                        <th>Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($fees as $fee)
                                    <tr>
                                        <td>{{ $fee->fee_name }}</td>
                                        <td>{{ number_format($fee->amount, 2) }}</td>
                                        <td>
                                            <input type="number" class="form-control quantity-input"
                                                name="quantities[{{ $fee->id }}]"
                                                data-price="{{ $fee->amount }}" min="0" value="0">
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <h3>Total Amount: <span id="total-amount">0.00</span></h3>
                        </div>

                        <button class="btn btn-danger btn-lg w-100 w-md-auto mt-3" type="submit">Submit</button>
                    </form>

                </div>
            </div>
        </div>
    </div>

</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const quantityInputs = document.querySelectorAll('.quantity-input');
        const totalAmountElement = document.getElementById('total-amount');

        quantityInputs.forEach(input => {
            input.addEventListener('input', calculateTotal);
        });

        function calculateTotal() {
            let total = 0;

            quantityInputs.forEach(input => {
                const price = parseFloat(input.dataset.price);
                const quantity = parseFloat(input.value) || 0;

                total += price * quantity;
            });

            totalAmountElement.textContent = total.toFixed(2);
        }
    });
</script>

@endsection