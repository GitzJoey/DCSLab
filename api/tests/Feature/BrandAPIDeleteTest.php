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

class BrandAPIDeleteTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_brand_api_call_delete_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(Brand::factory()->count(2))
                    )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $brand = $company->brands()->inRandomOrder()->first();

        $api = $this->json('POST', route('api.post.db.product.brand.delete', $brand->ulid));

        $api->assertSuccessful();
        $this->assertSoftDeleted('brands', [
            'id' => $brand->id,
        ]);
    }

    public function test_brand_api_call_delete_of_nonexistance_ulid_expect_not_found()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()->create();

        $this->actingAs($user);
        $ulid = $this->faker->uuid();

        $api = $this->json('POST', route('api.post.db.product.brand.delete', $ulid));

        $api->assertStatus(404);
    }

    public function test_brand_api_call_delete_without_parameters_expect_failed()
    {
        $this->expectException(Exception::class);
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()->create();

        $this->actingAs($user);
        $api = $this->json('POST', route('api.post.db.product.brand.delete', null));
    }
}
