@extends('layout.main-master')
@section('content')

<main style="background-image: url('/bgpup4.jpg'); background-repeat: no-repeat; background-size: cover; min-height: 85vh; padding: 5%;">

    <div class="container" style="width:50%">

        <div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th colspan="5">
                            <h2>REGISTERED USERS</h2>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th colspan="5">
                            <div>
                                <form action="{{ url()->current() }}" method="GET">

                                    <input type="text" id="search" class="border p-2 w-1/3 rounded" placeholder="🔍 Search students..." onkeyup="filterTable()">

                                    <label class="block mb-2">Sort By</label>
                                    <select name="sort_by" class="w-full p-2 border rounded mb-4">
                                        <option value="id" {{ request('sort_by') == 'id' ? 'selected' : '' }}>ID</option>
                                        <option value="entity_name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Name</option>
                                    </select>

                                    <!-- Hidden Input for Sorting Order -->
                                    <input type="hidden" name="sort_order" id="sortOrderInput" value="asc">

                                    <button type="submit" class="btn btn-danger">Apply</button>
                                    <a href="{{ url()->current() }}" class="btn btn-danger">Reset</a>
                                </form>

                            </div>
                        </th>
                    </tr>
                    <tr>
                        <th>ID</th>
                        <th>Email</th>
                        <th>Password</th>
                        <th>Role</th>
                        <th>Action</th>
                    </tr>
                </tbody>
                <tbody>
                    @foreach($users as $user)
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

                    <!-- Update User Modal -->
                    <div class="modal fade" id="updateUserModal{{ $user->id }}" tabindex="-1" aria-labelledby="updateUserModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="updateUserModalLabel">Update User</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="{{ route('users.update', $user->id) }}" method="POST">
                                        @csrf
                                        @method('POST')

                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="text" name="email" class="form-control" value="{{ $user->email }}" disabled>
                                        </div>

                                        <div class="mb-3">
                                            <label for="role" class="form-label">Role</label>
                                            <select class="form-select" name="role" class="form-control" value="{{ $user->role }}" required aria-label="Default select example">
                                                <option value="Superadmin">Superadmin</option>
                                                <option value="Admin">Admin</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-danger">Update User Role</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    @endforeach

                </tbody>


            </table>
        </div>

    </div>

</main>

@endsection