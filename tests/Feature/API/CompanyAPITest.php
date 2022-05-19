<?php

namespace Tests\Feature\API;

use Carbon\Carbon;
use App\Models\User;
use Tests\APITestCase;
use App\Models\Company;
use App\Actions\RandomGenerator;
use App\Enums\ActiveStatus;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\CompanyTableSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CompanyAPITest extends APITestCase
{
    use WithFaker;

    public function test_api_call_require_authentication()
    {
        $api = $this->getJson('/api/get/dashboard/company/company/read');
        $this->assertContains($api->getStatusCode(), array(401, 405));

        $api = $this->getJson('/api/post/dashboard/company/company/save');
        $this->assertContains($api->getStatusCode(), array(401, 405));

        $api = $this->getJson('/api/post/dashboard/company/company/edit/1');
        $this->assertContains($api->getStatusCode(), array(401, 405));

        $api = $this->getJson('/api/post/dashboard/company/company/delete/1');
        $this->assertContains($api->getStatusCode(), array(401, 405));
    }

    public function test_api_call_save_with_all_field_filled()
    {
        $this->actingAs($this->developer);

        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $default = 0;
        $status = $this->faker->randomElement(ActiveStatus::toArrayName());
        $userId = $this->developer->id;

        $api = $this->json('POST', route('api.post.db.company.company.save'), [
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'default' => $default,
            'status' => $status,
            'userId' => $userId
        ]);

        $api->assertSuccessful();
        $this->assertDatabaseHas('companies', [
            'code' => $code,
            'name' => $name
        ]);
    }

    public function test_api_call_save_with_minimal_field_filled()
    {
        $this->actingAs($this->developer);

        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $address = null;
        $default = 0;
        $status = $this->faker->randomElement(ActiveStatus::toArrayName());
        $userId = $this->developer->id;

        $api = $this->json('POST', route('api.post.db.company.company.save'), [
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'default' => $default,
            'status' => $status,
            'userId' => $userId
        ]);

        $api->assertSuccessful();
        $this->assertDatabaseHas('companies', [
            'code' => $code,
            'name' => $name
        ]);
    }

    public function test_api_call_save_with_existing_code()
    {
        $this->actingAs($this->developer);

        $code = $this->developer->companies->random(1)->first()->code;
        $name = $this->faker->name;
        $address = $this->faker->address;
        $default = 1;
        $status = 'ACTIVE';
        $userId = $this->developer->id;

        $api = $this->json('POST', route('api.post.db.company.company.save'), [
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'default' => $default,
            'status' => $status,
            'userId' => $userId
        ]);

        $api->assertStatus(422);       
        $api->assertJsonStructure([
            'errors'
        ]);
    }

    public function test_api_call_save_with_empty_string_param()
    {
        $this->actingAs($this->developer);

        $code = '';
        $name = '';
        $address = '';
        $default = '';
        $status = '';
        $userId = '';

        $api = $this->json('POST', route('api.post.db.company.company.save'), [
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'default' => $default,
            'status' => $status,
            'userId' => $userId
        ]);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'message'
        ]);
    }

    public function test_api_call_edit_with_all_field_filled()
    {
        $this->actingAs($this->developer);

        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $default = 0;
        $status = $this->faker->randomElement(ActiveStatus::toArrayValue());
        $userId = $this->developer->id;

        $company = Company::create([
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'default' => $default,
            'status' => $status,
            'userId' => $userId
        ]);
        $companyId = $company->id;
        $this->developer->companies()->attach([$companyId]);

        $newCode = (new RandomGenerator())->generateNumber(1, 9999) . 'new';
        $newName = $this->faker->name;
        $newAddress = $this->faker->address;
        $newDefault = 0;
        $newStatus = $this->faker->randomElement(ActiveStatus::toArrayName());

        $api_edit = $this->json('POST', route('api.post.db.company.company.edit', [ 'id' => Hashids::encode($companyId) ]), [
            'company_id' => Hashids::encode($companyId),
            'code' => $newCode,
            'name' => $newName,
            'address' => $newAddress,
            'default' => $newDefault,
            'status' => $newStatus
        ]);

        $api_edit->assertSuccessful();
        $this->assertDatabaseHas('companies', [
            'id' => $companyId,
            'code' => $newCode,
            'name' => $newName
        ]);
    }

    public function test_api_call_edit_with_minimal_field_filled()
    {
        $this->actingAs($this->developer);

        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $address = null;
        $default = 0;
        $status = $this->faker->randomElement(ActiveStatus::toArrayValue());
        $userId = $this->developer->id;

        $company = Company::create([
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'default' => $default,
            'status' => $status,
            'userId' => $userId
        ]);
        $companyId = $company->id;
        $this->developer->companies()->attach([$companyId]);

        $newCode = (new RandomGenerator())->generateAlphaNumeric(5) . 'new';
        $newName = $this->faker->name;
        $newAddress = null;
        $newDefault = 0;
        $newStatus = $this->faker->randomElement(ActiveStatus::toArrayName());

        $api_edit = $this->json('POST', route('api.post.db.company.company.edit', [ 'id' => Hashids::encode($companyId) ]), [
            'company_id' => Hashids::encode($companyId),
            'code' => $newCode,
            'name' => $newName,
            'address' => $newAddress,
            'default' => $newDefault,
            'status' => $newStatus
        ]);

        $api_edit->assertSuccessful();
        $this->assertDatabaseHas('companies', [
            'id' => $companyId,
            'code' => $newCode,
            'name' => $newName,
            'address' => '',
            'default' => $newDefault,
            'status' => ActiveStatus::fromName($newStatus)
        ]);
    }

    public function test_api_call_edit_with_existing_code()
    {
        $this->actingAs($this->developer);

        $companyId = $this->developer->companies->random(1)->first()->id;

        $newCode = $this->developer->companies->random(1)->whereNotIn('id', [$companyId])->first()->code;
        $newName = $this->faker->name;
        $newAddress = $this->faker->address;
        $newDefault = 0;
        $newStatus = $this->faker->randomElement(ActiveStatus::toArrayName());

        $api_edit = $this->json('POST', route('api.post.db.company.company.edit', [ 'id' => Hashids::encode($companyId) ]), [
            'company_id' => Hashids::encode($companyId),
            'code' => $newCode,
            'name' => $newName,
            'address' => $newAddress,
            'default' => $newDefault,
            'status' => $newStatus
        ]);

        $api_edit->assertStatus(422);
        $api_edit->assertJsonStructure([
            'errors'
        ]);
    }

    public function test_api_call_delete()
    {
        $this->actingAs($this->developer);

        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $default = 0;
        $status = 1;
        $userId = $this->developer->id;

        $company = Company::create([
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'default' => $default,
            'status' => $status,
            'userId' => $userId
        ]);
        $companyId = $company->id;

        $api = $this->json('POST', route('api.post.db.company.company.delete', Hashids::encode($companyId)));

        $api->assertSuccessful();
        $this->assertSoftDeleted('companies', [
            'id' => $companyId
        ]);
    }

    public function test_api_call_delete_nonexistance_id()
    {
        $this->actingAs($this->developer);

        $api = $this->json('POST', route('api.post.db.company.company.delete', (new RandomGenerator())->generateAlphaNumeric(5)));

        $api->assertStatus(500);
        $api->assertJsonStructure([
            'message'
        ]);
    }

    public function test_api_call_delete_default_company()
    {
        $this->actingAs($this->developer);

        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $default = 1;
        $status = 1;
        $userId = $this->developer->id;

        $company = Company::create([
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'default' => $default,
            'status' => $status,
            'userId' => $userId
        ]);
        $this->developer->companies()->attach([$company->id]);

        $companyId = $this->developer->companies->where('default', '=', 1)->first()->id;
        $api = $this->json('POST', route('api.post.db.company.company.delete', Hashids::encode($companyId)));

        $api->assertStatus(500);
        $api->assertJsonStructure([
            'message'
        ]);
    }

    public function test_api_call_read_when_user_have_companies_read_with_empty_search()
    {
        $this->actingAs($this->developer);

        $userId = $this->developer->id;
        $search = "";
        $paginate = 1;
        $page = 1;
        $perPage = 10;

        $api = $this->getJson(route('api.get.db.company.company.read', [
            'userId' => $userId,
            'search' => $search,
            'paginate' => $paginate,
            'page' => $page,
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

    public function test_api_call_read_when_user_have_companies_with_special_char_in_search()
    {
        $this->actingAs($this->developer);

        $userId = $this->developer->id;
        $search = " !#$%&'()*+,-./:;<=>?@[\]^_`{|}~";
        $paginate = 1;
        $page = 1;
        $perPage = 10;

        $api = $this->getJson(route('api.get.db.company.company.read', [
            'userId' => $userId,
            'search' => $search,
            'paginate' => $paginate,
            'page' => $page,
            'perPage' => $perPage,          
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

    public function test_api_call_read_when_user_have_companies_with_negative_value_in_perpage_param()
    {
        $this->actingAs($this->developer);

        $userId = $this->developer->id;
        $search = '';
        $paginate = 1;
        $page = 1;
        $perPage = -10;

        $api = $this->getJson(route('api.get.db.company.company.read', [
            'userId' => $userId,
            'search' => $search,
            'paginate' => $paginate,
            'page' => $page,
            'perPage' => $perPage,
            
        ]));

        $api->assertStatus(200);
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

    public function test_api_call_read_when_user_have_companies_without_pagination()
    {
        $this->actingAs($this->developer);

        $userId = $this->developer->id;
        $search = '';
        $page = 1;
        $perPage = 10;

        $api = $this->getJson(route('api.get.db.company.company.read', [
            'userId' => $userId,
            'search' => $search,
            'page' => $page,
            'perPage' => $perPage,
            
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

    public function test_api_call_read_when_user_doesnt_have_companies_with_empty_search()
    {
        $user = new User();
        $user->name = 'testing';
        $user->email = $this->faker->email;
        $user->password = Hash::make("abcde12345");
        $user->password_changed_at = Carbon::now();
        $user->save();

        $this->actingAs($user);

        $userId = $user->id;
        $search = '';
        // $paginate = (new RandomGenerator())->generateNumber(0, 1);
        $paginate = 1;
        $page = 1;
        $perPage = 10;

        $api = $this->getJson(route('api.get.db.company.company.read', [
            'userId' => $userId,
            'search' => $search,
            'paginate' => $paginate,
            'page' => $page,
            'perPage' => $perPage,
            
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

    public function test_api_call_read_when_user_doesnt_have_companies_with_special_char_in_search()
    {
        $user = new User();
        $user->name = 'testing';
        $user->email = $this->faker->email;
        $user->password = Hash::make("abcde12345");
        $user->password_changed_at = Carbon::now();
        $user->save();

        $this->actingAs($user);

        $userId = $user->id;
        $search = " !#$%&'()*+,-./:;<=>?@[\]^_`{|}~";
        $paginate = 1;
        $page = 1;
        $perPage = 10;

        $api = $this->getJson(route('api.get.db.company.company.read', [
            'userId' => $userId,
            'search' => $search,
            'paginate' => $paginate,
            'page' => $page,
            'perPage' => $perPage,
            
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

    public function test_api_call_read_when_user_doesnt_have_companies_with_negative_value_in_perpage_param()
    {
        $user = new User();
        $user->name = 'testing';
        $user->email = $this->faker->email;
        $user->password = Hash::make("abcde12345");
        $user->password_changed_at = Carbon::now();
        $user->save();

        $this->actingAs($user);

        $userId = $user->id;
        $search = '';
        $paginate = 1;
        $page = 1;
        $perPage = -10;

        $api = $this->getJson(route('api.get.db.company.company.read', [
            'userId' => $userId,
            'search' => $search,
            'paginate' => $paginate,
            'page' => $page,
            'perPage' => $perPage,
            
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

    public function test_api_call_read_when_user_doesnt_have_companies_without_pagination()
    {
        $user = new User();
        $user->name = 'testing';
        $user->email = $this->faker->email;
        $user->password = Hash::make("abcde12345");
        $user->password_changed_at = Carbon::now();
        $user->save();

        $this->actingAs($user);

        $userId = $user->id;
        $search = '';
        $page = 1;
        $perPage = 10;

        $api = $this->getJson(route('api.get.db.company.company.read', [
            'userId' => $userId,
            'search' => $search,
            'page' => $page,
            'perPage' => $perPage,
            
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
}
