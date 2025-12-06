<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // A rota '/' redireciona para login se não autenticado
        $response = $this->get('/');

        // Verifica se redireciona (302) ou se está autenticado (200)
        $this->assertContains($response->status(), [200, 302]);
    }
}
