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
        // Pengunjung dialihkan ke gerbang password
        $response = $this->get('/');
        $response->assertRedirect('/access-gate');

        // Gerbang password dapat diakses dengan sukses
        $gateResponse = $this->get('/access-gate');
        $gateResponse->assertStatus(200);
    }
}
