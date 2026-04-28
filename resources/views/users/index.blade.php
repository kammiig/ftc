@extends('layouts.app')

@section('title', 'Users')
@section('subtitle', 'Admin and staff access')

@section('content')
<div class="toolbar mb-3">
    <div></div>
    <a href="{{ route('users.create') }}" class="btn btn-primary"><i data-lucide="plus"></i> User</a>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>@include('partials.status', ['status' => $user->role])</td>
                    <td>@include('partials.status', ['status' => $user->status])</td>
                    <td class="text-end">
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary"><i data-lucide="pencil"></i></a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-5">No users found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $users->links() }}</div>
</div>
@endsection
