<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/admin');
    }

    public function test_admin_dashboard_mengarahkan_guest_ke_halaman_masuk(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect(route('admin.masuk'));
    }
}
