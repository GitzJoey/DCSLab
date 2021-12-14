<?php

namespace Tests\Feature;

use App\Services\UnitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UnitServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(UnitService::class);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_read()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
    
    public function test_create()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_update()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_delete()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
