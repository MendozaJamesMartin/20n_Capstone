@extends('layout.main')
@section('content')

<main style="background-image:url('/bgpup2.jpg'); background-repeat:no-repeat; background-size:cover; min-height: 85vh; padding: 5%;">
    <div class="container" style="width:50%">
        <div class="bg-light" style="padding:5%">
            <h1>Add Items to List of Fees</h1>
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
</main>

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