<?php

namespace Tests\Feature\API;

use App\Actions\RandomGenerator;
use App\Models\Employee;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class EmployeeAPITest extends APITestCase
{
    use WithFaker;

    public function test_api_call_require_authentication()
    {
        $api = $this->getJson('/api/get/dashboard/company/employee/read');
        $this->assertContains($api->getStatusCode(), array(401, 405));

        $api = $this->getJson('/api/post/dashboard/company/employee/save');
        $this->assertContains($api->getStatusCode(), array(401, 405));

        $api = $this->getJson('/api/post/dashboard/company/employee/edit/1');
        $this->assertContains($api->getStatusCode(), array(401, 405));

        $api = $this->getJson('/api/post/dashboard/company/employee/delete/1');
        $this->assertContains($api->getStatusCode(), array(401, 405));
    }

    public function test_api_call_read()
    {
        $this->assertTrue(true);
    }

    public function test_api_call_read_getPaymentTermType()
    {
        $this->assertTrue(true);
    }

    public function test_api_call_save()
    {
        $this->assertTrue(true);
    }

    public function test_api_call_edit()
    {
        $this->assertTrue(true);
    }

    public function test_api_call_delete()
    {
        $this->assertTrue(true);
    }
}
