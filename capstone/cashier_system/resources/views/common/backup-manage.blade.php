@extends('layout.main-master')

@section('content')
<main style="padding: 2rem; min-height: 85vh; background: #f5f7fa;">
    <div class="container" style="max-width: 850px;">

        <h2 class="mb-4 fw-bold">Backup Management</h2>

        {{-- Global messages --}}
        @if(session('success'))
            <div class="alert alert-success rounded-3 p-3">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger rounded-3 p-3">{{ session('error') }}</div>
        @endif

        {{-- Error bag alerts --}}
        @foreach (['restoreErrorBag', 'deleteErrorBag', 'downloadErrorBag'] as $bag)
            @if ($errors->$bag->any())
                <div class="alert alert-danger rounded-3 p-3 mb-3">
                    <ul class="mb-0">
                        @foreach ($errors->$bag->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @endforeach

        {{-- Create new backup --}}
        <div class="mb-4">
            <button type="button" class="btn btn-warning rounded-3 px-4" data-bs-toggle="modal" data-bs-target="#exportModal">
                <i class="bi bi-database-fill me-1"></i> Create Backup
            </button>
        </div>

        {{-- Backup list --}}
        <div class="card shadow-sm rounded-4">
            <div class="card-body p-3">
                <h5 class="mb-3">Saved Backups</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Backup Name</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($backups as $backup)
                            <tr>
                                <td>{{ $backup->id }}</td>
                                <td>{{ $backup->name }}</td>
                                <td>{{ $backup->created_at }}</td>
                                <td class="d-flex gap-2">

                                    {{-- Restore button --}}
                                    <button type="button"
                                        class="btn btn-danger btn-sm rounded-3"
                                        data-bs-toggle="modal"
                                        data-bs-target="#restoreModal{{ $backup->id }}">
                                        Restore
                                    </button>

                                    {{-- Delete button --}}
                                    <button type="button"
                                        class="btn btn-outline-dark btn-sm rounded-3"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteModal{{ $backup->id }}">
                                        Delete
                                    </button>

                                </td>
                            </tr>

                            {{-- Restore Modal --}}
                            <div class="modal fade" id="restoreModal{{ $backup->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content rounded-4">
                                        <form action="{{ route('backups.restore', $backup->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-header border-0">
                                                <h5 class="modal-title">Confirm Restore</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Restoring <strong>{{ $backup->name }}</strong> will overwrite your current database. Enter your password:</p>
                                                <input type="password" name="password" class="form-control rounded-3" required>
                                            </div>
                                            <div class="modal-footer border-0">
                                                <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger rounded-3">Restore Backup</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            {{-- Delete Modal --}}
                            <div class="modal fade" id="deleteModal{{ $backup->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content rounded-4">
                                        <form action="{{ route('backups.delete', $backup->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <div class="modal-header border-0">
                                                <h5 class="modal-title">Confirm Delete</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Are you sure you want to delete <strong>{{ $backup->name }}</strong>? Enter your password:</p>
                                                <input type="password" name="password" class="form-control rounded-3" required>
                                            </div>
                                            <div class="modal-footer border-0">
                                                <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-outline-dark rounded-3">Delete Backup</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            @empty
                            <tr>
                                <td colspan="4" class="text-center">No backups available.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Export Modal --}}
        <div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content rounded-4">
                    <form action="{{ route('backups.export') }}" method="POST">
                        @csrf
                        <div class="modal-header border-0">
                            <h5 class="modal-title">Create Backup</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>You are about to create and download a backup. Enter your password to confirm:</p>
                            <input type="password" name="password" class="form-control rounded-3" required>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-warning rounded-3">Create & Download Backup</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</main>
@endsection
