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
     * Credentials are read from config('admin.*') — backed by config/admin.php,
     * which itself reads the environment — rather than calling env() directly
     * here. That indirection matters: once `php artisan config:cache` has run
     * (as Laravel Cloud and most production deploys do), env() returns null
     * everywhere except inside config/*.php files, so a direct env() call in
     * this seeder would silently fall back to the hardcoded defaults instead
     * of whatever was actually set in the hosting platform's environment
     * variables. Set ADMIN_EMAIL, ADMIN_NAME, ADMIN_PHONE, ADMIN_PASSWORD in
     * Laravel Cloud's environment variables before running this seeder in
     * production (see .env.example). Sensible local/dev defaults are used
     * if they're left unset.
     *
     * 'role' matches this application's own auth system: a plain string
     * column on users (checked by the "role:admin" route middleware and
     * User::isAdmin()) — there is no separate roles/permissions table.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => config('admin.email')],
            [
                'name' => config('admin.name'),
                'phone' => config('admin.phone'),
                'password' => Hash::make(config('admin.password')),
                'role' => 'admin',
            ]
        );
    }
}
