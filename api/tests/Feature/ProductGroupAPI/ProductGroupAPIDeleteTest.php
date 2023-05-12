<?php

namespace Tests\Feature\ProductGroupAPI;

use Exception;
use App\Models\Role;
use App\Models\User;
use Tests\APITestCase;
use App\Models\Company;
use App\Enums\UserRoles;
use Illuminate\Support\Str;
use App\Models\ProductGroup;

class ProductGroupAPIDeleteTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_product_group_api_call_delete_expect_successful()
    {
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(ProductGroup::factory())
                    )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $productGroup = $company->productGroups()->inRandomOrder()->first();

        $api = $this->json('POST', route('api.post.db.product.product_group.delete', $productGroup->ulid));

        $api->assertSuccessful();
        $this->assertSoftDeleted('product_groups', [
            'id' => $productGroup->id,
        ]);
    }

    public function test_product_group_api_call_delete_of_nonexistance_ulid_expect_not_found()
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        
        $ulid = Str::ulid()->generate();

        $api = $this->json('POST', route('api.post.db.product.product_group.delete', $ulid));

        $api->assertStatus(404);
    }

    public function test_product_group_api_call_delete_without_parameters_expect_failed()
    {
        $this->expectException(Exception::class);
        $user = User::factory()->create();

        $this->actingAs($user);
        $api = $this->json('POST', route('api.post.db.product.product_group.delete', null));
    }
}
