<?php

namespace Tests\Feature;

use App\Enums\UserRoles;
use App\Models\Brand;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BrandAPIReadTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_brand_api_call_read_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(Brand::factory())
                    )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $ulid = $company->brands()->inRandomOrder()->first()->ulid;

        $api = $this->getJson(route('api.get.db.product.brand.read', $ulid));

        $api->assertSuccessful();
    }

    public function test_brand_api_call_read_without_ulid_expect_exception()
    {
        $this->expectException(Exception::class);
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $this->actingAs($user);

        $this->getJson(route('api.get.db.product.brand.read', null));
    }

    public function test_brand_api_call_read_with_nonexistance_ulid_expect_not_found()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(Brand::factory())
                    )
                    ->create();

        $this->actingAs($user);

        $ulid = $this->faker->uuid();

        $api = $this->getJson(route('api.get.db.product.brand.read', $ulid));

        $api->assertStatus(404);
    }
}
