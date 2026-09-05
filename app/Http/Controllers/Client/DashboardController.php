<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * The Client Dashboard is just two action cards (Book an Event / Schedule
     * an Appointment) — deliberately no stats, no booking history, nothing
     * else queried here. Booking/appointment history itself is unaffected
     * and still fully available via "My Bookings" and "Appointments" in the
     * sidebar; this page just never needed to load it.
     */
    public function index()
    {
        return view('client.dashboard');
    }
}