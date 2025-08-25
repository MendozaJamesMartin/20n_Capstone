@extends('layout.main-master')

@section('content')
<main style="padding: 2rem; min-height: 85vh; background: #f9fafb;">
    <div class="container" style="max-width: 800px;">
        <h2 class="mb-4">Backup Management</h2>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Create new backup --}}
        <form action="{{ route('backups.export') }}" method="POST" class="mb-4">
            @csrf
            <button type="submit" class="btn btn-warning">
                Create Backup
            </button>
        </form>

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
                            {{-- Restore button --}}
                            <form action="{{ route('backups.restore', $backup->id) }}" method="POST" onsubmit="return confirm('Restore this backup? This will overwrite all data!')">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm">Restore</button>
                            </form>

                            {{-- Delete button --}}
                            <form action="{{ route('backups.delete', $backup->id) }}" method="POST" onsubmit="return confirm('Delete this backup permanently?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-dark btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
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
