<?php

namespace App\Http\Controllers;

use App\Models\EventPackage;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $availability = $request->get('availability');

        $query = EventPackage::latest();

        if ($availability === 'available') {
            $query->where('is_available', true);
        } elseif ($availability === 'unavailable') {
            $query->where('is_available', false);
        }

        if ($search) {
            $query->where('package_name', 'like', "%{$search}%");
        }

        $packages = $query->paginate(10)->withQueryString();

        return view('admin.packages.index', compact('packages', 'search', 'availability'));
    }

    public function create()
    {
        return view('admin.packages.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'package_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'inclusions' => ['nullable', 'string'],
            'is_available' => ['sometimes', 'boolean'],
        ]);

        $validated['is_available'] = $request->boolean('is_available');

        EventPackage::create($validated);

        return redirect()->route('admin.packages.index')->with('success', 'Package created successfully.');
    }

    public function edit(EventPackage $package)
    {
        return view('admin.packages.edit', compact('package'));
    }

    public function update(Request $request, EventPackage $package)
    {
        $validated = $request->validate([
            'package_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'inclusions' => ['nullable', 'string'],
            'is_available' => ['sometimes', 'boolean'],
        ]);

        $validated['is_available'] = $request->boolean('is_available');

        $package->update($validated);

        return redirect()->route('admin.packages.index')->with('success', 'Package updated successfully.');
    }

    public function destroy(EventPackage $package)
    {
        // A package already linked to bookings shouldn't just vanish — that would
        // silently blank out which package those bookings were for.
        if ($package->bookings()->exists()) {
            return back()->with('error', 'This package is linked to existing bookings and cannot be deleted. Mark it unavailable instead.');
        }

        $package->delete();

        return redirect()->route('admin.packages.index')->with('success', 'Package deleted successfully.');
    }
}