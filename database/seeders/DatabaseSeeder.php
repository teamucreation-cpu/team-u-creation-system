<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Client;
use App\Models\EventPackage;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Bootstrap Admin account — see AdminSeeder for details. Kept as its
        // own seeder (rather than inline here) so it can also be run on its
        // own in production via:
        //   php artisan db:seed --class=AdminSeeder --force
        // without re-running the sample packages/testimonials below.
        $this->call(AdminSeeder::class);

        // Sample event packages so a fresh install has something for clients to
        // browse and book right away, matching Team U Creation's typical services.
        $packages = [
            [
                'package_name' => 'Wedding Essentials',
                'description' => 'A complete wedding coordination package covering ceremony and reception logistics.',
                'price' => 45000.00,
                'inclusions' => "On-the-day coordination team\nCeremony & reception layout planning\nSupplier coordination (catering, photo/video, styling)\nEvent timeline & program management\nEmergency kit and backup coordination",
            ],
            [
                'package_name' => 'Debut Celebration',
                'description' => 'Full-service coordination for 18th birthday celebrations, from program to production.',
                'price' => 30000.00,
                'inclusions' => "Program flow & host coordination\n18 Roses/Candles/Treasures coordination\nVenue setup supervision\nSupplier coordination\nOn-site event management team",
            ],
            [
                'package_name' => 'Corporate Event Package',
                'description' => 'End-to-end coordination for corporate seminars, product launches, and company events.',
                'price' => 50000.00,
                'inclusions' => "Event program design\nVenue & logistics coordination\nRegistration & guest management\nAV/technical coordination\nOn-site event staff",
            ],
            [
                'package_name' => 'Birthday Party Package',
                'description' => 'A festive, fuss-free birthday coordination package for kids and adult celebrations.',
                'price' => 20000.00,
                'inclusions' => "Theme & venue styling coordination\nGames & program facilitation\nSupplier coordination (catering, entertainment)\nOn-site event assistance",
            ],
        ];

        foreach ($packages as $package) {
            EventPackage::firstOrCreate(
                ['package_name' => $package['package_name']],
                array_merge($package, ['is_available' => true])
            );
        }

        // Sample completed events with approved reviews, so the "What Our
        // Clients Say" section on the landing page has content out of the box.
        $testimonials = [
            [
                'name' => 'Maria Santos',
                'email' => 'maria.santos@teamucreation.test',
                'event_type' => 'Wedding',
                'rating' => 5,
                'title' => 'Exceeded every expectation',
                'feedback' => 'The coordination team was professional and organized. Every detail of our wedding was handled smoothly and exceeded our expectations.',
            ],
            [
                'name' => 'Carlos Reyes',
                'email' => 'carlos.reyes@teamucreation.test',
                'event_type' => 'Corporate Event',
                'rating' => 5,
                'title' => 'Flawless product launch',
                'feedback' => 'Team U Creation ran our product launch without a single hitch. Punctual, communicative, and genuinely cared about getting every detail right.',
            ],
            [
                'name' => 'Angela Bautista',
                'email' => 'angela.bautista@teamucreation.test',
                'event_type' => 'Debut',
                'rating' => 4,
                'title' => 'A beautifully coordinated debut',
                'feedback' => 'Our daughter\'s debut was beautifully coordinated from start to finish. The team was warm, responsive, and kept everything on schedule.',
            ],
        ];

        foreach ($testimonials as $t) {
            $user = User::firstOrCreate(
                ['email' => $t['email']],
                [
                    'name' => $t['name'],
                    'phone' => '0900' . rand(1000000, 9999999),
                    'password' => Hash::make('password'),
                    'role' => 'client',
                ]
            );

            $client = Client::firstOrCreate(['user_id' => $user->id]);

            $booking = Booking::firstOrCreate(
                ['client_id' => $client->id, 'event_type' => $t['event_type']],
                [
                    'event_date' => now()->subMonths(rand(1, 6)),
                    'venue' => 'Iriga City, Camarines Sur',
                    'status' => 'Completed',
                ]
            );

            Review::updateOrCreate(
                ['booking_id' => $booking->id],
                [
                    'client_id' => $client->id,
                    'title' => $t['title'],
                    'rating' => $t['rating'],
                    'feedback' => $t['feedback'],
                    'status' => 'Approved',
                ]
            );
        }
    }
}