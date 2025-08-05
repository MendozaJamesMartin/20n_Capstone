@extends('layout.main-master')
@section('content')

<main style="background-image: url('/bgpup3.jpg'); background-repeat: no-repeat; background-size:auto; background-position: right center; min-height: 85vh; padding: 2%;">
<div class="container py-4">
    <h2 class="mb-4">Audit Logs</h2>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Date</th>
                <th>User</th>
                <th>Event</th>
                <th>Model</th>
                <th>Old Values</th>
                <th>New Values</th>
            </tr>
        </thead>
        <tbody>
            @foreach($audits as $audit)
                <tr>
                    <td>{{ $audit->created_at->format('Y-m-d H:i:s') }}</td>
                    <td>{{ $audit->user->name ?? 'System' }}</td>
                    <td>{{ ucfirst($audit->event) }}</td>
                    <td>{{ class_basename($audit->auditable_type) }} (ID: {{ $audit->auditable_id }})</td>
                    <td><pre>{{ json_encode($audit->old_values, JSON_PRETTY_PRINT) }}</pre></td>
                    <td><pre>{{ json_encode($audit->new_values, JSON_PRETTY_PRINT) }}</pre></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $audits->links() }}
</div>
</main>

@endsection
