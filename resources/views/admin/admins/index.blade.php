@extends('layouts.app')

@section('title', 'Admin Accounts')
@section('page-title', 'Admin Accounts')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">All Admin Accounts</h5>
    <a href="{{ route('admin.admins.create') }}" class="btn btn-gold"><i class="fas fa-plus me-1"></i> Add Admin</a>
</div>

<div class="table-custom">
    <table class="table mb-0">
        <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Created</th><th>Actions</th></tr></thead>
        <tbody>
            @forelse($admins as $account)
            <tr>
                <td>
                    <strong>{{ $account->name }}</strong>
                    @if($account->id === auth()->id())
                        <span class="badge bg-secondary ms-1">You</span>
                    @endif
                </td>
                <td>{{ $account->email }}</td>
                <td>{{ $account->phone }}</td>
                <td>{{ \App\Support\PhilippineTime::date($account->created_at) }}</td>
                <td>
                    <a href="{{ route('admin.admins.edit', $account) }}" class="btn btn-sm btn-outline-gold">Edit</a>
                    @if($account->id !== auth()->id() && $admins->count() > 1)
                        <form method="POST" action="{{ route('admin.admins.destroy', $account) }}" class="d-inline" onsubmit="return confirm('Delete this admin account?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-muted text-center py-3">No admin accounts yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
