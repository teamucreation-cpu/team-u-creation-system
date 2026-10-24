@extends('layouts.app')

@section('title', 'New Clients Report - ' . $periodLabel)
@section('page-title', 'New Clients Report - ' . $periodLabel)

@section('content')
<div class="admin-clients-compact">
    <a href="{{ route('admin.reports.index', ['period' => $period]) }}" class="btn btn-sm btn-outline-gold mb-3">
        <i class="fas fa-arrow-left me-1"></i> Back to Reports
    </a>

    <h5 class="mb-2">New Clients Report - {{ $periodLabel }}</h5>
    <div class="list-toolbar d-flex flex-wrap align-items-center justify-content-between gap-3">
        <form method="GET" action="{{ route('admin.reports.clients') }}" class="d-flex flex-wrap gap-2 align-items-center flex-grow-1">
            <input type="hidden" name="period" value="{{ $period }}">
            <input type="text" name="search" class="form-control" style="max-width: 340px; flex: 1 1 220px;" placeholder="Search by name, email, or phone..." value="{{ $search }}">
            <button type="submit" class="btn btn-gold"><i class="fas fa-search me-1"></i> Search</button>
            @if($search)
                <a href="{{ route('admin.reports.clients', ['period' => $period]) }}" class="btn btn-outline-secondary">Clear</a>
            @endif
        </form>
        @if($clients->hasPages())
            <div class="flex-shrink-0">{{ $clients->onEachSide(1)->links() }}</div>
        @endif
    </div>

    <div class="table-custom table-responsive">
        <table class="table mb-0">
            <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Bookings</th><th>Joined</th><th></th></tr></thead>
            <tbody>
                @forelse($clients as $client)
                    <tr>
                        <td><strong>{{ $client->user->name }}</strong></td>
                        <td>{{ $client->user->email }}</td>
                        <td>{{ $client->user->phone }}</td>
                        <td>{{ $client->bookings->count() }}</td>
                        <td>{{ \App\Support\PhilippineTime::date($client->created_at) }}</td>
                        <td><a href="{{ route('admin.clients.show', $client) }}" class="btn btn-sm btn-outline-gold">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted text-center py-3">No new clients found for this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection