@extends('layout.main-master')
@section('content')

<main style="background-image: url('/bgpup4.jpg'); background-repeat: no-repeat; background-size: cover; min-height: 85vh; padding: 5%;">

    <div class="container" style="width:50%">

        <div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th colspan="3">
                            <h2>CONCESSIONAIRES LIST</h2>
                        </th>
                        <th>
                            <button class="btn btn-danger btn-lg" data-bs-toggle="modal" data-bs-target="#addConcessionaireModal">New</button>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th colspan="4">
                        <div>
                        <form action="{{ url()->current() }}" method="GET">

                            <input type="text" id="search" class="border p-2 w-1/3 rounded" placeholder="🔍 Search concessionaires..." onkeyup="filterTable()">

                                <label class="block mb-2">Sort By</label>
                                <select name="sort_by" class="w-full p-2 border rounded mb-4">
                                    <option value="transaction_date" {{ request('sort_by') == 'id' ? 'selected' : '' }}>ID</option>
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
                        <th>Action</th>
                    </tr>
                </tbody>
                <tbody>
                @forelse($concessionaires as $concessionaire)
                    <tr>
                        <td>{{ $concessionaire->id }}</td>
                        <td>{{ $concessionaire->name }}</td>
                        <td>{{ $concessionaire->contact }}</td>
                        <td>
                            <a href="#" class="btn btn-info btn-sm btn-danger">View Details</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No Concessionaires found</td>
                    </tr>
                @endforelse

                <!-- Add empty rows to fill up to 10 rows -->
                @for ($i = $concessionaires->count(); $i < 10; $i++)
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                @endfor
                </tbody>
            </table>
        </div>

    </div>

    </div>

</main>

<!-- Add Fee Modal -->
<div class="modal fade" id="addConcessionaireModal" tabindex="-1" aria-labelledby="addConcessionaireModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addConcessionaireModalLabel">Add New Concessionaire</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('AddConcessionaires') }}" method="POST">
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