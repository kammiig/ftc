@extends('layouts.app')

@section('title', 'Edit User')
@section('subtitle', $user->email)

@section('content')
<form method="POST" action="{{ route('users.update', $user) }}">
    @include('users._form', ['method' => 'PUT'])
</form>

<form method="POST" action="{{ route('users.destroy', $user) }}" class="mt-3" onsubmit="return confirm('Delete this user?')">
    @csrf
    @method('DELETE')
    <button class="btn btn-outline-danger"><i data-lucide="trash-2"></i> Delete User</button>
</form>
@endsection
