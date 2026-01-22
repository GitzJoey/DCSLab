<?php

namespace Tests\Feature\API\BranchAPI;

use App\Enums\RecordStatusEnum;
use App\Enums\UserRolesEnum;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class BranchAPIEditTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_branch_api_call_update_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory()->setStatusActive()->count(3)))
            ->create();

        $company = $user->companies->first();

        $branch = $company->branches()->inRandomOrder()->first();

        $payload = Branch::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.branch.edit', $branch->ulid), $payload);

        $api->assertUnauthorized();
    }

    public function test_branch_api_call_update_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory()->setStatusActive()->count(3)))
            ->create();

        $this->actingAs($user);

        $company = $user->companies->first();

        $branch = $company->branches()->inRandomOrder()->first();

        $payload = Branch::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.branch.edit', $branch->ulid), $payload);

        $api->assertForbidden();
    }

    public function test_branch_api_call_update_with_script_tags_in_payload_expect_stripped()
    {
        $this->markTestSkipped('Test under construction');
    }

    public function test_branch_api_call_update_with_script_tags_in_payload_expect_encoded()
    {
        $this->markTestSkipped('Test under construction');
    }

    public function test_branch_api_call_update_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory()->setStatusActive()->count(3)))
            ->create();

        $this->actingAs($user);

        $company = $user->companies->first();

        $branch = $company->branches()->inRandomOrder()->first();

        $payload = Branch::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.branch.edit', $branch->ulid), $payload);

        $api->assertSuccessful();
        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'company_id' => $company->id,
            'code' => $payload['code'],
            'name' => $payload['name'],
        ]);
    }

    public function test_branch_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setIsDefault()
                    ->has(Branch::factory()->setStatusActive()->count(3))
            )->create();

        $this->actingAs($user);

        $company = $user->companies->first();

        $branches = $company->branches()->inRandomOrder()->take(2)->get();
        $branch_1 = $branches[0];
        $branch_2 = $branches[1];

        $payload = Branch::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => $branch_1->code,
        ])->toArray();

        $api = $this->json('POST', route('api.post.branch.edit', $branch_2->ulid), $payload);

        $api->assertUnprocessable();
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_branch_api_call_update_and_use_existing_code_in_different_company_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->count(2)
                ->state(new Sequence(
                    ['default' => true],
                    ['default' => false]
                ))
            )->create();

        $this->actingAs($user);

        $company_1 = $user->companies[0];
        $companyId_1 = $company_1->id;

        $company_2 = $user->companies[1];
        $companyId_2 = $company_2->id;

        Branch::factory()->create([
            'company_id' => $companyId_1,
            'code' => 'test1',
        ]);

        Branch::factory()->create([
            'company_id' => $companyId_2,
            'code' => 'test2',
        ]);

        $payload = Branch::factory()->make([
            'company_id' => Hashids::encode($companyId_2),
            'code' => 'test1',
            'is_main' => true,
            'status' => RecordStatusEnum::ACTIVE->value,
        ])->toArray();

        $api = $this->json('POST', route('api.post.branch.edit', $company_2->branches()->first()->ulid), $payload);

        if ($api->status() !== 200) {
            dd($api->json());
        }

        $api->assertSuccessful();
    }

    public function test_branch_api_call_update_with_auto_code_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory()->setStatusActive()->count(1)))
            ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        $branch = $company->branches()->first();

        $payload = Branch::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => config('dcslab.KEYWORDS.AUTO'),
            'is_main' => true,
            'status' => RecordStatusEnum::ACTIVE->value,
        ])->toArray();

        $api = $this->json('POST', route('api.post.branch.edit', $branch->ulid), $payload);

        $api->assertSuccessful();

        $this->assertDatabaseMissing('branches', [
            'id' => $branch->id,
            'code' => config('dcslab.KEYWORDS.AUTO'),
        ]);

        $branch->refresh();
        $this->assertNotEquals(config('dcslab.KEYWORDS.AUTO'), $branch->code);
    }

    public function test_branch_api_call_update_is_main_expect_other_branches_reset()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory()->count(3)->state(['is_main' => false]))
            )
            ->create();

        $this->actingAs($user);

        $company = $user->companies->first();

        // Make the first branch main initially
        $branches = $company->branches;
        $branches[0]->update(['is_main' => true]);

        // Update the second branch to be main
        $targetBranch = $branches[1];

        $payload = Branch::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => $targetBranch->code,
            'is_main' => true,
            'status' => RecordStatusEnum::ACTIVE->value,
        ])->toArray();

        $api = $this->json('POST', route('api.post.branch.edit', $targetBranch->ulid), $payload);

        $api->assertSuccessful();

        // Check target branch is main
        $this->assertDatabaseHas('branches', [
            'id' => $targetBranch->id,
            'is_main' => true,
        ]);

        // Check previous main branch is not main anymore
        $this->assertDatabaseHas('branches', [
            'id' => $branches[0]->id,
            'is_main' => false,
        ]);
    }
}
