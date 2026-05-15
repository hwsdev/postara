<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_redirects_to_setup_when_not_configured(): void
    {
        // Fresh DB — no setup_completed flag
        $response = $this->get('/');

        $response->assertRedirect('/setup');
    }

    public function test_setup_page_is_accessible(): void
    {
        $response = $this->get('/setup');

        $response->assertStatus(200);
    }

    public function test_redirects_to_login_after_setup_complete(): void
    {
        Setting::set('setup_completed', '1');

        $response = $this->get('/');

        $response->assertRedirect('/login');
    }
}
