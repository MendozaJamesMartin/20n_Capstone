@extends('layout.main-master')
@section('content')

<main style="background-image: url('/bgpup4.jpg'); background-repeat: no-repeat; background-size: cover; min-height: 85vh; padding: 5%;">
    <div class="container bg-light" style="width:50%; padding:2%;">

        @if(session('success'))
            <p style="color: green;">{{ session('success') }}</p>
        @elseif(session('error'))
            <p style="color: red;">{{ session('error') }}</p>
        @endif

        <div class="mb-3 d-flex justify-content-between align-items-center">
            <h1 class="mb-0">List of Deleted Fees</h1>
            <div class="d-flex gap-2">
                <a href="{{ route('fees.list') }}" class="btn btn-danger">View Active Fees</a>
            </div>
        </div>

        <table class="table table-striped border">
            <thead>
                <tr>
                    <th>Item ID</th>
                    <th>Item Name</th>
                    <th>Item Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($fees as $fee)
                <tr>
                    <td>{{ $fee->id }}</td>
                    <td>{{ $fee->fee_name }}</td>
                    <td>{{ $fee->amount }}</td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href=" {{ route('fees.restore', $fee->id) }} " class="btn btn-warning btn-sm">Restore</a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</main>

@endsection
