@extends('layout.main-master')

@section('content')
<main style="padding: 2rem; min-height: 85vh; background: #f5f7fa;">

    <div class="container" style="max-width: 900px;">

        <h2 class="mb-4 fw-bold text-dark">Backup Management</h2>

        {{-- Success / Error Alerts --}}
        @if(session('success'))
            <div class="alert alert-success rounded-3 shadow-sm p-3">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger rounded-3 shadow-sm p-3">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
        <div class="alert alert-danger rounded-3 shadow-sm p-3 mb-3">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Create Backup Button --}}
        <div class="mb-4">
            <button type="button" class="btn btn-warning rounded-3 px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#exportModal">
                <i class="bi bi-cloud-arrow-down-fill me-1"></i> Create Backup
            </button>
        </div>

        {{-- Backup List --}}
        <div class="card shadow-sm rounded-4">
            <div class="card-body p-3">
                <h5 class="mb-3 fw-semibold">Saved Backups</h5>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Backup File</th>
                                <th>Created At</th>
                                <th style="width: 180px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($backups as $backup)
                                <tr>
                                    <td>{{ $backup->id }}</td>
                                    <td class="fw-semibold">{{ $backup->name }}</td>
                                    <td>{{ $backup->created_at->format('Y-m-d H:i:s') }}</td>

                                    <td class="d-flex gap-2">

                                        {{-- DOWNLOAD --}}
                                        <a href="{{ route('backups.download', $backup->id) }}" class="btn btn-warning btn-sm rounded-3 shadow-sm">
                                            <i class="bi bi-download me-1"></i> Download
                                        </a>

                                        {{-- DELETE --}}
                                        <button class="btn btn-outline-danger btn-sm rounded-3 shadow-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteModal{{ md5($backup->name) }}">
                                            <i class="bi bi-trash"></i>
                                        </button>

                                    </td>
                                </tr>

                                {{-- Delete Modal --}}
                                <div class="modal fade" id="deleteModal{{ md5($backup->name) }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content rounded-4 shadow-sm">
                                            <form action="{{ route('backups.delete', $backup->name) }}" method="POST">
                                                @csrf
                                                @method('DELETE')

                                                <div class="modal-header border-0">
                                                    <h5 class="modal-title">Delete Backup</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>

                                                <div class="modal-body">
                                                    <p class="mb-2">You are about to delete:</p>
                                                    <p class="fw-semibold">{{ $backup->name }}</p>

                                                    <p class="mt-3">Enter your password to confirm:</p>
                                                    <input type="password" name="password" class="form-control rounded-3" required>
                                                </div>

                                                <div class="modal-footer border-0">
                                                    <button type="button" class="btn btn-secondary rounded-3 shadow-sm" data-bs-dismiss="modal">
                                                        Cancel
                                                    </button>
                                                    <button type="submit" class="btn btn-danger rounded-3 shadow-sm">
                                                        Delete Backup
                                                    </button>
                                                </div>

                                            </form>
                                        </div>
                                    </div>
                                </div>

                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-3 text-muted">No backups available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

        {{-- Create Backup Modal --}}
        <div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content rounded-4 shadow-sm">
                    <form action="{{ route('backups.export') }}" method="POST">
                        @csrf

                        <div class="modal-header border-0">
                            <h5 class="modal-title">Create Backup</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <p>You are about to create and download a new encrypted backup.</p>
                            <p>Enter your password to confirm:</p>

                            <input type="password" name="password" class="form-control rounded-3" required>
                        </div>

                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-secondary rounded-3 shadow-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-warning rounded-3 shadow-sm">
                                Create & Download Backup
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

    </div>
</main>

<style>
    .table-hover tbody tr:hover {
        background-color: rgba(0,0,0,0.03);
    }

    .btn:focus {
        box-shadow: none;
    }
</style>

@endsection
