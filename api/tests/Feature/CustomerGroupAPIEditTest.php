<?php

namespace Tests\Feature;

use App\Enums\UserRoles;
use App\Models\Company;
use App\Models\CustomerGroup;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class CustomerGroupAPIEditTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_customer_group_api_call_update_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(CustomerGroup::factory())
                    )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $customerGroup = $company->customerGroups()->inRandomOrder()->first();

        $customerGroupArr = CustomerGroup::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.customer.customer_group.edit', $customerGroup->ulid), $customerGroupArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('customer_groups', [
            'id' => $customerGroup->id,
            'company_id' => $company->id,
            'code' => $customerGroupArr['code'],
            'name' => $customerGroupArr['name'],
            'max_open_invoice' => $customerGroupArr['max_open_invoice'],
            'max_outstanding_invoice' => $customerGroupArr['max_outstanding_invoice'],
            'max_invoice_age' => $customerGroupArr['max_invoice_age'],
            'payment_term_type' => $customerGroupArr['payment_term_type'],
            'payment_term' => $customerGroupArr['payment_term'],
            'selling_point' => $customerGroupArr['selling_point'],
            'selling_point_multiple' => $customerGroupArr['selling_point_multiple'],
            'sell_at_cost' => $customerGroupArr['sell_at_cost'],
            'price_markup_percent' => $customerGroupArr['price_markup_percent'],
            'price_markup_nominal' => $customerGroupArr['price_markup_nominal'],
            'price_markdown_percent' => $customerGroupArr['price_markdown_percent'],
            'price_markdown_nominal' => $customerGroupArr['price_markdown_nominal'],
            'round_on' => $customerGroupArr['round_on'],
            'round_digit' => $customerGroupArr['round_digit'],
            'remarks' => $customerGroupArr['remarks'],
        ]);
    }

    public function test_customer_group_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(CustomerGroup::factory()->count(2))
                    )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $customerGroups = $company->customerGroups()->inRandomOrder()->take(2)->get();
        $customerGroup_1 = $customerGroups[0];
        $customerGroup_2 = $customerGroups[1];

        $customerGroupArr = CustomerGroup::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => $customerGroup_2->code,
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.customer.customer_group.edit', $customerGroup_1->ulid), $customerGroupArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_customer_group_api_call_update_and_use_existing_code_in_different_company_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(CustomerGroup::factory()))
                    ->has(Company::factory()->setStatusActive()
                        ->has(CustomerGroup::factory())
                    )->create();

        $this->actingAs($user);

        $companies = $user->companies()->inRandomOrder()->get();

        $company_1 = $companies[0];
        CustomerGroup::factory()->for($company_1)->create([
            'code' => 'test1',
        ]);

        $company_2 = $companies[1];
        $customerGroup_2 = CustomerGroup::factory()->for($company_2)->create([
            'code' => 'test2',
        ]);

        $customerGroupArr = CustomerGroup::factory()->make([
            'company_id' => Hashids::encode($company_2->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.customer.customer_group.edit', $customerGroup_2->ulid), $customerGroupArr);

        $api->assertSuccessful();
    }
}
