<?php

namespace Tests\Feature\API\CompanyAPI;

use App\Enums\UserRolesEnum;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\APITestCase;

class CompanyAPIEditTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_company_api_call_update_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies->first();

        $payload = Company::factory()->setStatusActive()->make()->toArray();

        $api = $this->json('POST', route('api.post.company.edit', $company->ulid), $payload);

        $api->assertUnauthorized();
    }

    public function test_company_api_call_update_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies->first();

        $payload = Company::factory()->setStatusActive()->make()->toArray();

        $api = $this->json('POST', route('api.post.company.edit', $company->ulid), $payload);

        $api->assertForbidden();
    }

    public function test_company_api_call_update_with_script_tags_in_payload_expect_stripped()
    {
        $this->markTestSkipped('Test under construction');
    }

    public function test_company_api_call_update_with_script_tags_in_payload_expect_encoded()
    {
        $this->markTestSkipped('Test under construction');
    }

    public function test_company_api_call_update_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies->first();

        $payload = Company::factory()->setStatusActive()->setIsDefault()->make()->toArray();

        $api = $this->json('POST', route('api.post.company.edit', $company->ulid), $payload);

        $api->assertSuccessful();
        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'code' => $payload['code'],
            'name' => $payload['name'],
            'address' => $payload['address'],
            'default' => $payload['default'],
            'status' => $payload['status'],
        ]);
    }

    public function test_company_api_call_update_and_use_existing_code_in_same_user_expect_failed()
    {
        $companyCount = 2;
        $idxDefaultCompany = random_int(0, $companyCount - 1);

        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->count($companyCount)
                ->state(new Sequence(
                    fn (Sequence $sequence) => [
                        'default' => $sequence->index == $idxDefaultCompany ? true : false,
                    ]
                ))
            )
            ->create();

        $this->actingAs($user);

        $companies = $user->companies()->take(2)->get();
        $company_1 = $companies[0];
        $company_2 = $companies[1];

        $payload = Company::factory()->make([
            'code' => $company_2->code,
        ])->toArray();

        $api = $this->json('POST', route('api.post.company.edit', $company_1->ulid), $payload);

        $api->assertUnprocessable();
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_company_api_call_update_and_use_existing_name_in_same_user_expect_failed()
    {
        $companyCount = 2;
        $idxDefaultCompany = random_int(0, $companyCount - 1);

        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->count($companyCount)
                ->state(new Sequence(
                    fn (Sequence $sequence) => [
                        'default' => $sequence->index == $idxDefaultCompany,
                    ]
                ))
            )
            ->create();

        $this->actingAs($user);

        $companies = $user->companies()->take(2)->get();
        $company_1 = $companies[0];
        $company_2 = $companies[1];

        $payload = Company::factory()->make([
            'name' => $company_2->name,
        ])->toArray();

        $api = $this->json('POST', route('api.post.company.edit', $company_1->ulid), $payload);

        $api->assertUnprocessable();
        $api->assertJsonValidationErrors(['name']);
    }

    public function test_company_api_call_update_and_use_existing_code_in_different_user_expect_successful()
    {
        User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()->state([
                'code' => 'CP001',
            ]))
            ->create();

        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->first();
        $payload = Company::factory()->setStatusActive()->make([
            'code' => 'CP001',
        ])->toArray();

        $api = $this->json('POST', route('api.post.company.edit', $company->ulid), $payload);

        $api->assertSuccessful();
        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'code' => 'CP001',
            'name' => $payload['name'],
        ]);
    }

    public function test_company_api_call_update_with_auto_code_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->first();
        $payload = Company::factory()->setStatusActive()->make([
            'code' => config('dcslab.KEYWORDS.AUTO'),
        ])->toArray();

        $api = $this->json('POST', route('api.post.company.edit', $company->ulid), $payload);

        $api->assertSuccessful();

        $this->assertDatabaseMissing('companies', [
            'id' => $company->id,
            'code' => config('dcslab.KEYWORDS.AUTO'),
        ]);
    }
}
