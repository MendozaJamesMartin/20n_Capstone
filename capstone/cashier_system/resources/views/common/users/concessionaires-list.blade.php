@extends('layout.main-master')
@section('content')

<main style="background-image: url('/bgpup4.jpg'); background-repeat: no-repeat; background-size: cover; min-height: 85vh; padding: 5%;">

    <div class="container" style="width:50%">

        <div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th colspan="4">
                            <h2>CONCESSIONAIRES LIST</h2>
                        </th>
                        <th>
                            <button class="btn btn-danger btn-lg" data-bs-toggle="modal" data-bs-target="#addConcessionaireModal">New</button>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th colspan="5">
                        <div>
                        <form action="{{ url()->current() }}" method="GET">

                            <input type="text" id="search" class="border p-2 w-1/3 rounded" placeholder="🔍 Search concessionaires..." onkeyup="filterTable()">

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
                        <th>Concessionaire ID</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </tbody>
                <tbody>
                @foreach($concessionaires as $concessionaire)
                    <tr>
                        <td>{{ $concessionaire->id }}</td>
                        <td>{{ $concessionaire->name }}</td>
                        <td>{{ $concessionaire->contact }}</td>
                        <td>{{ $concessionaire->status }}</td>
                        <td>
                            <!-- Update Fee Button (Triggers Modal) -->
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#updateConcessionaireModal{{ $concessionaire->id }}">Update</button>
                        </td>
                    </tr>

                <!-- Update Concessionaire Modal -->
                <div class="modal fade" id="updateConcessionaireModal{{ $concessionaire->id }}" tabindex="-1" aria-labelledby="updateConcessionaireModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="updateConcessionaireModalLabel">Update Concessionaire</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form action="{{ route('concessionaires.update', $concessionaire->id) }}" method="POST">
                                    @csrf
                                    @method('POST')
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Name</label>
                                        <input type="text" name="name" class="form-control" value="{{ $concessionaire->name }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="contact" class="form-label">Contact</label>
                                        <input type="text" name="contact" class="form-control" value="{{ $concessionaire->contact }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" name="status" value="{{ $concessionaire->status }}" aria-label="Default select example">
                                            <option value="Active">Active</option>
                                            <option value="Inactive">Inactive</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-danger">Save</button>
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

<!-- Add Concessionaire Modal -->
<div class="modal fade" id="addConcessionaireModal" tabindex="-1" aria-labelledby="addConcessionaireModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addConcessionaireModalLabel">Add New Concessionaire</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('concessionaires.add') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Concessionaire Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="contact" class="form-label">Contact No.</label>
                        <input type="text" name="contact" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Concessionaire</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection