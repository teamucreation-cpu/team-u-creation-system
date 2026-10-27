<?php

namespace Tests\Feature;

use App\Models\BusinessSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBusinessSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_business_information_and_welcome_page_uses_saved_values(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create([
            'role' => 'admin',
            'phone' => '09170000001',
            'address' => 'Admin Home',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.profile.edit'))
            ->assertOk()
            ->assertSee('Personal Information')
            ->assertSee('Business Contact Information')
            ->assertSee('Iriga City, Camarines Sur');

        $this->put(route('admin.profile.update'), [
            'name' => $admin->name,
            'email' => $admin->email,
            'phone' => '09170000002',
            'address' => 'Updated Admin Home',
            'password' => '',
            'password_confirmation' => '',
            'business_location' => 'Updated Business Location',
            'business_phone' => '+63 917 000 0003',
            'business_email' => 'contact@teamucreation.test',
            'business_facebook_url' => 'https://www.facebook.com/teamucreation.test',
            'business_days' => 'Tuesday – Saturday',
            'business_opening_time' => '09:30',
            'business_closing_time' => '18:15',
        ])->assertRedirect(route('admin.profile.edit'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'phone' => '09170000002',
            'address' => 'Updated Admin Home',
        ]);
        $this->assertDatabaseHas('business_settings', [
            'location' => 'Updated Business Location',
            'phone' => '+63 917 000 0003',
            'email' => 'contact@teamucreation.test',
            'facebook_url' => 'https://www.facebook.com/teamucreation.test',
            'business_days' => 'Tuesday – Saturday',
            'opening_time' => '09:30',
            'closing_time' => '18:15',
        ]);

        $this->post(route('logout'))->assertRedirect('/');

        $this->get('/')
            ->assertOk()
            ->assertSee('Updated Business Location')
            ->assertSee('tel:+639170000003')
            ->assertSee('contact@teamucreation.test')
            ->assertSee('https://www.facebook.com/teamucreation.test')
            ->assertSee('Tuesday – Saturday')
            ->assertSee('9:30 AM')
            ->assertSee('6:15 PM');
    }

    public function test_business_hours_must_be_valid_and_closing_time_must_follow_opening_time(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $originalPhone = BusinessSetting::query()->firstOrFail()->phone;

        $this->actingAs($admin)
            ->from(route('admin.profile.edit'))
            ->put(route('admin.profile.update'), [
                'name' => $admin->name,
                'email' => $admin->email,
                'phone' => $admin->phone,
                'address' => $admin->address,
                'business_location' => 'Updated Location',
                'business_phone' => '+63 917 000 0003',
                'business_email' => 'contact@teamucreation.test',
                'business_facebook_url' => 'https://www.facebook.com/teamucreation.test',
                'business_days' => 'Tuesday – Saturday',
                'business_opening_time' => '18:15',
                'business_closing_time' => '09:30',
            ])
            ->assertRedirect(route('admin.profile.edit'))
            ->assertSessionHasErrors('business_closing_time');

        $this->assertDatabaseHas('business_settings', ['phone' => $originalPhone]);
    }
}
