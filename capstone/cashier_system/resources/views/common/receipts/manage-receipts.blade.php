@extends('layout.main-master')

@section('content')

<main style="min-height: 85vh; padding: 3%;
    background: linear-gradient(135deg, #eef2f7, #f8f9fc);">

    <div class="container mt-4" style="animation: fadeIn 0.4s ease;">

        <!-- Page Title -->
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <h3 class="fw-bold mb-0">Receipt Management</h3>
        </div>

        <!-- Instructions -->
        <div class="alert alert-info shadow-sm rounded-4 mb-4" style="white-space:pre-line;">
            <ul class="mb-0">
                <li>🧾 Receipt numbers are issued sequentially from loaded batches.</li>
                <li>⚠️ Only one batch can be <strong>Active</strong> at a time. The system automatically switches when the current batch is exhausted.</li>
                <li>✅ Add a new batch before the current one runs out to avoid interruptions.</li>
                <li>ℹ️ Hover over labels <i class="bi bi-info-circle"></i> for details.</li>
            </ul>
        </div>

        <!-- Alerts -->
        @if(session('success'))
        <div class="alert alert-success shadow-sm">{{ session('success') }}</div>
        @elseif(session('error'))
        <div class="alert alert-danger shadow-sm">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
        <div class="alert alert-danger shadow-sm">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Low Receipt Warning -->
        @if ($currentBatch && $currentBatch->remaining_count <= 5)
            <div class="alert alert-warning fw-bold shadow-sm rounded-4">
                ⚠️ Only {{ $currentBatch->remaining_count }} receipts left. Please load a new batch soon.
            </div>
        @endif

        <!-- Active Batch Card -->
        @if ($currentBatch)
        <div class="bg-white rounded-4 shadow p-4 mb-4" style="border:1px solid #e5e7eb;">
            <h5 class="fw-bold pb-2 border-bottom">Current Active Receipt Batch</h5>

            <div class="row mt-3">
                <div class="col-md-4 mb-3">
                    <strong>Start Number:</strong> {{ $currentBatch->start_number }}
                    <i class="bi bi-info-circle text-muted" data-bs-toggle="tooltip"
                       title="The first receipt number in this batch."></i>
                </div>
                <div class="col-md-4 mb-3">
                    <strong>Current Number:</strong> {{ $currentBatch->display_next_number }}
                    <i class="bi bi-info-circle text-muted" data-bs-toggle="tooltip"
                       title="The next receipt number to be issued."></i>
                </div>
                <div class="col-md-4 mb-3">
                    <strong>End Number:</strong> {{ $currentBatch->end_number }}
                    <i class="bi bi-info-circle text-muted" data-bs-toggle="tooltip"
                       title="The last receipt number available."></i>
                </div>

                <div class="col-md-4 mb-3">
                    <strong>Receipts Used:</strong> {{ $currentBatch->used_count }}
                </div>
                <div class="col-md-4 mb-3">
                    <strong>Receipts Left:</strong> {{ $currentBatch->remaining_count }}
                </div>
            </div>
        </div>

        @else
        <div class="alert alert-warning shadow-sm rounded-4">
            No active receipt batch available.
        </div>
        @endif

        <!-- Add Batch -->
        <div class="bg-white rounded-4 shadow p-4 mb-4" style="border:1px solid #e5e7eb;">
            <h5 class="fw-bold pb-2 border-bottom">Add New Receipt Batch</h5>

            <form method="POST" action="{{ route('receipts.addBatch') }}">
                @csrf
                <div class="row g-3 mt-1">
                    <div class="col-md-6">
                        <label class="form-label">
                            Start Number
                            <i class="bi bi-info-circle text-muted" data-bs-toggle="tooltip"
                               title="Enter the first number of the receipt book."></i>
                        </label>
                        <input type="number" name="start_number" class="form-control shadow-sm rounded-3" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            End Number
                            <i class="bi bi-info-circle text-muted" data-bs-toggle="tooltip"
                               title="Enter the final number in the batch."></i>
                        </label>
                        <input type="number" name="end_number" class="form-control shadow-sm rounded-3" required>
                    </div>
                </div>

                <button class="btn btn-success mt-3 px-4 rounded-pill shadow-sm">Add Batch</button>
            </form>
        </div>

        <!-- All Batches Table -->
        <div class="bg-white rounded-4 shadow p-4" style="border:1px solid #e5e7eb;">
            <h5 class="fw-bold pb-2 border-bottom">All Receipt Batches</h5>

            <div class="table-responsive shadow-sm rounded-3 mt-3" style="border:1px solid #e2e3e5;">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Start</th>
                            <th>Current</th>
                            <th>End</th>
                            <th>Used</th>
                            <th>Left</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Exhausted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                    @forelse ($batches as $batch)
                        <tr class="align-middle">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $batch->start_number }}</td>
                            <td>{{ $batch->display_next_number }}</td>
                            <td>{{ $batch->end_number }}</td>
                            <td>{{ $batch->used_count }}</td>
                            <td>{{ $batch->remaining_count }}</td>
                            <td>
                                @if ($batch->next_number <= $batch->end_number)
                                    @if ($currentBatch?->id == $batch->id)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-info">Pending</span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">Empty</span>
                                @endif
                            </td>
                            <td>{{ $batch->created_at->format('Y-m-d') }}</td>
                            <td>{{ optional($batch->exhausted_at)->format('Y-m-d') ?? '—' }}</td>
                            <td>
                                <button class="btn btn-sm btn-primary shadow-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editBatchModal{{ $batch->id }}">
                                    Edit
                                </button>

                                <form method="POST" action="{{ route('receipts.deleteBatch', $batch->id) }}"
                                      class="d-inline"
                                      onsubmit="return confirm('Delete this batch?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger shadow-sm">Delete</button>
                                </form>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editBatchModal{{ $batch->id }}">
                            <div class="modal-dialog">
                                <form method="POST" action="{{ route('receipts.editBatch', $batch->id) }}">
                                    @csrf @method('PUT')
                                    <div class="modal-content shadow-lg rounded-4">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title">Edit Receipt Batch</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body bg-light">
                                            <div class="mb-3">
                                                <label class="form-label">Start Number</label>
                                                <input type="number" value="{{ $batch->start_number }}" disabled class="form-control">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">End Number</label>
                                                <input type="number" name="end_number" class="form-control"
                                                       value="{{ $batch->end_number }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Next Number</label>
                                                <input type="number" name="next_number" class="form-control"
                                                       value="{{ $batch->next_number }}" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button class="btn btn-primary">Save</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-3">
                                No batches found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</main>

<!-- Fade-in animation -->
<style>
    @keyframes fadeIn {
        from {opacity:0; transform:translateY(10px);}
        to {opacity:1; transform:translateY(0);}
    }
</style>

<!-- Re-enable tooltips -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
});
</script>

@endsection
