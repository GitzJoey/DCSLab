<?php

namespace Tests\Feature\API\BranchAPI;

use App\Enums\UserRolesEnum;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Support\Str;
use Tests\APITestCase;

class BranchAPIDeleteTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_branch_api_call_delete_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setIsDefault()
                ->has(Branch::factory()))
            ->create();

        $branch = $user->companies()->inRandomOrder()->first()
            ->branches()->inRandomOrder()->first();

        $api = $this->json('POST', route('api.post.branch.delete', $branch->ulid));

        $api->assertUnauthorized();
    }

    public function test_branch_api_call_delete_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory()))
            ->create();

        $this->actingAs($user);

        $branch = $user->companies()->inRandomOrder()->first()
            ->branches()->inRandomOrder()->first();

        $api = $this->json('POST', route('api.post.branch.delete', $branch->ulid));

        $api->assertForbidden();
    }

    public function test_branch_api_call_delete_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setIsDefault()
                ->has(Branch::factory()))
            ->create();

        $this->actingAs($user);

        $branch = $user->companies()->inRandomOrder()->first()
            ->branches()->inRandomOrder()->first();

        $api = $this->json('POST', route('api.post.branch.delete', $branch->ulid));

        $api->assertSuccessful();
        $this->assertSoftDeleted('branches', [
            'id' => $branch->id,
        ]);
    }

    public function test_branch_api_call_delete_of_nonexistance_ulid_expect_not_found()
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $ulid = Str::ulid()->generate();

        $api = $this->json('POST', route('api.post.branch.delete', $ulid));

        $api->assertStatus(404);
    }

    public function test_branch_api_call_delete_without_parameters_expect_failed()
    {
        $this->expectException(Exception::class);
        $user = User::factory()->create();

        $this->actingAs($user);
        $api = $this->json('POST', route('api.post.branch.delete', null));
    }

    public function test_branch_api_call_delete_main_branch_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setIsDefault()
                ->has(Branch::factory()->setIsMainBranch(true)))
            ->create();

        $this->actingAs($user);

        $branch = $user->companies()->first()->branches()->first();

        $api = $this->json('POST', route('api.post.branch.delete', $branch->ulid));

        $api->assertUnprocessable();
        $api->assertJsonValidationErrors(['' => trans('rules.branch.delete_main_branch')]);
    }
}
