@extends('layout.main-master')
@section('content')

<main style="background-image: url('/bgpup4.jpg'); background-repeat: no-repeat; background-size: cover; min-height: 85vh; padding: 5%;">
    <div class="container" style="width:50%">

        <div>
            <table class="table table-striped border">
                <tr>
                    <th colspan="3">
                        <h1>List of Retired Fees</h1>
                    </th>
                    <th>
                        <!-- Add Fee Button (Triggers Modal) -->
                        <button class="btn btn-danger btn-lg" data-bs-toggle="modal" data-bs-target="#addFeeModal">Add Item</button>
                    </th>
                </tr>
                <tr>
                    <th>Item ID</th>
                    <th>Item Name</th>
                    <th>Item Price</th>
                    <th>Actions</th>
                </tr>
                @foreach($fees as $fee)
                <tr>
                    <td>{{ $fee->id }}</td>
                    <td>{{ $fee->fee_name }}</td>
                    <td>{{ $fee->amount }}</td>
                    <td>
                        <div class="d-flex gap-2">
                            <!-- Update Fee Button (Triggers Modal) -->
                            <a href=" {{ route('fees.restore', $fee->id) }} " class="btn btn-warning btn-sm">Restore</a>
                        </div>
                    </td>
                </tr>

                @endforeach
            </table>
        </div>

    </div>
</main>

@endsection