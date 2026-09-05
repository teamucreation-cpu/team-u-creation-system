<?php

namespace App\Http\Controllers;

use App\Models\EventPackage;

class ClientPackageController extends Controller
{
    public function index()
    {
        $packages = EventPackage::where('is_available', true)->latest()->get();
        return view('client.packages.index', compact('packages'));
    }

    public function show(EventPackage $package)
    {
        if (! $package->is_available) {
            abort(404);
        }

        return view('client.packages.show', compact('package'));
    }
}