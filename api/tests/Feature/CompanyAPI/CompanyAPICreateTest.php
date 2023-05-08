<?php

namespace Tests\Feature;

use App\Enums\UserRoles;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CompanyAPICreateTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_company_api_call_store_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->create();

        $this->actingAs($user);

        $companyArr = Company::factory()->setStatusActive()->setIsDefault()
                        ->make()->toArray();

        $api = $this->json('POST', route('api.post.db.company.company.save'), $companyArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('companies', [
            'code' => $companyArr['code'],
            'name' => $companyArr['name'],
            'address' => $companyArr['address'],
            'default' => $companyArr['default'],
            'status' => $companyArr['status'],
        ]);
    }

    public function test_company_api_call_store_with_empty_string_parameters_expect_validation_error()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                ->create();

        $this->actingAs($user);

        $companyArr = [];

        $api = $this->json('POST', route('api.post.db.company.company.save'), $companyArr);

        $api->assertJsonValidationErrors(['code', 'name', 'status']);
    }
}
