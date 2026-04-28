@extends('layouts.app')

@section('title', 'Backup Management')
@section('subtitle', 'Admin-only database and full-file backups stored outside public access')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-body">
                <div class="form-section-title">Create Backup</div>
                <form method="POST" action="{{ route('backups.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Backup type</label>
                        <select class="form-select" name="type" required>
                            <option value="full">Full backup: database and uploaded files</option>
                            <option value="database">Database-only backup</option>
                        </select>
                    </div>
                    <button class="btn btn-primary"><i data-lucide="database-backup"></i> Create Backup</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body">
                <div class="form-section-title">Security</div>
                <p class="mb-2">Backup ZIP files are stored in <code>storage/app/backups</code>, not in the public web root.</p>
                <p class="mb-0">Downloads, creation, and deletion are protected by login and Admin role checks, and every action is recorded in the activity log.</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Date</th><th>Filename</th><th>Type</th><th>Status</th><th>Size</th><th>Created By</th><th>Message</th><th></th></tr></thead>
            <tbody>
            @forelse($backups as $backup)
                <tr>
                    <td>{{ $backup->created_at?->format('d M Y h:i A') }}</td>
                    <td class="fw-semibold">{{ $backup->filename }}</td>
                    <td>@include('partials.status', ['status' => $backup->type])</td>
                    <td>@include('partials.status', ['status' => $backup->status])</td>
                    <td>{{ $backup->sizeForHumans() }}</td>
                    <td>{{ $backup->user?->name ?: 'Cron/System' }}</td>
                    <td>{{ $backup->message ?: '-' }}</td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            @if($backup->status === 'completed')
                                <a class="btn btn-outline-success" href="{{ route('backups.download', $backup) }}" title="Download"><i data-lucide="download"></i></a>
                            @endif
                            <form method="POST" action="{{ route('backups.destroy', $backup) }}" onsubmit="return confirm('Delete this backup permanently?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-outline-danger" title="Delete"><i data-lucide="trash-2"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-5">No backups created yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $backups->links() }}</div>
</div>
@endsection
