<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_does_not_serve_a_root_web_route(): void
    {
        $response = $this->get('/');

        $response->assertNotFound();
    }
}
