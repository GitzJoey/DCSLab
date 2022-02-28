<?php

namespace Tests\Feature\Service;

use App\Services\RoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Tests\ServiceTestCase;
use TypeError;

class RoleServiceTest extends ServiceTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(RoleService::class);
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
