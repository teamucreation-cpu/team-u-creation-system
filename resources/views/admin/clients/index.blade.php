@extends('layouts.app')

@section('title', 'Clients')
@section('page-title', 'Clients')

@push('styles')
<style>
    /* Scoped to this page only — every other admin table keeps the default
       .table-custom / .list-toolbar spacing. Goal: fit the header + search
       toolbar + all 10 rows of a full page on a standard laptop viewport
       (~700–800px of usable height) with no scrolling, by trimming padding
       and margins rather than shrinking any text. */
    .admin-clients-compact .list-toolbar {
        padding: 0.5rem 1rem;
        margin-bottom: 0.4rem;
    }
    .admin-clients-compact .table-custom thead th {
        padding: 0.4rem 1rem;
    }
    .admin-clients-compact .table-custom tbody td {
        padding: 0.3rem 1rem;
    }
    .admin-clients-compact .pagination { margin-bottom: 0; }
    /* The Bootstrap-5 pagination partial's own "Showing X to Y of Z
       results" caption — kept, just tightened so it doesn't add much
       height next to the search box. */
    .admin-clients-compact .list-toolbar p.small { margin-bottom: 0.2rem; }

    @media (max-width: 575.98px) {
        .admin-clients-compact .list-toolbar { padding: 0.75rem; }
    }
</style>
@endpush

@section('content')
<div class="admin-clients-compact">
    {{-- Search and pagination share one toolbar row — pagination no longer
         sits below the table, so it (and the search box) are always visible
         together regardless of how tall the table itself ends up being. --}}
    <div class="list-toolbar d-flex flex-wrap align-items-center justify-content-between gap-3">
        <form method="GET" action="{{ route('admin.clients.index') }}" class="d-flex flex-wrap gap-2 align-items-center flex-grow-1">
            <input type="text" name="search" class="form-control" style="max-width: 340px; flex: 1 1 220px;" placeholder="Search by name, email, or phone..." value="{{ $search }}">
            <button type="submit" class="btn btn-gold"><i class="fas fa-search me-1"></i> Search</button>
            @if($search)
                <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-secondary">Clear</a>
            @endif
        </form>
        @if($clients->hasPages())
            <div class="flex-shrink-0">{{ $clients->onEachSide(1)->links() }}</div>
        @endif
    </div>

    <div class="table-custom">
        <table class="table mb-0">
            <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Bookings</th><th>Joined</th><th></th></tr></thead>
            <tbody>
                @forelse($clients as $client)
                <tr>
                    <td><strong>{{ $client->user->name }}</strong></td>
                    <td>{{ $client->user->email }}</td>
                    <td>{{ $client->user->phone }}</td>
                    <td>{{ $client->bookings->count() }}</td>
                    <td>{{ $client->created_at->format('M d, Y') }}</td>
                    <td><a href="{{ route('admin.clients.show', $client) }}" class="btn btn-sm btn-outline-gold">View</a></td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-muted text-center py-3">No clients found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
