@extends('layout.main-master')

@section('content')

<main class="py-5 px-3" style="min-height: 85vh; background-color: #f8f9fa;">
    <div class="container">
        <h2 class="mb-4">Receipt Management</h2>

        <!-- Page Instructions -->
        <div class="alert alert-info">
            <ul class="mb-0">
                <li>🧾 Receipt numbers are issued sequentially from loaded batches.</li>
                <li>⚠️ Only one batch can be <strong>Active</strong> at a time. The system automatically switches when the current batch is exhausted.</li>
                <li>✅ Add a new batch before the current one runs out to avoid interruptions.</li>
                <li>ℹ️ Hover over labels <i class="bi bi-info-circle"></i> for details.</li>
            </ul>
        </div>

        @if(session('success'))
            <p class="text-success">{{ session('success') }}</p>
        @elseif(session('error'))
            <p class="text-danger">{{ session('error') }}</p>
        @endif

        <!-- Current Batch Summary -->
        @if ($currentBatch && $currentBatch->remaining_count <= 5)
            <p class="text-warning fw-bold">
                ⚠️ Only {{ $currentBatch->remaining_count }} receipts left. Please load a new batch soon.
            </p>
        @endif

        <!-- Current Active Receipt Batch -->
        @if ($currentBatch)
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    Current Active Receipt Batch
                </div>
                <div class="card-body">
                    <p>
                        <strong>Start Number:</strong> 
                        {{ $currentBatch->start_number ?? 0 }}
                        <i class="bi bi-info-circle text-muted" data-bs-toggle="tooltip" title="The first receipt number in this batch."></i>
                    </p>
                    <p>
                        <strong>Current Receipt Number:</strong> 
                        {{ $currentBatch->display_next_number }}
                        <i class="bi bi-info-circle text-muted" data-bs-toggle="tooltip" title="This is the next receipt number that will be issued."></i>
                    </p>
                    <p>
                        <strong>End Number:</strong> 
                        {{ $currentBatch->end_number ?? 0 }}
                        <i class="bi bi-info-circle text-muted" data-bs-toggle="tooltip" title="The last available receipt number in this batch."></i>
                    </p>
                    <p>
                        <strong>Receipts Used:</strong> {{ $currentBatch->used_count }}
                    </p>
                    <p>
                        <strong>Receipts Left:</strong> {{ $currentBatch->remaining_count }}
                    </p>
                </div>
            </div>
        @else
            <div class="alert alert-warning">No active receipt batch available.</div>
        @endif

        <!-- Add New Batch Form -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                Add New Receipt Batch
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('receipts.addBatch') }}">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="start_number" class="form-label">
                                Start Number
                                <i class="bi bi-info-circle text-muted" data-bs-toggle="tooltip" title="Enter the first number of your receipt book. Example: 100001"></i>
                            </label>
                            <input type="number" class="form-control" name="start_number" id="start_number" required>
                        </div>
                        <div class="col-md-6">
                            <label for="end_number" class="form-label">
                                End Number
                                <i class="bi bi-info-circle text-muted" data-bs-toggle="tooltip" title="Enter the last number of your receipt book. Example: 100500"></i>
                            </label>
                            <input type="number" class="form-control" name="end_number" id="end_number" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success">Add Batch</button>
                </form>
            </div>
        </div>

        <!-- All Batches Table -->
        <div class="card">
            <div class="card-header">
                All Receipt Batches
                <i class="bi bi-info-circle text-muted" data-bs-toggle="tooltip" title="This table shows all batches, including Active, Pending, and Empty ones."></i>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered m-0">
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
                            <th>Emptied</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($batches as $batch)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $batch->start_number }}</td>
                            <td>{{ $batch->display_next_number }}</td>
                            <td>{{ $batch->end_number }}</td>
                            <td>{{ $batch->used_count }}</td>
                            <td>{{ $batch->remaining_count }}</td>
                            <td>
                                @if ($batch->next_number <= $batch->end_number)
                                    @if ($batch->id == $currentBatch?->id)
                                        <span class="badge bg-success" data-bs-toggle="tooltip" title="This batch is currently in use.">Active</span>
                                    @else
                                        <span class="badge bg-info" data-bs-toggle="tooltip" title="This batch is ready to be used after the current one finishes.">Pending</span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary" data-bs-toggle="tooltip" title="This batch has been completely used.">Empty</span>
                                @endif
                            </td>
                            <td>{{ ($batch->created_at)->format('Y-m-d') }}</td>
                            <td>{{ optional($batch->exhausted_at)->format('Y-m-d') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">No batches found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<!-- Enable Bootstrap tooltips -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
});
</script>

@endsection
