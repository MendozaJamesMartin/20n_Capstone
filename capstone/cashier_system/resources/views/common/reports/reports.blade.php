@extends('layout.main-master')

@section('content')
<main class="flex-grow-1 p-4" style="min-height:85vh; background-color:#f5f5f5;">

    <div class="container" style="max-width:1200px;">

        <!-- FILTER CARD -->
        <div class="bg-white p-4 p-md-5 rounded-2 shadow-sm border mb-4">

            <h2 class="mb-4 fw-bold" style="color:#7b1113;">Filters</h2>

            <form method="GET" action="{{ route('reports.export') }}" class="row g-3 align-items-end">

                <div class="col-md-3">
                    <label for="start_date" class="form-label fw-semibold">Start Date</label>
                    <input type="date" id="start_date" name="start_date" class="form-control shadow-sm" required>
                </div>

                <div class="col-md-3">
                    <label for="end_date" class="form-label fw-semibold">End Date</label>
                    <input type="date" id="end_date" name="end_date" class="form-control shadow-sm" required>
                </div>

                <div class="col-md-3">
                    <select class="form-select" id="report_type" name="report_type">
                        <option value="transactions">Transactions Report</option>
                        <option value="cash_receipts">Cash Receipts Record</option>
                        <option value="accountability">Report of Accountability</option>
                        <option value="collections">Report of Collections</option>
                        <option value="deposits">Deposits Report</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="button" id="viewReport" class="btn btn-primary shadow-sm">
                        <i class="fas fa-eye me-1"></i> View
                    </button>
                    <button type="submit" class="btn btn-success shadow-sm">
                        <i class="fas fa-download me-1"></i> Download
                    </button>
                </div>

            </form>
        </div>

        <div class="table-responsive rounded-2 border shadow-sm">
            <table class="table table-bordered" id="reportsTable">
                <thead id="reportHead"></thead>
                <tbody id="reportBody">
                    <tr>
                        <td class="text-center text-muted p-5">
                            <i class="fas fa-file-alt fa-2x mb-3"></i>
                            <div>
                                Select filters and download the report.
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>

</main>

<script>
    document.getElementById('viewReport')
        .addEventListener('click', function() {

            const tbody =
                document.getElementById('reportBody');

            const thead =
                document.getElementById('reportHead');

            fetch(
                    "{{ route('reports.view') }}"
                )

                .then(res => res.json())

                .then(res => {

                    thead.innerHTML = '';

                    tbody.innerHTML = `
        <tr>
            <td class="text-center p-5">

                <i class="fas fa-file-excel fa-3x text-success mb-3"></i>

                <div class="fw-bold mb-2">
                    Report Preview Unavailable
                </div>

                <div class="text-muted">
                    ${res.message}
                </div>

            </td>
        </tr>
        `;

                })

                .catch(err => {

                    console.log(err);

                });

        });
</script>

@endsection