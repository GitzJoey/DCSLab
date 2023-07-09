<?php

namespace Tests\Feature\API\BranchAPI;

use App\Enums\UserRoles;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class BranchAPICreateTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_branch_api_call_store_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $branchArr = Branch::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.company.branch.save'), $branchArr);

        $api->assertStatus(401);
    }

    public function test_branch_api_call_store_without_access_right_expect_forbidden_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $branchArr = Branch::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.company.branch.save'), $branchArr);

        $api->assertStatus(403);
    }

    public function test_branch_api_call_store_with_script_tags_in_payload_expect_stripped()
    {
        $this->markTestSkipped('Test under construction');
    }

    public function test_branch_api_call_store_with_script_tags_in_payload_expect_encoded()
    {
        $this->markTestSkipped('Test under construction');
    }

    public function test_branch_api_call_store_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $branchArr = Branch::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.company.branch.save'), $branchArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('branches', [
            'company_id' => $company->id,
            'code' => $branchArr['code'],
            'name' => $branchArr['name'],
        ]);
    }

    public function test_branch_api_call_store_with_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        Branch::factory()->for($company)->create([
            'code' => 'test1',
        ]);

        $branchArr = array_merge([
            'company_id' => Hashids::encode($company->id),
        ], Branch::factory()->make([
            'code' => 'test1',
        ])->toArray());

        $api = $this->json('POST', route('api.post.db.company.branch.save'), $branchArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_branch_api_call_store_with_existing_code_in_different_company_expect_successful()
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

        $company_1 = $user->companies[0];
        $companyId_1 = $company_1->id;

        $company_2 = $user->companies[1];
        $companyId_2 = $company_2->id;

        Branch::factory()->create([
            'company_id' => $companyId_1,
            'code' => 'test1',
        ]);

        $branchArr = Branch::factory()->make([
            'company_id' => Hashids::encode($companyId_2),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.company.branch.save'), $branchArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('branches', [
            'company_id' => $companyId_2,
            'code' => $branchArr['code'],
        ]);
    }

    public function test_branch_api_call_store_with_empty_string_parameters_expect_validation_error()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $branchArr = [];
        $api = $this->json('POST', route('api.post.db.company.branch.save'), $branchArr);

        $api->assertJsonValidationErrors(['company_id', 'code', 'name']);
    }
}
