@extends('layout.main-master')
@section('content')

<main style="background-image: url('/bgpup4.jpg'); background-repeat: no-repeat; background-size: cover; min-height: 85vh; padding: 5%;">
    <div class="container" style="width:50%">

        <div>
            <table class="table table-striped border">
                <tr>
                    <th colspan="3">
                        <h1>List of Fees</h1>
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
                        <!-- Update Fee Button (Triggers Modal) -->
                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#updateFeeModal{{ $fee->id }}">Update</button>
                    </td>
                </tr>

                <!-- Update Fee Modal -->
                <div class="modal fade" id="updateFeeModal{{ $fee->id }}" tabindex="-1" aria-labelledby="updateFeeModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="updateFeeModalLabel">Update Fee</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form action="{{ route('UpdateFees', $fee->id) }}" method="POST">
                                    @csrf
                                    @method('POST')
                                    <div class="mb-3">
                                        <label for="fee_name" class="form-label">Fee Name</label>
                                        <input type="text" name="fee_name" class="form-control" value="{{ $fee->fee_name }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="amount" class="form-label">Amount</label>
                                        <input type="text" name="amount" class="form-control" value="{{ $fee->amount }}" required>
                                    </div>
                                    <button type="submit" class="btn btn-danger">Update Fee</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </table>
        </div>

    </div>
</main>

<!-- Add Fee Modal -->
<div class="modal fade" id="addFeeModal" tabindex="-1" aria-labelledby="addFeeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addFeeModalLabel">Add New Fee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('AddFees') }}" method="POST">
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