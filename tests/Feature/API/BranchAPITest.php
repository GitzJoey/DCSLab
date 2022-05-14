<?php

namespace Tests\Feature\API;

use App\Models\Branch;
use Tests\APITestCase;
use App\Enums\ActiveStatus;
use App\Actions\RandomGenerator;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BranchAPITest extends APITestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        Parent::setUp();
    }
    
    public function test_api_call_require_authentication()
    {
        $api = $this->getJson('/api/get/dashboard/company/branch/read');
        $this->assertContains($api->getStatusCode(), array(401, 405));

        $api = $this->getJson('/api/post/dashboard/company/branch/save');
        $this->assertContains($api->getStatusCode(), array(401, 405));

        $api = $this->getJson('/api/post/dashboard/company/branch/edit/1');
        $this->assertContains($api->getStatusCode(), array(401, 405));

        $api = $this->getJson('/api/post/dashboard/company/branch/delete/1');
        $this->assertContains($api->getStatusCode(), array(401, 405));
    }

    public function test_api_call_save_with_all_field_filled()
    {
        $this->actingAs($this->developer);

        $companyId = $this->developer->companies->random(1)->first()->id;
        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $contact = $this->faker->e164PhoneNumber;
        $remarks = $this->faker->sentence();
        $status = $this->faker->randomElement(ActiveStatus::toArrayName());

        $api = $this->json('POST', route('api.post.db.company.branch.save'), [
            'company_id' => Hashids::encode($companyId),
            'code' => $code, 
            'name' => $name,
            'address' => $address,
            'city' => $city,
            'contact' => $contact,
            'remarks' => $remarks,
            'status' => $status
        ]);

        $api->assertSuccessful();
        $this->assertDatabaseHas('branches', [
            'company_id' => $companyId,
            'name' => $name,
            'address' => $address,
            'city' => $city,
            'contact' => $contact,
            'remarks' => $remarks,
            'status' => ActiveStatus::fromName($status)
        ]);
    }

    public function test_api_call_save_with_minimal_field_filled()
    {
        $this->actingAs($this->developer);

        $companyId = $this->developer->companies->random(1)->first()->id;
        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $address = null;
        $city = null;
        $contact = null;
        $remarks = null;
        $status = $this->faker->randomElement(ActiveStatus::toArrayName());

        $api = $this->json('POST', route('api.post.db.company.branch.save'), [
            'company_id' => Hashids::encode($companyId),
            'code' => $code, 
            'name' => $name,
            'address' => $address,
            'city' => $city,
            'contact' => $contact,
            'remarks' => $remarks,
            'status' => $status
        ]);

        $api->assertSuccessful();
        $this->assertDatabaseHas('branches', [
            'company_id' => $companyId,
            'name' => $name,
            'address' => $address,
            'city' => $city,
            'contact' => $contact,
            'remarks' => $remarks,
            'status' => ActiveStatus::fromName($status)
        ]);
    }

    public function test_api_call_save_with_existing_code_in_same_company()
    {
        $this->actingAs($this->developer);

        $companyId = $this->developer->companies->random(1)->first()->id;

        $branch = Branch::whereCompanyId($companyId)->inRandomOrder()->first();
        
        $other_branch = Branch::whereCompanyId($companyId)->where('id', '!=', $branch->id)->first();
        if (!$other_branch)
            $this->markTestSkipped('There\'s no other branches');

        $code = $other_branch->code;
        $name = $this->faker->name;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $contact = $this->faker->e164PhoneNumber;
        $remarks = $this->faker->sentence();
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $api = $this->json('POST', route('api.post.db.company.branch.save'), [
            'company_id' => Hashids::encode($companyId),
            'code' => $code, 
            'name' => $name,
            'address' => $address,
            'city' => $city,
            'contact' => $contact,
            'remarks' => $remarks,
            'status' => $status
        ]);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors'
        ]);
    }

    public function test_api_call_save_with_existing_code_in_different_company()
    {
        $this->actingAs($this->developer);

        $company_1 = $this->developer->companies[0];
        $company_2 = $this->developer->companies[1];

        $theCode = (new RandomGenerator())->generateAlphaNumeric(5);

        Branch::create([
            'company_id' => $company_1->id,
            'code' => $theCode, 
            'name' => $this->faker->name,
            'address' => '',
            'city' => '',
            'contact' => '',
            'remarks' => '',
            'status' => 1
        ]);

        //print($company_1->id . '-' . $company_1->code . ' | ' . $company_2->id . '-' . $company_2->code);

        $api = $this->json('POST', route('api.post.db.company.branch.save'), [
            'company_id' => Hashids::encode($company_2->id),
            'code' => $theCode, 
            'name' => $this->faker->name,
            'address' => '',
            'city' => '',
            'contact' => '',
            'remarks' => '',
            'status' => 1
        ]);

        $api->assertSuccessful();
        $this->assertDatabaseHas('branches', [
            'company_id' => $company_2->id,
            'code' => $theCode
        ]);
    }

    public function test_api_call_save_with_null_param()
    {
        $this->actingAs($this->developer);

        $companyId = null;
        $code = null;
        $name = null;
        $name = null;
        $address = null;
        $city = null;
        $contact = null;
        $remarks = null;
        $status = null;

        $api = $this->json('POST', route('api.post.db.company.branch.save'), [
            'company_id' => $companyId,
            'code' => $code, 
            'name' => $name,
            'address' => $address,
            'city' => $city,
            'contact' => $contact,
            'remarks' => $remarks,
            'status' => $status
        ]);

        $api->assertStatus(500);
        $api->assertJsonStructure([
            'message'
        ]);
    }

    public function test_api_call_save_with_empty_string_param()
    {
        $this->actingAs($this->developer);

        $companyId = '';
        $code = '';
        $name = '';
        $name = '';
        $address = '';
        $city = '';
        $contact = '';
        $remarks = '';
        $status = '';

        $api = $this->json('POST', route('api.post.db.company.branch.save'), [
            'company_id' => $companyId,
            'code' => $code, 
            'name' => $name,
            'address' => $address,
            'city' => $city,
            'contact' => $contact,
            'remarks' => $remarks,
            'status' => $status
        ]);

        $api->assertStatus(500);
        $api->assertJsonStructure([
            'message'
        ]);
    }

    public function test_api_call_edit_with_all_field_filled()
    {
        $this->actingAs($this->developer);

        $companyId = $this->developer->companies->random(1)->first()->id;
        $code = (new RandomGenerator())->generateNumber(1,9999);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $contact = $this->faker->e164PhoneNumber;
        $remarks = $this->faker->sentence();
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $branch = Branch::create([
            'company_id' => $companyId,
            'code' => $code, 
            'name' => $name,
            'address' => $address,
            'city' => $city,
            'contact' => $contact,
            'remarks' => $remarks,
            'status' => $status
        ]);

        $branchId = $branch->id;

        $api_edit = $this->json('POST', route('api.post.db.company.branch.edit', [ 'id' => Hashids::encode($branchId) ]), [
            'company_id' => Hashids::encode($companyId),
            'code' => (new RandomGenerator())->generateNumber(1, 9999) . 'new',
            'name' => $this->faker->name,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'contact' => $this->faker->e164PhoneNumber,
            'remarks' => $this->faker->sentence,
            'status' => (new RandomGenerator())->generateNumber(0, 1),
        ]);

        $api_edit->assertSuccessful();
        $this->assertDatabaseHas('branches', [
            'id' => $branchId,
            'company_id' => $companyId
        ]);
    }

    public function test_api_call_edit_with_minimal_field_filled()
    {
        $this->actingAs($this->developer);

        $companyId = $this->developer->companies->random(1)->first()->id;
        $code = (new RandomGenerator())->generateNumber(1,9999);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $contact = $this->faker->e164PhoneNumber;
        $remarks = $this->faker->sentence;
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $branch = Branch::create([
            'company_id' => $companyId,
            'code' => $code, 
            'name' => $name,
            'address' => $address,
            'city' => $city,
            'contact' => $contact,
            'remarks' => $remarks,
            'status' => $status
        ]);

        $branchId = $branch->id;

        $api_edit = $this->json('POST', route('api.post.db.company.branch.edit', [ 'id' => Hashids::encode($branchId) ]), [
            'company_id' => Hashids::encode($companyId),
            'code' => (new RandomGenerator())->generateNumber(1, 9999) . 'new',
            'name' => $this->faker->name,
            'address' => '',
            'city' => '',
            'contact' => '',
            'remarks' => '',
            'status' => (new RandomGenerator())->generateNumber(0, 1),
        ]);

        $api_edit->assertSuccessful();
    }

    public function test_api_call_edit_and_change_the_code_with_existing_code_in_the_same_company()
    {
        $this->actingAs($this->developer);

        $company = $this->developer->companies()->whereHas('branches')->inRandomOrder()->first();
        if (!$company)
            $this->markTestSkipped('No suitable company found');

        $twoBranches = Branch::whereCompanyId($company->id)->inRandomOrder()->take(2)->get();
        if (count($twoBranches) != 2)
            $this->markTestSkipped('Not enough Branch for testing');

        $companyId = Hashids::encode($company->id);
        $code = $twoBranches[1]->code;
        $name = $this->faker->name;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $contact = $this->faker->e164PhoneNumber;
        $remarks = null;
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $api_edit = $this->json('POST', route('api.post.db.company.branch.edit', [ 'id' => Hashids::encode($twoBranches[0]->id) ]), [
            'company_id' => $companyId,
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'city' => $city,
            'contact' => $contact,
            'remarks' => $remarks,
            'status' => $status,
        ]);

        $api_edit->assertStatus(422);
        $api_edit->assertJsonStructure([
            'message',
            'errors'
        ]);
    }

    public function test_api_call_edit_with_null_param()
    {
        $this->actingAs($this->developer);

        $company = $this->developer->companies()->whereHas('branches')->inRandomOrder()->first();

        $branch = Branch::whereCompanyId($company->id)->inRandomOrder()->first();
        if (!$branch)
            $this->markTestSkipped('Branch not found');

        $branchId = Hashids::encode($branch->id);

        $api_edit = $this->json('POST', route('api.post.db.company.branch.edit', [ 'id' => $branchId ]), [
            'company_id' => null,
            'code' => null,
            'name' => null,
            'address' => null,
            'city' => null,
            'contact' => null,
            'remarks' => null,
            'status' => null,
        ]);

        $api_edit->assertStatus(500);
    }

    public function test_api_call_delete()
    {
        $this->actingAs($this->developer);

        $branch = Branch::inRandomOrder()->first();
        if (!$branch)
            $this->markTestSkipped('Branches with company not found');

        $hId = Hashids::encode($branch->id);

        $api = $this->json('POST', route('api.post.db.company.branch.delete', $hId));

        $api->assertSuccessful();
        $this->assertSoftDeleted('branches', [
            'id' => $branch->id
        ]);
    }

    public function test_api_call_delete_nonexistance_id()
    {
        $this->actingAs($this->developer);

        $api = $this->json('POST', route('api.post.db.company.branch.delete', (new RandomGenerator())->generateAlphaNumeric(5)));

        $api->assertStatus(500);
    }

    public function test_api_call_read_with_empty_search()
    {
        $this->actingAs($this->developer);

        $company = $this->developer->companies->random(1)->first();
                
        $companyId = $company->id;
        $search = '';
        $paginate = 1;
        $perPage = 10;

        $api = $this->getJson(route('api.get.db.company.branch.read', [
            'companyId' => Hashids::encode($companyId),
            'search' => $search,
            'paginate' => $paginate,
            'perPage' => $perPage
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data', 
            'links' => [
                'first', 'last', 'prev', 'next'
            ], 
            'meta'=> [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total'
            ]
        ]);
    }

    public function test_api_call_read_with_special_char_in_search()
    {
        $this->actingAs($this->developer);

        $company = $this->developer->companies->random(1)->first();

        $companyId = $company->id;
        $search = " !#$%&'()*+,-./:;<=>?@[\]^_`{|}~";
        $paginate = 1;
        $perPage = 10;

        $api = $this->getJson(route('api.get.db.company.branch.read', [
            'companyId' => Hashids::encode($companyId),
            'search' => $search,
            'paginate' => $paginate,
            'perPage' => $perPage
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data', 
            'links' => [
                'first', 'last', 'prev', 'next'
            ], 
            'meta'=> [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total'
            ]
        ]);
    }

    public function test_api_call_read_with_negative_value_in_perpage_param()
    {
        $this->actingAs($this->developer);

        $company = $this->developer->companies->random(1)->first();

        $companyId = $company->id;
        $search = "";
        $paginate = 1;
        $perPage = -10;

        $api = $this->getJson(route('api.get.db.company.branch.read', [
            'companyId' => Hashids::encode($companyId),
            'search' => $search,
            'paginate' => $paginate,
            'perPage' => $perPage
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data', 
            'links' => [
                'first', 'last', 'prev', 'next'
            ], 
            'meta'=> [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total'
            ]
        ]);
    }

    public function test_api_call_read_without_pagination()
    {
        $this->actingAs($this->developer);

        $company = $this->developer->companies->random(1)->first();

        $companyId = $company->id;
        $search = "";
        $perPage = 10;

        $api = $this->getJson(route('api.get.db.company.branch.read', [
            'companyId' => Hashids::encode($companyId),
            'search' => $search,
            'perPage' => $perPage
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data', 
            'links' => [
                'first', 'last', 'prev', 'next'
            ], 
            'meta'=> [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total'
            ]
        ]);
    }

    public function test_api_call_read_with_null_param()
    {
        $this->actingAs($this->developer);

        $api = $this->getJson(route('api.get.db.company.branch.read', [
            'companyId' => null,
            'search' => null,
            'paginate' => null,
            'perPage' => null
        ]));

        $api->assertStatus(500);
        $api->assertJsonStructure([
            'message'
        ]);
    }
}