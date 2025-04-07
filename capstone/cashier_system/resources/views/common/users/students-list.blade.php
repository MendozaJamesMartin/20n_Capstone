@extends('layout.main-master')
@section('content')

<main style="background-image: url('/bgpup4.jpg'); background-repeat: no-repeat; background-size: cover; min-height: 85vh; padding: 5%;">

    <div class="container" style="width:50%">

        <div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th colspan="5">
                            <h2>REGISTERED STUDENTS LIST</h2>
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
                        <th>Student ID</th>
                        <th>First Name</th>
                        <th>Middle Name</th>
                        <th>Last Name</th>
                        <th>Suffix</th>
                    </tr>
                </tbody>
                <tbody>
                @forelse($students as $student)
                    <tr>
                        <td>{{ $student->student_id }}</td>
                        <td>{{ $student->first_name }}</td>
                        <td>{{ $student->middle_name }}</td>
                        <td>{{ $student->last_name }}</td>
                        <td>{{ $student->suffix }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No Students found</td>
                    </tr>
                @endforelse

                <!-- Add empty rows to fill up to 10 rows -->
                @for ($i = $student->count(); $i < 10; $i++)
                    <tr>
                        <td>&nbsp;</td>
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

@endsection