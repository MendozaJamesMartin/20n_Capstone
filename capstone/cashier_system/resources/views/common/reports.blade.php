@extends('layout.main-master')

@section('content')
<main class="flex-grow-1 p-4" style="min-height:85vh; background-color:#f5f5f5;">

    <div class="container" style="max-width:1200px;">

        <!-- FILTER CARD -->
        <div class="bg-white p-4 p-md-5 rounded-2 shadow-sm border mb-4">

            <h2 class="mb-4 fw-bold" style="color:#7b1113;">Filters</h2>

            <form method="GET" action="{{ route('reports.monthly.export') }}" class="row g-3 align-items-end">

                <div class="col-md-3">
                    <label for="start_date" class="form-label fw-semibold">Start Date</label>
                    <input type="date" id="start_date" name="start_date" class="form-control shadow-sm" required>
                </div>

                <div class="col-md-3">
                    <label for="end_date" class="form-label fw-semibold">End Date</label>
                    <input type="date" id="end_date" name="end_date" class="form-control shadow-sm" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Fees</label>
                    <button type="button" class="btn btn-outline-secondary w-100 shadow-sm text-start" data-bs-toggle="modal" data-bs-target="#feesModal" disabled>
                        Select Fees
                    </button>
                    <input type="hidden" id="fees" name="fees[]">
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="button" id="viewReport" class="btn btn-primary w-100 shadow-sm">
                        <i class="fas fa-eye me-1"></i> View
                    </button>
                    <button type="submit" class="btn btn-success w-100 shadow-sm">
                        <i class="fas fa-download me-1"></i> Download
                    </button>
                </div>

            </form>
        </div>

        <!-- REPORT TABLE CARD -->
        <div class="bg-white p-4 p-md-5 rounded-2 shadow-sm border">

            <h2 class="mb-4 fw-bold" style="color:#7b1113;">Report</h2>

            <div class="table-responsive rounded-2 border shadow-sm">
                <table class="table table-hover table-bordered" id="reportsTable">
                    <thead class="table-light text-center">
                        <tr>
                            <th width="10%">DATE</th>
                            <th width="15%">OFFICIAL RECEIPT NO.</th>
                            <th width="25%">PAYOR NAME</th>
                            <th >FEES</th>
                            <th width="10%">COLLECTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5" class="text-center text-muted">Use the filters above to view the report.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>

    </div>

    <!-- Fees Modal -->
    <div class="modal fade" id="feesModal" tabindex="-1" aria-labelledby="feesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="feesModalLabel">Select Fees</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @foreach($fees as $fee)
                    <div class="form-check">
                        <input class="form-check-input fee-checkbox" type="checkbox" value="{{ $fee->id }}" id="fee{{ $fee->id }}">
                        <label class="form-check-label" for="fee{{ $fee->id }}">{{ $fee->fee_name }}</label>
                    </div>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary shadow-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary shadow-sm" id="saveFeesSelection">Save</button>
                </div>
            </div>
        </div>
    </div>

</main>

<script>
document.getElementById('viewReport').addEventListener('click', function() {
    const startDate = document.getElementById('start_date').value;
    const endDate   = document.getElementById('end_date').value;
    const feeIds    = Array.from(document.querySelectorAll('.fee-checkbox:checked')).map(cb => cb.value);

    const params = new URLSearchParams();
    params.append('start_date', startDate);
    params.append('end_date', endDate);
    feeIds.forEach(id => params.append('fees[]', id));

    fetch("{{ route('reports.view') }}?" + params.toString())
        .then(res => res.json())
        .then(res => {
            const tableBody = document.querySelector('#reportsTable tbody');
            tableBody.innerHTML = '';

            res.data.forEach(row => {
                const tr = document.createElement('tr');

                if (row.length === 1) {
                    const td = document.createElement('td');
                    td.colSpan = 5;
                    td.innerHTML = `<strong>${row[0]}</strong>`;
                    tr.appendChild(td);
                } else {
                    row.forEach(col => {
                        const td = document.createElement('td');
                        td.textContent = col;
                        td.style.textAlign = 'center';
                        tr.appendChild(td);
                    });
                }

                tableBody.appendChild(tr);
            });
        })
        .catch(err => console.error(err));
});

</script>

@endsection
