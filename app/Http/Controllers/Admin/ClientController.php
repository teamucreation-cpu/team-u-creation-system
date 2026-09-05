<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $query = Client::with('user', 'bookings')->latest();

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $clients = $query->paginate(10)->withQueryString();

        return view('admin.clients.index', compact('clients', 'search'));
    }

    public function show(Client $client)
    {
        $client->load(['user', 'bookings.package', 'appointments']);
        return view('admin.clients.show', compact('client'));
    }
}