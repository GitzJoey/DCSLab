<?php

namespace Tests\Feature\API\ProductAPI;

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
use Illuminate\Support\Str;
use Tests\APITestCase;

class ProductAPIDeleteTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_product_api_call_delete_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
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

        $api = $this->json('POST', route('api.post.db.product.product.delete', $product->ulid));

        $api->assertSuccessful();
        $this->assertSoftDeleted('products', [
            'id' => $product->id,
        ]);
    }

    public function test_product_api_call_delete_of_nonexistance_ulid_expect_not_found()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $ulid = Str::ulid()->generate();

        $api = $this->json('POST', route('api.post.db.product.product.delete', $ulid));

        $api->assertStatus(404);
    }

    public function test_product_api_call_delete_without_parameters_expect_failed()
    {
        $this->expectException(Exception::class);

        $user = User::factory()->create();

        $this->actingAs($user);

        $this->json('POST', route('api.post.db.product.product.delete', null));
    }
}
