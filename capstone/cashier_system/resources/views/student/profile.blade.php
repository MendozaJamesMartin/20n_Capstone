@extends('layout.main-user')
@section('content')

<main style="background-image: url('/bgpup4.jpg'); background-repeat: no-repeat; background-size: cover; min-height: 85vh; padding: 5%;">
    <div class="container" style="width:50%">

        <div>
            <table class="table table-striped border">
                <tr>
                    <th colspan="4">
                        <h1>User Profile</h1>
                    </th>
                </tr>
                <tr>
                    <td>
                        <p> <strong>Email:</strong> {{ $user->email }}</p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p> <strong>First Name:</strong> {{ $student->first_name }}</p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p> <strong>Middle Name:</strong> {{ $student->middle_name }}</p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p> <strong>Last Name:</strong> {{ $student->last_name }}</p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p> <strong>Suffix:</strong> {{ $student->suffix }}</p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p> <strong>Password:</strong> ******** </p>
                    </td>
                </tr>
            </table>
        </div>

    </div>
</main>

@endsection