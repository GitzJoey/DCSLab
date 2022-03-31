<?php

namespace Tests\Feature\Service;

use TypeError;
use Tests\ServiceTestCase;
use App\Services\RoleService;
use Illuminate\Support\Collection;
use Illuminate\Container\Container;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleServiceTest extends ServiceTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $container = Container::getInstance();
        $this->service = $container->make(RoleService::class);
    }

    public function test_call_read_with_empty_param()
    {
        $response = $this->service->read();

        $this->assertInstanceOf(Collection::class, $response);
        $this->assertNotNull($response);
    }

    public function test_call_read_with_default_role_param()
    {
        $response = $this->service->read(exclude: ['dev', 'administraotr']);

        $this->assertInstanceOf(Collection::class, $response);
        $this->assertNotNull($response);
    }

    public function test_call_read_with_random_param_expect_empty()
    {
        $response = $this->service->read(exclude: ['random']);

        $this->assertNotNull($response);
    }

    public function test_call_read_with_null_param()
    {
        $this->expectException(TypeError::class);
        $this->service->read(null);
    }
}
