<?php

namespace Tests\Feature;

use App\Enums\ProductGroupCategory;
use App\Enums\UnitCategory;
use App\Enums\UserRoles;
use App\Models\Brand;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\ProductUnit;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductAPIReadTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_product_api_call_read_product_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                        ->has(Brand::factory()->count(5))
                        ->has(Unit::factory()->setCategoryToProduct()->count(5))
                    )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $productGroup = $company->productGroups()
                            ->where('category', '=', ProductGroupCategory::PRODUCTS->value)
                            ->inRandomOrder()->first();

        $brand = $company->brands()->inRandomOrder()->first();

        $product = Product::factory()
                    ->for($company)
                    ->for($productGroup)
                    ->for($brand)
                    ->setProductTypeAsProduct();

        $units = $company->units()->where('category', '=', UnitCategory::PRODUCTS->value)
                    ->inRandomOrder()->get()->shuffle();

        $productUnitCount = random_int(1, $units->count());
        $primaryUnitIdx = random_int(0, $productUnitCount - 1);

        for ($j = 0; $j < $productUnitCount; $j++) {
            $product = $product->has(
                ProductUnit::factory()
                    ->for($company)->for($units[$j])
                    ->setConversionValue($j == 0 ? 1 : random_int(2, 10))
                    ->setIsPrimaryUnit($j == $primaryUnitIdx)
            );
        }

        $product = $product->create();

        $api = $this->getJson(route('api.get.db.product.product.read', $product->ulid));

        $api->assertSuccessful();
    }

    public function test_product_api_call_read_service_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(ProductGroup::factory()->setCategoryToService()->count(5))
                        ->has(Unit::factory()->setCategoryToService()->count(5))
                    )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $productGroup = $company->productGroups()
                            ->where('category', '=', ProductGroupCategory::SERVICES->value)
                            ->inRandomOrder()->first();

        $service = Product::factory()
                    ->for($company)
                    ->for($productGroup)
                    ->setProductTypeAsService();

        $unit = $company->units()->where('category', '=', UnitCategory::SERVICES->value)
                    ->inRandomOrder()->first();

        $service = $service->has(
            ProductUnit::factory()
                ->for($company)->for($unit)
                ->setConversionValue(1)
                ->setIsPrimaryUnit(true)
        );

        $service = $service->create();

        $api = $this->getJson(route('api.get.db.product.service.read', $service->ulid));

        $api->assertSuccessful();
    }

    public function test_product_api_call_read_without_ulid_expect_exception()
    {
        $this->expectException(Exception::class);
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                        ->has(Brand::factory()->count(5))
                        ->has(Unit::factory()->setCategoryToProduct()->count(5))
                    )->create();

        $this->actingAs($user);

        $this->getJson(route('api.get.db.product.product.read', null));
    }

    public function test_product_api_call_read_with_nonexistance_ulid_expect_not_found()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                        ->has(Brand::factory()->count(5))
                        ->has(Unit::factory()->setCategoryToProduct()->count(5))
                    )->create();

        $this->actingAs($user);

        $ulid = $this->faker->uuid();

        $api = $this->getJson(route('api.get.db.product.product.read', $ulid));

        $api->assertStatus(404);
    }
}
