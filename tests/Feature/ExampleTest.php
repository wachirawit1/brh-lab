<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_root_redirects_to_the_application_index(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('amr.index'));
    }
    public function test_legacy_telegram_http_endpoints_are_not_exposed(): void
    {
        $this->get('/telegram/updates')->assertNotFound();
        $this->post('/telegram/webhook')->assertMethodNotAllowed();
        $this->get('/telegram/set-webhook')->assertNotFound();
        $this->get('/telegram/webhook-info')->assertNotFound();
        $this->post('/telegram/send')->assertMethodNotAllowed();
    }

    public function test_logout_cannot_be_triggered_with_get(): void
    {
        $this->get('/logout')->assertNotFound();
    }
}
