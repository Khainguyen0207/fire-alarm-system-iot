<?php

namespace Tests\Feature;

use Tests\TestCase;

class DocumentationPageTest extends TestCase
{
    public function test_documentation_page_renders_live_api_playground(): void
    {
        $response = $this->get('/docs');

        $response
            ->assertOk()
            ->assertSee('Điều hướng')
            ->assertSee('Cài đặt ngưỡng')
            ->assertSee('/iot/data')
            ->assertSee('Gửi request')
            ->assertSee('Response JSON thực tế')
            ->assertSee('localStorage')
            ->assertSee('http://localhost:8000/api/v1')
            ->assertSee('ws://localhost:8080')
            ->assertSee('local-reverb-key')
            ->assertSee('fire-alarm-device-key');
    }
}
