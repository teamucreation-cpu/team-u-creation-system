<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Create (or refresh) the one bootstrap Admin account needed to log
     * into a fresh or freshly-deployed installation.
     *
     * updateOrCreate() keys on email, so running this seeder any number of
     * times — locally or in production — never creates a second admin row
     * for the same address; it only inserts once and thereafter just
     * refreshes that same row's name/phone/password/role.
     *
     * Credentials are read from the environment rather than hardcoded here,
     * so no real password ever sits in source control. Set these in Laravel
     * Cloud's environment variables before running this seeder in
     * production (see .env.example):
     *   ADMIN_EMAIL, ADMIN_NAME, ADMIN_PHONE, ADMIN_PASSWORD
     * Sensible local/dev defaults are used if they're left unset.
     *
     * 'role' matches this application's own auth system: a plain string
     * column on users (checked by the "role:admin" route middleware and
     * User::isAdmin()) — there is no separate roles/permissions table.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'teamucreation@gmail.com')],
            [
                'name' => env('ADMIN_NAME', 'teamucreation'),
                'phone' => env('ADMIN_PHONE', '09000000000'),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
                'role' => 'admin',
            ]
        );
    }
}
