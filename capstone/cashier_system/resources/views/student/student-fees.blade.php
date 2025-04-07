@extends('layout.main-user')
@section('content')

<main style="background-image: url('/bgpup4.jpg'); background-repeat: no-repeat; background-size: cover; min-height: 85vh; padding: 5%;">
    <div class="container" style="width:50%">

        <div>
            <table class="table table-striped border">
                <tr>
                    <th colspan="3">
                        <h1>List of Fees</h1>
                    </th>
                </tr>
                <tr>
                    <th>Item ID</th>
                    <th>Item Name</th>
                    <th>Item Price</th>
                </tr>
                @foreach($fees as $fee)
                <tr>
                    <td>{{ $fee->id }}</td>
                    <td>{{ $fee->fee_name }}</td>
                    <td>{{ $fee->amount }}</td>
                </tr>
        </div>
        @endforeach
        </table>
    </div>

    </div>
</main>

@endsection