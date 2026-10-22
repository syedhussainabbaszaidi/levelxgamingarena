<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    /*
    |----------------------------------------------------------------------
    | Customer profile (/customer/profile, public portal layout)
    |----------------------------------------------------------------------
    */

    public function test_customer_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/customer/profile');

        $response->assertOk();

        // Customer profile must use the public portal layout, not the admin one.
        $response->assertDontSee('id="sidebar"', false); // admin sidebar
        $response->assertSee('My profile');
    }

    public function test_legacy_profile_route_redirects_customers_to_customer_profile(): void
    {
        $user = User::factory()->create();

        $this
            ->actingAs($user)
            ->get('/profile')
            ->assertRedirect(route('customer.profile'));
    }

    public function test_customer_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/customer/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('customer.profile'));

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_customer_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/customer/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('customer.profile'));

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_customer_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/customer/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_customer_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/customer/profile')
            ->delete('/customer/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect(route('customer.profile'));

        $this->assertNotNull($user->fresh());
    }

    /*
    |----------------------------------------------------------------------
    | Back-office profile (/profile, admin layout)
    |----------------------------------------------------------------------
    */

    public function test_admin_profile_page_is_displayed_with_admin_layout(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $admin = User::where('role', 'admin')->firstOrFail();

        $response = $this
            ->actingAs($admin)
            ->get('/profile');

        $response->assertOk();
        $response->assertSee('id="sidebar"', false); // admin sidebar present exactly once
        $this->assertSame(
            1,
            substr_count($response->getContent(), 'id="sidebar"')
        );
    }

    public function test_admin_profile_information_can_be_updated(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $admin = User::where('role', 'admin')->firstOrFail();

        $response = $this
            ->actingAs($admin)
            ->patch('/profile', [
                'name' => 'Admin User',
                'email' => 'admin-updated@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $admin->refresh();

        $this->assertSame('Admin User', $admin->name);
        $this->assertSame('admin-updated@example.com', $admin->email);
    }
}
