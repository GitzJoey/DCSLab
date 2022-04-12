<?php

namespace Tests\Feature\API;

use Carbon\Carbon;
use App\Models\User;
use Tests\APITestCase;
use App\Models\Company;
use App\Actions\RandomGenerator;
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
        $this->actingAs($this->user);

        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $default = 0;
        $status = (new RandomGenerator())->generateNumber(0, 1);
        $userId = $this->user->id;

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
        $this->actingAs($this->user);

        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $address = '';
        $default = 0;
        $status = (new RandomGenerator())->generateNumber(0, 1);
        $userId = $this->user->id;

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
        $this->actingAs($this->user);

        $code = $this->user->companies->random(1)->first()->code;
        $name = $this->faker->name;
        $address = $this->faker->address;
        $default = 1;
        $status = 1;
        $userId = $this->user->id;

        $api = $this->json('POST', route('api.post.db.company.company.save'), [
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'default' => $default,
            'status' => $status,
            'userId' => $userId
        ]);

        $api->assertStatus(500);       
        $api->assertJsonStructure([
            'errors'
        ]);
    }

    public function test_api_call_save_with_null_param()
    {
        $this->actingAs($this->user);

        $code = null;
        $name = null;
        $address = null;
        $default = null;
        $status = null;
        $userId = null;

        $api = $this->json('POST', route('api.post.db.company.company.save'), [
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'default' => $default,
            'status' => $status,
            'userId' => $userId
        ]);

        $api->assertStatus(500);
        $api->assertJsonStructure([
            'message'
        ]);
    }

    public function test_api_call_save_with_empty_string_param()
    {
        $this->actingAs($this->user);

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

        $api->assertStatus(500);
        $api->assertJsonStructure([
            'message'
        ]);
    }

    public function test_api_call_edit_with_all_field_filled()
    {
        $this->actingAs($this->user);

        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $default = (new RandomGenerator())->generateNumber(0, 1);
        $status = (new RandomGenerator())->generateNumber(0, 1);
        $userId = $this->user->id;

        $company = Company::create([
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'default' => $default,
            'status' => $status,
            'userId' => $userId
        ]);
        $companyId = $company->id;

        $newCode = (new RandomGenerator())->generateNumber(1, 9999) . 'new';
        $newName = $this->faker->name;
        $newAddress = $this->faker->address;
        $newDefault = (new RandomGenerator())->generateNumber(0, 1);
        $newStatus = (new RandomGenerator())->generateNumber(0, 1);

        $api_edit = $this->json('POST', route('api.post.db.company.company.edit', [ 'id' => Hashids::encode($companyId) ]), [
            'code' => $newCode,
            'name' => $newName,
            'address' => $newAddress,
            'default' => $newDefault,
            'status' => $newStatus
        ]);

        $api_edit->assertSuccessful();
        $this->assertDatabaseHas('companies', [
            'id' => $companyId,
            'code' => $newCode
        ]);
    }

    public function test_api_call_edit_with_minimal_field_filled()
    {
        $this->actingAs($this->user);

        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $address = '';
        $default = (new RandomGenerator())->generateNumber(0, 1);
        $status = (new RandomGenerator())->generateNumber(0, 1);
        $userId = $this->user->id;

        $company = Company::create([
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'default' => $default,
            'status' => $status,
            'userId' => $userId
        ]);
        $companyId = $company->id;

        $newCode = (new RandomGenerator())->generateNumber(1, 9999) . 'new';
        $newName = $this->faker->name;
        $newAddress = '';
        $newDefault = (new RandomGenerator())->generateNumber(0, 1);
        $newStatus = (new RandomGenerator())->generateNumber(0, 1);

        $api_edit = $this->json('POST', route('api.post.db.company.company.edit', [ 'id' => Hashids::encode($companyId) ]), [
            'code' => $newCode,
            'name' => $newName,
            'address' => $newAddress,
            'default' => $newDefault,
            'status' => $newStatus
        ]);

        $api_edit->assertSuccessful();
        $this->assertDatabaseHas('companies', [
            'id' => $companyId,
            'code' => $newCode
        ]);
    }

    public function test_api_call_edit_with_existing_code()
    {
        $this->actingAs($this->user);

        $code = $this->user->companies->random(1)->first()->code;
        $name = $this->faker->name;
        $address = $this->faker->address;
        $default = 1;
        $status = 1;
        $userId = $this->user->id;

        $company = Company::create([
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'default' => $default,
            'status' => $status,
            'userId' => $userId
        ]);
        $companyId = $company->id;

        $newCode = (new RandomGenerator())->generateNumber(1, 9999) . 'new';
        $newName = $this->faker->name;
        $newAddress = $this->faker->address;
        $newDefault = (new RandomGenerator())->generateNumber(0, 1);
        $newStatus = (new RandomGenerator())->generateNumber(0, 1);

        $api_edit = $this->json('POST', route('api.post.db.company.company.edit', [ 'id' => Hashids::encode($companyId) ]), [
            'code' => $newCode,
            'name' => $newName,
            'address' => $newAddress,
            'default' => $newDefault,
            'status' => $newStatus
        ]);

        $api_edit->assertStatus(500);
        $api_edit->assertJsonStructure([
            'message'
        ]);
    }

    public function test_api_call_edit_with_null_param()
    {
        $this->actingAs($this->user);

        $newCode = null;
        $newName = null;
        $newAddress = null;
        $newDefault = null;
        $newStatus = null;

        $api_edit = $this->json('POST', route('api.post.db.company.company.edit', null), [
            'code' => $newCode,
            'name' => $newName,
            'address' => $newAddress,
            'default' => $newDefault,
            'status' => $newStatus
        ]);

        $api_edit->assertStatus(500);
        $api_edit->assertJsonStructure([
            'message'
        ]);
    }

    public function test_api_call_delete()
    {
        $this->actingAs($this->user);

        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $default = 0;
        $status = 1;
        $userId = $this->user->id;

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
        $this->actingAs($this->user);

        $api = $this->json('POST', route('api.post.db.company.company.delete', (new RandomGenerator())->generateAlphaNumeric(5)));

        $api->assertStatus(500);
        $api->assertJsonStructure([
            'message'
        ]);
    }

    public function test_api_call_delete_default_company()
    {
        $this->actingAs($this->user);

        $companyId = $this->user->id;

        $api = $this->json('POST', route('api.post.db.company.company.delete', Hashids::encode($companyId)));

        $api->assertStatus(500);
        $api->assertJsonStructure([
            'errors'
        ]);
    }

    public function test_api_call_read_when_user_have_companies_read_with_empty_search()
    {
        $this->actingAs($this->user);

        $userId = $this->user->id;
        $search = "";
        $paginate = (new RandomGenerator())->generateNumber(0, 1);
        $perPage = 10;

        $api = $this->getJson(route('api.get.db.company.company.read', [
            'userId' => $userId,
            'search' => $search,
            'paginate' => $paginate,
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

    public function test_api_call_read_when_user_have_companies_with_special_char_in_search()
    {
        $this->actingAs($this->user);

        $userId = $this->user->id;
        $search = " !#$%&'()*+,-./:;<=>?@[\]^_`{|}~";
        $paginate = (new RandomGenerator())->generateNumber(0, 1);
        $perPage = 10;

        $api = $this->getJson(route('api.get.db.company.company.read', [
            'userId' => $userId,
            'search' => $search,
            'paginate' => $paginate,
            'perPage' => $perPage,
            
        ]));

        $api->assertSuccessful();
    }

    public function test_api_call_read_when_user_have_companies_with_negative_value_in_perpage_param()
    {
        $this->actingAs($this->user);

        $userId = User::has('companies')->get()->first()->id;
        $search = '';
        $paginate = (new RandomGenerator())->generateNumber(0, 1);
        $perPage = -10;

        $api = $this->getJson(route('api.get.db.company.company.read', [
            'userId' => $userId,
            'search' => $search,
            'paginate' => $paginate,
            'perPage' => $perPage,
            
        ]));

        $api->assertStatus(500);
        $api->assertJsonStructure([
            'errors'
        ]);
    }

    public function test_api_call_read_when_user_have_companies_without_pagination()
    {
        $this->actingAs($this->user);

        $userId = User::has('companies')->get()->first()->id;
        $search = '';
        $perPage = 10;

        $api = $this->getJson(route('api.get.db.company.company.read', [
            'userId' => $userId,
            'search' => $search,
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

    public function test_api_call_read_when_user_have_companies_with_null_param()
    {
        $this->actingAs($this->user);

        $api = $this->getJson(route('api.get.db.company.company.read', [
            'userId' => null,
            'search' => null,
            'paginate' => null,
            'perPage' => null,
            
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
        $paginate = (new RandomGenerator())->generateNumber(0, 1);
        $perPage = 10;

        $api = $this->getJson(route('api.get.db.company.company.read', [
            'userId' => $userId,
            'search' => $search,
            'paginate' => $paginate,
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
        if (empty($password)) {
            $user->password = (new RandomGenerator())->generateAlphaNumeric(5);
            $user->password_changed_at = null;
        } else {
            $user->password = Hash::make($password);
            $user->password_changed_at = Carbon::now();
        }
        $user->save();

        $this->actingAs($user);

        $userId = $user->id;
        $search = " !#$%&'()*+,-./:;<=>?@[\]^_`{|}~";
        $paginate = (new RandomGenerator())->generateNumber(0, 1);
        $perPage = 10;

        $api = $this->getJson(route('api.get.db.company.company.read', [
            'userId' => $userId,
            'search' => $search,
            'paginate' => $paginate,
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
        $user = User::doesnthave('companies')->get();

        if ($user->count() == 0) {
            $user = new User();
            $user->name = 'testing';
            $user->email = $this->faker->email;

            if (empty($password)) {
                $user->password = (new RandomGenerator())->generateAlphaNumeric(5);
                $user->password_changed_at = null;
            } else {
                $user->password = Hash::make($password);
                $user->password_changed_at = Carbon::now();
            }

            $user->save();
            $selectedUser = $user;
        } else {
            $selectedUser = $user->shuffle()->first();
        }

        $this->actingAs($this->user);

        $userId = $selectedUser->id;
        $search = '';
        $paginate = (new RandomGenerator())->generateNumber(0, 1);
        $perPage = -10;

        $api = $this->getJson(route('api.get.db.company.company.read', [
            'userId' => $userId,
            'search' => $search,
            'paginate' => $paginate,
            'perPage' => $perPage,
            
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'errors'
        ]);
    }

    public function test_api_call_read_when_user_doesnt_have_companies_without_pagination()
    {
        $user = User::doesnthave('companies')->get();

        if ($user->count() == 0) {
            $user = new User();
            $user->name = 'testing';
            $user->email = $this->faker->email;

            if (empty($password)) {
                $user->password = (new RandomGenerator())->generateAlphaNumeric(5);
                $user->password_changed_at = null;
            } else {
                $user->password = Hash::make($password);
                $user->password_changed_at = Carbon::now();
            }

            $user->save();
            $selectedUser = $user;
        } else {
            $selectedUser = $user->shuffle()->first();
        }

        $this->actingAs($this->user);

        $userId = $selectedUser->id;
        $search = '';
        $perPage = 10;

        $api = $this->getJson(route('api.get.db.company.company.read', [
            'userId' => $userId,
            'search' => $search,
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

    public function test_api_call_read_when_user_doesnt_have_companies_with_null_param()
    {
        $this->actingAs($this->user);

        $api = $this->getJson(route('api.get.db.company.company.read', [
            'userId' => null,
            'search' => null,
            'paginate' => null,
            'perPage' => null,
            
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