<?php

namespace Tests\Feature\API\CompanyAPI;

use App\Enums\UserRoles;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class CompanyAPIEditTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_company_api_call_update_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies->first();

        $companyArr = Company::factory()->setStatusActive()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.company.company.edit', $company->ulid), $companyArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'code' => $companyArr['code'],
            'name' => $companyArr['name'],
            'address' => $companyArr['address'],
            'default' => $companyArr['default'],
            'status' => $companyArr['status'],
        ]);
    }

    public function test_company_api_call_update_and_use_existing_code_in_same_user_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setStatusActive()
                    ->count(2)
                    ->state(new Sequence(['default' => true], ['default' => false]))
            )->create();

        $this->actingAs($user);

        $companies = $user->companies()->take(2)->get();
        $company_1 = $companies[0];
        $company_2 = $companies[1];

        $companyArr = Company::factory()->make([
            'code' => $company_2->code,
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.company.company.edit', $company_1->ulid), $companyArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }
}
