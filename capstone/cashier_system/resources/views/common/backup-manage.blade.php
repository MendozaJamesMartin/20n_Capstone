@extends('layout.main-master')

@section('content')
<main style="padding: 2rem; min-height: 85vh; background: #f9fafb;">
    <div class="container" style="max-width: 600px;">
        <h2 class="mb-4">Backup Management</h2>

        {{-- Success/Error messages --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Export database button --}}
        <form action="{{ route('backups.export-db') }}" method="GET" class="mb-4">
            <button type="submit" class="btn btn-warning">
                Export Database (SQL)
            </button>
        </form>

        <hr>

        {{-- Import / Restore database form --}}
        <form id="restoreForm" action="{{ route('backups.restore') }}" method="POST" enctype="multipart/form-data" onsubmit="return confirmRestore()">
            @csrf
            <div class="mb-3">
                <label for="sql_file" class="form-label">Restore Database from SQL File</label>
                <input type="file" name="sql_file" id="sql_file" class="form-control" accept=".sql,.txt" required>
            </div>
            <button type="submit" class="btn btn-danger">
                Restore Database
            </button>
        </form>
    </div>
</main>

<script>
    function confirmRestore() {
        return confirm('Are you sure you want to restore the database? This will overwrite current data!');
    }
</script>
@endsection
