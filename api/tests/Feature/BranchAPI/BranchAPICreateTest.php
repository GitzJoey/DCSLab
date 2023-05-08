<?php

namespace Tests\Feature;

use App\Enums\UserRoles;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class BranchAPICreateTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_branch_api_call_store_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
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
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
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
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault())
                    ->has(Company::factory()->setStatusActive())
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
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
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
