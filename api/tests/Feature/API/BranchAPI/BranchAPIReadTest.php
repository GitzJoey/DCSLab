<?php

namespace Tests\Feature\API\BranchAPI;

use App\Enums\RecordStatusEnum;
use App\Enums\UserRolesEnum;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Str;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class BranchAPIReadTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_branch_api_call_read_any_without_authorization_expect_unauthorized_message()
    {
        $branchCount = 2;
        $idxMainBranch = random_int(0, $branchCount - 1);

        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()
                ->has(Branch::factory()->setStatusActive()->count($branchCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'is_main' => $sequence->index == $idxMainBranch ? true : false,
                        ]
                    ))
                ))
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $api = $this->getJson(route('api.get.branch.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
            'search' => '',
            'refresh' => true,
            'paginate' => [
                'page' => 1,
                'per_page' => 10,
            ],
        ]));

        $api->assertUnauthorized();
    }

    public function test_branch_api_call_read_any_without_access_right_expect_forbidden_message()
    {
        $branchCount = 2;
        $idxMainBranch = random_int(0, $branchCount - 1);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()
                ->has(Branch::factory()->setStatusActive()->count($branchCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'is_main' => $sequence->index == $idxMainBranch ? true : false,
                        ]
                    ))
                ))
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $api = $this->getJson(route('api.get.branch.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
            'search' => '',
            'refresh' => true,
            'paginate' => [
                'page' => 1,
                'per_page' => 10,
            ],
        ]));

        $api->assertForbidden();
    }

    public function test_branch_api_call_read_without_authorization_expect_unauthorized_message()
    {
        $branchCount = 2;
        $idxMainBranch = random_int(0, $branchCount - 1);

        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()
                ->has(Branch::factory()->setStatusActive()->count($branchCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'is_main' => $sequence->index == $idxMainBranch ? true : false,
                        ]
                    ))
                ))
            ->create();

        $company = $user->companies->first();

        $ulid = $company->branches()->inRandomOrder()->first()->ulid;

        $api = $this->getJson(route('api.get.branch.read', $ulid));

        $api->assertUnauthorized();
    }

    public function test_branch_api_call_read_with_sql_injection_expect_injection_ignored()
    {
        $branchCount = 2;
        $idxMainBranch = random_int(0, $branchCount - 1);

        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()
                ->has(Branch::factory()->setStatusActive()->count($branchCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'is_main' => $sequence->index == $idxMainBranch ? true : false,
                        ]
                    ))
                ))
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $injections = [
            "' OR '1'='1",
            '1 UNION SELECT username, password FROM users',
            '1; DROP TABLE users',
            "' OR '1'='1' --",
            '1 OR SLEEP(5)',
            "1; INSERT INTO logs (message) VALUES ('Injected SQL query')",
            "1; UPDATE users SET password = 'hacked' WHERE id = 1; --",
            "admin'--",
            "' OR 1=1 --",
        ];

        $testIdx = random_int(0, count($injections) - 1);

        $api = $this->getJson(route('api.get.branch.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
            'search' => $injections[$testIdx],
            'refresh' => true,
            'paginate' => [
                'page' => 1,
                'per_page' => 10,
            ],
        ]));

        $api->assertSuccessful();

        $api->assertJsonFragment([
            'total' => 0,
        ]);

        $api->assertJsonStructure([
            'data',
            'links' => [
                'first', 'last', 'prev', 'next',
            ],
            'meta' => [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
            ],
        ]);

        $testIdx = random_int(0, count($injections) - 1);

        $api = $this->getJson(route('api.get.branch.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
            'search' => $injections[$testIdx],
            'refresh' => true,
            'get' => [
                'limit' => 10,
            ],
        ]));

        $api->assertSuccessful();

        $api->assertJsonFragment([
            'data' => [],
        ]);
    }

    public function test_branch_api_call_read_without_access_right_expect_forbidden_message()
    {
        $branchCount = 2;
        $idxMainBranch = random_int(0, $branchCount - 1);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()
                ->has(Branch::factory()->setStatusActive()->count($branchCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'is_main' => $sequence->index == $idxMainBranch ? true : false,
                        ]
                    ))
                ))
            ->create();

        $this->actingAs($user);

        $company = $user->companies->first();

        $ulid = $company->branches()->inRandomOrder()->first()->ulid;

        $api = $this->getJson(route('api.get.branch.read', $ulid));

        $api->assertForbidden();
    }

    public function test_branch_api_call_read_any_with_or_without_pagination_expect_paginator_or_collection()
    {
        $branchCount = 2;
        $idxMainBranch = random_int(0, $branchCount - 1);

        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()
                ->has(Branch::factory()->setStatusActive()->count($branchCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'is_main' => $sequence->index == $idxMainBranch ? true : false,
                        ]
                    ))
                ))
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $api = $this->getJson(route('api.get.branch.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
            'search' => '',
            'refresh' => true,
            'paginate' => [
                'page' => 1,
                'per_page' => 10,
            ],
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data',
            'links' => [
                'first', 'last', 'prev', 'next',
            ],
            'meta' => [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
            ],
        ]);

        $api = $this->getJson(route('api.get.branch.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
            'search' => '',
            'refresh' => true,
            'get' => [
                'limit' => 10,
            ],
        ]));

        $api->assertSuccessful();
    }

    public function test_branch_api_call_read_any_with_pagination_expect_several_per_page()
    {
        $branchCount = 2;
        $idxMainBranch = random_int(0, $branchCount - 1);

        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()
                ->has(Branch::factory()->setStatusActive()->count($branchCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'is_main' => $sequence->index == $idxMainBranch ? true : false,
                        ]
                    ))
                ))
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $api = $this->getJson(route('api.get.branch.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
            'search' => '',
            'refresh' => true,
            'paginate' => [
                'page' => 1,
                'per_page' => 25,
            ],
        ]));

        $api->assertSuccessful();

        $api->assertJsonFragment([
            'per_page' => 25,
        ]);

        $api->assertJsonStructure([
            'data',
            'links' => [
                'first', 'last', 'prev', 'next',
            ],
            'meta' => [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
            ],
        ]);
    }

    public function test_branch_api_call_read_any_with_search_expect_filtered_results()
    {
        $branchCount = 4;
        $idxMainBranch = random_int(0, $branchCount - 1);
        $idxTest = random_int(0, $branchCount - 1);
        $defaultName = Branch::factory()->make()->name;
        $testName = Branch::factory()->insertStringInName('testing')->make()->name;

        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory()->setStatusActive()->count($branchCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'is_main' => $sequence->index == $idxMainBranch ? true : false,
                            'name' => $sequence->index == $idxTest ? $testName : $defaultName,
                        ]
                    ))
                )
            )
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $api = $this->getJson(route('api.get.branch.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
            'search' => 'testing',
            'refresh' => true,
            'paginate' => [
                'page' => 1,
                'per_page' => 10,
            ],
        ]));
        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data',
            'links' => [
                'first', 'last', 'prev', 'next',
            ],
            'meta' => [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
            ],
        ]);

        $api->assertJsonFragment([
            'total' => 1,
        ]);
    }

    public function test_branch_api_call_read_any_without_search_querystring_expect_failed()
    {
        $branchCount = 2;
        $idxMainBranch = random_int(0, $branchCount - 1);

        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()
                ->has(Branch::factory()->setStatusActive()->count($branchCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'is_main' => $sequence->index == $idxMainBranch ? true : false,
                        ]
                    ))
                ))
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $api = $this->getJson(route('api.get.branch.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
        ]));

        $api->assertUnprocessable();
    }

    public function test_branch_api_call_read_any_with_special_char_in_search_expect_results()
    {
        $branchCount = 5;
        $idxMainBranch = random_int(0, $branchCount - 1);

        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()
                ->has(Branch::factory()->setStatusActive()->count($branchCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'is_main' => $sequence->index == $idxMainBranch ? true : false,
                        ]
                    ))
                ))
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $api = $this->getJson(route('api.get.branch.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
            'search' => " !#$%&'()*+,-./:;<=>?@[\]^_`{|}~",
            'refresh' => false,
            'paginate' => [
                'page' => 1,
                'per_page' => 10,
            ],
        ]));
        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data',
            'links' => [
                'first', 'last', 'prev', 'next',
            ],
            'meta' => [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
            ],
        ]);
    }

    public function test_branch_api_call_read_any_with_negative_value_in_parameters_expect_results()
    {
        $branchCount = 2;
        $idxMainBranch = random_int(0, $branchCount - 1);

        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()
                ->has(Branch::factory()->setStatusActive()->count($branchCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'is_main' => $sequence->index == $idxMainBranch ? true : false,
                        ]
                    ))
                ))
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $api = $this->getJson(route('api.get.branch.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
            'search' => '',
            'refresh' => false,
            'paginate' => [
                'page' => -1,
                'per_page' => -10,
            ],
        ]));

        $api->assertStatus(422);
    }

    public function test_branch_api_call_read_expect_successful()
    {
        $branchCount = 3;
        $idxMainBranch = random_int(0, $branchCount - 1);

        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()
                ->has(Branch::factory()->setStatusActive()->count($branchCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'is_main' => $sequence->index == $idxMainBranch ? true : false,
                        ]
                    ))
                ))
            ->create();

        $this->actingAs($user);

        $company = $user->companies->first();

        $ulid = $company->branches()->inRandomOrder()->first()->ulid;

        $api = $this->getJson(route('api.get.branch.read', $ulid));

        $api->assertSuccessful();
    }

    public function test_branch_api_call_read_without_ulid_expect_exception()
    {
        $this->expectException(Exception::class);

        $branchCount = 3;
        $idxMainBranch = random_int(0, $branchCount - 1);

        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()
                ->has(Branch::factory()->setStatusActive()->count($branchCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'is_main' => $sequence->index == $idxMainBranch ? true : false,
                        ]
                    ))
                ))
            ->create();

        $this->actingAs($user);

        $this->getJson(route('api.get.branch.read', null));
    }

    public function test_branch_api_call_read_with_nonexistance_ulid_expect_not_found()
    {
        $branchCount = 3;
        $idxMainBranch = random_int(0, $branchCount - 1);

        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()
                ->has(Branch::factory()->setStatusActive()->count($branchCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'is_main' => $sequence->index == $idxMainBranch ? true : false,
                        ]
                    ))
                ))
            ->create();

        $this->actingAs($user);

        $ulid = Str::ulid()->generate();

        $api = $this->getJson(route('api.get.branch.read', $ulid));

        $api->assertStatus(404);
    }

    public function test_branch_api_call_read_any_with_status_filter_expect_filtered_results()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()
                ->has(Branch::factory()->setStatusActive()->count(2))
                ->has(Branch::factory()->setStatusInactive()->count(2))
            )
            ->create();

        $this->actingAs($user);
        $company = $user->companies()->first();

        $api = $this->getJson(route('api.get.branch.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
            'status' => RecordStatusEnum::ACTIVE->value,
            'refresh' => true,
            'get' => ['limit' => 10],
        ]));

        $api->assertSuccessful();
        $api->assertJsonCount(2, 'data');
    }

    public function test_branch_api_call_read_any_with_is_main_filter_expect_filtered_results()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()
                ->has(Branch::factory()->setIsMainBranch(true)->count(1))
                ->has(Branch::factory()->setIsMainBranch(false)->count(3))
            )
            ->create();

        $this->actingAs($user);
        $company = $user->companies()->first();

        $api = $this->getJson(route('api.get.branch.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
            'is_main' => true,
            'refresh' => true,
            'get' => ['limit' => 10],
        ]));

        $api->assertSuccessful();
        $api->assertJsonCount(1, 'data');
        $this->assertTrue($api->json('data.0.is_main'));
    }

    public function test_branch_api_call_read_any_with_invalid_params_expect_validation_error()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive())
            ->create();

        $this->actingAs($user);
        $company = $user->companies()->first();

        $api = $this->getJson(route('api.get.branch.read_any', [
            'with_trashed' => false,
            'company_id' => 'invalid-hashid',
            'refresh' => true,
            'get' => ['limit' => 10],
        ]));

        $api->assertJsonValidationErrors(['company_id']);
    }

    public function test_branch_api_call_read_any_with_include_id_expect_included_result()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()
                ->has(Branch::factory()->setStatusActive()->count(15))
            )
            ->create();

        $this->actingAs($user);
        $company = $user->companies()->first();

        // Get a branch that would typically be on the second page (assuming per_page=10)
        // Since default sort is name asc, and include_id forces it to top
        $lastBranch = $company->branches()->orderBy('name', 'asc')->get()->last();

        $api = $this->getJson(route('api.get.branch.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
            'include_id' => Hashids::encode($lastBranch->id),
            'refresh' => true,
            'paginate' => [
                'page' => 1,
                'per_page' => 10,
            ],
        ]));

        $api->assertSuccessful();

        // Check if the included branch is present in the first page
        $data = $api->json('data');
        $this->assertTrue(collect($data)->contains('ulid', $lastBranch->ulid));
    }
}
