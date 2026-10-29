<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_redirects_to_the_dashboard()
    {
        $response = $this->get(route('home'));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_guests_visiting_home_end_up_on_the_login_page()
    {
        $this->followingRedirects()
            ->get(route('home'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('auth/Login'));
    }
}
