<?php

namespace Tests\Feature;

use App\Enums\UserRoles;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BranchAPIDeleteTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_branch_api_call_delete_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory()))
                    ->create();

        $this->actingAs($user);

        $branch = $user->companies()->inRandomOrder()->first()
                    ->branches()->inRandomOrder()->first();

        $api = $this->json('POST', route('api.post.db.company.branch.delete', $branch->ulid));

        $api->assertSuccessful();
        $this->assertSoftDeleted('branches', [
            'id' => $branch->id,
        ]);
    }

    public function test_branch_api_call_delete_of_nonexistance_ulid_expect_not_found()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()->create();

        $this->actingAs($user);
        $ulid = $this->faker->uuid();

        $api = $this->json('POST', route('api.post.db.company.branch.delete', $ulid));

        $api->assertStatus(404);
    }

    public function test_branch_api_call_delete_without_parameters_expect_failed()
    {
        $this->expectException(Exception::class);
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()->create();

        $this->actingAs($user);
        $api = $this->json('POST', route('api.post.db.company.branch.delete', null));
    }
}
