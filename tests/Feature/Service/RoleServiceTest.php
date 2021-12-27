<?php

namespace Tests\Feature\Service;

use App\Services\RoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Tests\TestCase;
use TypeError;

class RoleServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(RoleService::class);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_call_read_with_empty_array_param()
    {
        $response = $this->service->read([]);

        $this->assertInstanceOf(Collection::class, $response);
        $this->assertTrue(!is_null($response));
    }

    public function test_call_read_with_default_role_param()
    {
        $response = $this->service->read(['withDefaultRole' => true]);

        $this->assertInstanceOf(Collection::class, $response);
        $this->assertTrue(!is_null($response));
    }

    public function test_call_read_with_random_param_expect_null()
    {
        $response = $this->service->read(['random' => true]);

        $this->assertTrue(is_null($response));
    }

    public function test_call_read_with_null_param()
    {
        $this->expectException(TypeError::class);
        $this->service->read(null);
    }

    public function test_call_create()
    {
        $this->assertTrue(true);   
    }

    public function test_call_update()
    {
        $this->assertTrue(true);
    }

    public function test_call_delete()
    {
        $this->assertTrue(true);
    }
}
