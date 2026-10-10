<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Test root API status endpoint.
     */
    public function test_the_application_returns_a_successful_api_status_response(): void
    {
        $response = $this->getJson('/');

        $response->assertStatus(200)
            ->assertJsonPath('name', 'Baritone Music Academy API')
            ->assertJsonPath('status', 'online')
            ->assertJsonStructure([
                'name',
                'version',
                'status',
                'framework',
                'api_base_url',
                'endpoints',
            ]);
    }
}
