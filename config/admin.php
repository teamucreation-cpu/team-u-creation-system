<?php

// Bootstrap Admin credentials, used by database\seeders\AdminSeeder.php.
//
// This lives in config/ (not read via env() directly inside the seeder)
// specifically so it still works after `php artisan config:cache` runs —
// which Laravel Cloud (and most production deploys) does. Once config is
// cached, Laravel disables env() everywhere except inside config/*.php
// files: any env('ADMIN_...') call made directly from the seeder would
// silently return null and fall back to the hardcoded defaults instead of
// whatever was actually set in the hosting platform's environment
// variables — which is exactly what happened before this file existed.
return [
    'email' => env('ADMIN_EMAIL', 'teamucreation@gmail.com'),
    'name' => env('ADMIN_NAME', 'teamucreation'),
    'phone' => env('ADMIN_PHONE', '09000000000'),
    'password' => env('ADMIN_PASSWORD', 'password'),
];
