<?php

namespace Tests\Feature\API\CompanyAPI;

use App\Enums\UserRoles;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Str;
use Tests\APITestCase;

class CompanyAPIDeleteTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_company_api_call_delete_without_authorization_expect_unauthorized_message()
    {
        $companyCount = 2;
        $idxDefaultCompany = random_int(0, $companyCount - 1);

        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->count($companyCount)
                ->state(new Sequence(
                    fn (Sequence $sequence) => [
                        'default' => $sequence->index == $idxDefaultCompany ? true : false,
                    ]
                ))
            )
            ->create();

        $company = $user->companies()->where('default', '=', false)->first();

        $api = $this->json('POST', route('api.post.db.company.company.delete', $company->ulid));

        $api->assertStatus(401);
    }

    public function test_company_api_call_delete_without_access_right_expect_unauthorized_message()
    {
        $companyCount = 2;
        $idxDefaultCompany = random_int(0, $companyCount - 1);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->count($companyCount)
                ->state(new Sequence(
                    fn (Sequence $sequence) => [
                        'default' => $sequence->index == $idxDefaultCompany ? true : false,
                    ]
                ))
            )
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->where('default', '=', false)->first();

        $api = $this->json('POST', route('api.post.db.company.company.delete', $company->ulid));

        $api->assertStatus(403);
    }

    public function test_company_api_call_delete_expect_successful()
    {
        $companyCount = 2;
        $idxDefaultCompany = random_int(0, $companyCount - 1);

        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->count($companyCount)
                ->state(new Sequence(
                    fn (Sequence $sequence) => [
                        'default' => $sequence->index == $idxDefaultCompany ? true : false,
                    ]
                ))
            )
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->where('default', '=', false)->first();

        $api = $this->json('POST', route('api.post.db.company.company.delete', $company->ulid));

        $api->assertSuccessful();
        $this->assertSoftDeleted('companies', [
            'id' => $company->id,
        ]);
    }

    public function test_company_api_call_delete_of_nonexistance_ulid_expect_not_found()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $ulid = Str::ulid()->generate();

        $api = $this->json('POST', route('api.post.db.company.company.delete', $ulid));

        $api->assertStatus(404);
    }

    public function test_company_api_call_delete_without_parameters_expect_failed()
    {
        $this->expectException(Exception::class);
        $user = User::factory()->create();

        $this->actingAs($user);
        $api = $this->json('POST', route('api.post.db.company.company.delete', null));
    }
}
