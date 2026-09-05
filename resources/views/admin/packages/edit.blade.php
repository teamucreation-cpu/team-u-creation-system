@extends('layouts.app')

@section('title', 'Edit Package')

@section('content')
<h2 class="mb-4">Edit Package</h2>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('admin.packages.update', $package) }}">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label for="package_name" class="form-label">Package Name</label>
        <input type="text" class="form-control" id="package_name" name="package_name" value="{{ old('package_name', $package->package_name) }}" required>
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $package->description) }}</textarea>
    </div>

    <div class="mb-3">
        <label for="price" class="form-label">Price (₱)</label>
        <input type="number" step="0.01" min="0" class="form-control" id="price" name="price" value="{{ old('price', $package->price) }}" required>
    </div>

    <div class="mb-3">
        <label for="inclusions" class="form-label">Inclusions</label>
        <textarea class="form-control" id="inclusions" name="inclusions" rows="3" placeholder="One item per line">{{ old('inclusions', $package->inclusions) }}</textarea>
    </div>

    <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="is_available" name="is_available" value="1" {{ old('is_available', $package->is_available) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_available">Available for booking</label>
    </div>

    <button type="submit" class="btn btn-dark">Save Changes</button>
</form>
@endsection