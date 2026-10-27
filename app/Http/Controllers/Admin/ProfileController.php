<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $businessSettings = BusinessSetting::query()->firstOrFail();

        return view('admin.profile.edit', compact('user', 'businessSettings'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'confirmed', Rules\Password::min(8)],
            'business_location' => ['required', 'string', 'max:255'],
            'business_phone' => ['required', 'string', 'max:30'],
            'business_email' => ['required', 'email', 'max:255'],
            'business_facebook_url' => ['required', 'url:http,https', 'max:2048'],
            'business_days' => ['required', 'string', 'max:255'],
            'business_opening_time' => ['required', 'date_format:H:i'],
            'business_closing_time' => ['required', 'date_format:H:i', 'after:business_opening_time'],
        ]);

        DB::transaction(function () use ($user, $validated) {
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address'] ?? null,
            ]);

            if (!empty($validated['password'])) {
                $user->update(['password' => Hash::make($validated['password'])]);
            }

            BusinessSetting::query()->firstOrFail()->update([
                'location' => $validated['business_location'],
                'phone' => $validated['business_phone'],
                'email' => $validated['business_email'],
                'facebook_url' => $validated['business_facebook_url'],
                'business_days' => $validated['business_days'],
                'opening_time' => $validated['business_opening_time'],
                'closing_time' => $validated['business_closing_time'],
            ]);
        });

        return back()->with('success', 'Profile and business contact information updated successfully.');
    }
}
