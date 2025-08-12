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
            <h1 class="mb-0">List of Fees</h1>
            <div class="d-flex gap-2">
                <a href="{{ route('fees.list.deleted') }}" class="btn btn-danger">🗑 Deleted Fees</a>
                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#addFeeModal">➕ Add Item</button>
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
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#updateFeeModal{{ $fee->id }}">Update</button>
                            <a href="{{ route('fees.delete', $fee->id) }}" class="btn btn-warning btn-sm">Delete</a>
                        </div>
                    </td>
                </tr>

                    <!-- Update Fee Modal -->
                    <div class="modal fade" id="updateFeeModal{{ $fee->id }}" tabindex="-1" aria-labelledby="updateFeeModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Update Fee</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="{{ route('fees.update', $fee->id) }}" method="POST">
                                        @csrf
                                        @method('POST')
                                        <div class="mb-3">
                                            <label class="form-label">Fee Name</label>
                                            <input type="text" name="fee_name" class="form-control" value="{{ $fee->fee_name }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Amount</label>
                                            <input type="text" name="amount" class="form-control" value="{{ $fee->amount }}" required>
                                        </div>
                                        <div class="mb-3 form-check">
                                            <small class="text-muted">Set amount to 0 if this fee has variable amount</small>
                                        </div>
                                        <button type="submit" class="btn btn-danger">Update Fee</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </tbody>
        </table>
    </div>
</main>

<!-- Add Fee Modal -->
<div class="modal fade" id="addFeeModal" tabindex="-1" aria-labelledby="addFeeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Fee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('fees.add') }}" method="POST">
                    @csrf
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Item Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="itemTableBody">
                            <tr>
                                <td>
                                    <input type="text" class="form-control" name="fees[0][fee_name]" placeholder="Item Name" required>
                                </td>
                                <td>
                                    <input type="text" class="form-control" name="fees[0][amount]" placeholder="00.00" required>
                                </td>
                                <small class="text-muted">Set amount to 0 if this fee has variable amount</small>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm remove-row">Remove</button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-center">
                                    <button type="button" class="btn btn-warning btn-md" id="addRow">Add Row</button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                    <button type="submit" class="btn btn-danger btn-md">Add Items</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tableBody = document.getElementById('itemTableBody');
        const addRowButton = document.getElementById('addRow');
        let rowIndex = 1;

        addRowButton.addEventListener('click', () => {
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td>
                    <input type="text" class="form-control" name="fees[${rowIndex}][fee_name]" placeholder="Fee Name" required>
                </td>
                <td>
                    <input type="text" class="form-control" name="fees[${rowIndex}][amount]" placeholder="00.00" required>
                </td>
                <td class="text-center">
                    <input type="checkbox" class="form-check-input" name="fees[${rowIndex}][is_variable]" value="1">
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-row">Remove</button>
                </td>
            `;
            tableBody.appendChild(newRow);
            rowIndex++;
        });

        tableBody.addEventListener('click', (e) => {
            if (e.target.classList.contains('remove-row')) {
                e.target.closest('tr').remove();
            }
        });
    });
</script>

@endsection
