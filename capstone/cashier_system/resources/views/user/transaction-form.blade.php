@extends('layout.main-user')

@section('content')

<main style="background-image:url('/bgpup3.jpg'); background-repeat:no-repeat; background-size:cover; min-height: 85vh; padding: 5%;">

    <div class="container" style="width:75%">
        <div class="bg-light" style="padding:5%">
            <h1>Student Payment Form</h1>

            @if(session('success'))
            <p style="color: green;">{{ session('success') }}</p>
            @elseif(session('error'))
            <p style="color: red;">{{ session('error') }}</p>
            @endif

            <form method="POST" action="{{ route('NewStudentTransaction') }}">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="student_id">Select Student:</label>
                        <select class="form-control" name="student_id" id="student_id" required>
                            <option value="">-- Select Student --</option>
                            @foreach($students as $student)
                            <option value="{{ $student->id }}">
                                {{ $student->first_name }}
                                {{ $student->middle_name ? $student->middle_name . ' ' : '' }}
                                {{ $student->last_name }}
                                {{ $student->suffix ? $student->suffix : '' }}
                            </option>
                            @endforeach
                        </select>
                        @error('student_id') <p style="color: red;">{{ $message }}</p> @enderror
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

                <div style="max-height: 220px; overflow-y: scroll; border: 1px solid #ccc;">
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

                <div style="padding: 2%">
                    <button class="btn btn-danger btn-lg" type="submit">Submit</button>
                </div>
            </form>

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