<?php

namespace Tests\Feature\API\CompanyAPI;

use App\Enums\UserRolesEnum;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Tests\APITestCase;

class CompanyAPICreateTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_company_api_call_store_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->create();

        $payload = Company::factory()->setStatusActive()->setIsDefault()
            ->make()->toArray();

        $api = $this->json('POST', route('api.post.company.save'), $payload);

        $api->assertUnauthorized();
    }

    public function test_company_api_call_store_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->create();

        $this->actingAs($user);

        $payload = Company::factory()->setStatusActive()->setIsDefault()
            ->make()->toArray();

        $api = $this->json('POST', route('api.post.company.save'), $payload);

        $api->assertForbidden();
    }

    public function test_company_api_call_store_with_script_tags_in_payload_expect_stripped()
    {
        $this->markTestSkipped('Test under construction');
    }

    public function test_company_api_call_store_with_script_tags_in_payload_expect_encoded()
    {
        $this->markTestSkipped('Test under construction');
    }

    public function test_company_api_call_store_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->create();

        $this->actingAs($user);

        $payload = Company::factory()->setStatusActive()->setIsDefault()
            ->make()->toArray();

        $api = $this->json('POST', route('api.post.company.save'), $payload);

        $api->assertSuccessful();
        $this->assertDatabaseHas('companies', [
            'code' => $payload['code'],
            'name' => $payload['name'],
            'address' => $payload['address'],
            'default' => $payload['default'],
            'status' => $payload['status'],
        ]);
    }

    public function test_company_api_call_store_with_empty_string_parameters_expect_validation_error()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->create();

        $this->actingAs($user);

        $payload = [];

        $api = $this->json('POST', route('api.post.company.save'), $payload);

        $api->assertJsonValidationErrors(['code', 'name', 'status']);
    }
}
