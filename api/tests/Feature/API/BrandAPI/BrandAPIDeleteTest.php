<?php

namespace Tests\Feature\API\BrandAPI;

use App\Enums\UserRolesEnum;
use App\Models\Brand;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Support\Str;
use Tests\APITestCase;

class BrandAPIDeleteTest extends APITestCase
{
    public function test_brand_api_call_delete_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();
        $brand = Brand::factory()->for($company)->create();

        $api = $this->json('POST', route('api.post.brand.delete', $brand->ulid));

        $api->assertUnauthorized();
    }

    public function test_brand_api_call_delete_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $brand = Brand::factory()->for($company)->create();

        $api = $this->json('POST', route('api.post.brand.delete', $brand->ulid));

        $api->assertForbidden();
    }

    public function test_brand_api_call_delete_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $brand = Brand::factory()->for($company)->create();

        $api = $this->json('POST', route('api.post.brand.delete', $brand->ulid));

        $api->assertSuccessful();
        $this->assertSoftDeleted('brands', [
            'id' => $brand->id,
        ]);
    }

    public function test_brand_api_call_delete_of_nonexistance_ulid_expect_not_found()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->create();

        $this->actingAs($user);

        $ulid = Str::ulid()->generate();

        $api = $this->json('POST', route('api.post.brand.delete', $ulid));

        $api->assertStatus(404);
    }

    public function test_brand_api_call_delete_without_parameters_expect_failed()
    {
        $this->expectException(Exception::class);
        $user = User::factory()->create();

        $this->actingAs($user);
        $api = $this->json('POST', route('api.post.brand.delete', null));
    }

    public function test_brand_api_call_delete_with_sql_injection_expect_not_found()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

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
        $injection = $injections[$testIdx];

        $api = $this->json('POST', route('api.post.brand.delete', $injection));

        $api->assertStatus(404);
    }
}
