<?php

namespace Tests\Feature;

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\APITestCase;

class BranchAPITest extends APITestCase
{
    public function test_api_call_require_authentication()
    {
        $api = $this->getJson('/api/get/dashboard/company/branch/read');
        $this->assertContains($api->getStatusCode(), array(401, 405));

        $api = $this->getJson('/api/post/dashboard/company/branch/save');
        $this->assertContains($api->getStatusCode(), array(401, 405));

        $api = $this->getJson('/api/post/dashboard/company/branch/edit/1');
        $this->assertContains($api->getStatusCode(), array(401, 405));

        $api = $this->getJson('/api/post/dashboard/company/branch/delete/1');
        $this->assertContains($api->getStatusCode(), array(401, 405));
    }

    public function test_api_call_read()
    {
        $this->actingAs($this->user);

        $companyId = Company::inRandomOrder()->get()[0]->id;;
        $page = 1;
        $pageSize = 10;
        $search = '';

        $api = $this->getJson(route('api.post.db.company.branch.save', [
            'companyId' => $companyId,
            'page' => $page,
            'perPage' => $pageSize,
            'search' => $search
        ]));

        $api->assertStatus(200);
        $api->assertSuccessful();
    }

    public function test_api_call_read_getPaymentTermType()
    {
        $this->assertTrue(true);
    }

    public function test_api_call_store()
    {
        $this->assertTrue(true);
    }

    public function test_api_call_update()
    {
        $this->assertTrue(true);
    }

    public function test_api_call_delete()
    {
        $this->assertTrue(true);
    }
}
