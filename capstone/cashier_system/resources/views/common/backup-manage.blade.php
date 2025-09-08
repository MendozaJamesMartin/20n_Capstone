@extends('layout.main-master')

@section('content')
<main style="padding: 2rem; min-height: 85vh; background: #f9fafb;">
    <div class="container" style="max-width: 800px;">
        <h2 class="mb-4">Backup Management</h2>

        {{-- Global messages --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Error bag alerts --}}
        @if ($errors->restoreErrorBag->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->restoreErrorBag->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($errors->deleteErrorBag->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->deleteErrorBag->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($errors->downloadErrorBag->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->downloadErrorBag->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Create new backup --}}
        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#exportModal">
            Create Backup
        </button>
        <hr>

        {{-- List backups --}}
        <h4>Saved Backups</h4>
        <table class="table table-bordered table-striped">
            <thead>
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

                    {{-- Restore button (modal) --}}
                    <button type="button"
                        class="btn btn-danger btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#restoreModal{{ $backup->id }}">
                        Restore
                    </button>

                    {{-- Delete button (modal) --}}
                    <button type="button"
                        class="btn btn-outline-dark btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteModal{{ $backup->id }}">
                        Delete
                    </button>

                    </td>
                </tr>

                <!-- Export Modal -->
                <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('backups.export') }}" method="POST">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exportModalLabel">Confirm Backup</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p>You are about to create and download a backup. Please confirm by entering your password:</p>
                                    <div class="mb-3">
                                        <label for="backupPassword" class="form-label">Password</label>
                                        <input type="password" name="password" id="backupPassword" class="form-control" required>
                                        @error('password')
                                        <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-warning">Create & Download Backup</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Restore Modal -->
                <div class="modal fade" id="restoreModal{{ $backup->id }}" tabindex="-1" aria-labelledby="restoreModalLabel{{ $backup->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('backups.restore', $backup->id) }}" method="POST">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title">Confirm Restore</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Restoring <strong>{{ $backup->name }}</strong> will overwrite your current database. Enter your password to continue:</p>
                                    <div class="mb-3">
                                        <label for="restorePassword{{ $backup->id }}" class="form-label">Password</label>
                                        <input type="password" name="password" id="restorePassword{{ $backup->id }}" class="form-control" required>
                                        @error('password') <div class="text-danger small">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-danger">Restore Backup</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Delete Modal -->
                <div class="modal fade" id="deleteModal{{ $backup->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $backup->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('backups.delete', $backup->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <div class="modal-header">
                                    <h5 class="modal-title">Confirm Delete</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Are you sure you want to delete <strong>{{ $backup->name }}</strong>? Enter your password to confirm:</p>
                                    <div class="mb-3">
                                        <label for="deletePassword{{ $backup->id }}" class="form-label">Password</label>
                                        <input type="password" name="password" id="deletePassword{{ $backup->id }}" class="form-control" required>
                                        @error('password') <div class="text-danger small">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-outline-dark">Delete Backup</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                @empty
                <tr>
                    <td colspan="4">No backups available.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</main>
@endsection