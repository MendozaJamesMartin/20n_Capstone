@extends('layout.main-master')
@section('content')

<main style="background-image: url('/bgpup4.jpg'); background-repeat: no-repeat; background-size: cover; min-height: 85vh; padding: 5%;">

    <div class="container" style="width:50%">

        <div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th colspan="5">
                            <h2>USER INFORMATION</h2>
                        </th>
                    </tr>
                </thead>

                </tbody>
                <tbody>
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->email }}</td>
                        <td>********</td>
                        <td>{{ $user->role }}</td>
                        <td>
                            <!-- Update User Button (Triggers Modal) -->
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#updateUserModal{{ $user->id }}">Update</button>
                        </td>
                    </tr>
                </tbody>


            </table>
        </div>

    </div>

</main>

@endsection