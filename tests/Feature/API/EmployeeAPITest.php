<?php

namespace Tests\Feature\API;

use Tests\APITestCase;
use App\Models\Company;
use App\Models\Employee;
use App\Services\RoleService;
use App\Actions\RandomGenerator;
use App\Services\EmployeeService;
use Illuminate\Container\Container;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmployeeAPITest extends APITestCase
{
    use WithFaker;

    public function test_api_call_require_authentication()
    {
        $api = $this->getJson('/api/get/dashboard/company/employee/read');
        $this->assertContains($api->getStatusCode(), array(401, 405));

        $api = $this->getJson('/api/post/dashboard/company/employee/save');
        $this->assertContains($api->getStatusCode(), array(401, 405));

        $api = $this->getJson('/api/post/dashboard/company/employee/edit/1');
        $this->assertContains($api->getStatusCode(), array(401, 405));

        $api = $this->getJson('/api/post/dashboard/company/employee/delete/1');
        $this->assertContains($api->getStatusCode(), array(401, 405));
    }

    public function test_api_call_read_with_empty_search()
    {
        $this->actingAs($this->user);

        $companyId = Company::inRandomOrder()->get()[0]->id;
        $search = "";
        $paginate = 1;
        $perPage = 10;

        $api = $this->getJson(route('api.get.db.company.employee.read', [
            'companyId' => Hashids::encode($companyId),
            'search' => $search,
            'paginate' => $paginate,
            'perPage' => $perPage
        ]));

        $api->assertSuccessful();
    }

    public function test_api_call_read_with_special_char_in_search()
    {
        $this->actingAs($this->user);

        $companyId = Company::inRandomOrder()->get()[0]->id;
        $search = " !#$%&'()*+,-./:;<=>?@[\]^_`{|}~";
        $paginate = 1;
        $perPage = -10;

        $api = $this->getJson(route('api.get.db.company.employee.read', [
            'companyId' => Hashids::encode($companyId),
            'search' => $search,
            'paginate' => $paginate,
            'perPage' => $perPage
        ]));

        $api->assertSuccessful();
    }

    public function test_api_call_read_with_negative_value_in_perpage_param()
    {
        $this->actingAs($this->user);

        $companyId = Company::inRandomOrder()->get()[0]->id;
        $search = "";
        $paginate = 1;
        $perPage = -10;

        $api = $this->getJson(route('api.get.db.company.employee.read', [
            'companyId' => Hashids::encode($companyId),
            'search' => $search,
            'paginate' => $paginate,
            'perPage' => $perPage
        ]));

        $api->assertSuccessful();
    }

    public function test_api_call_read_without_pagination()
    {
        $this->actingAs($this->user);

        $companyId = Company::inRandomOrder()->get()[0]->id;
        $search = "";
        $perPage = 10;

        $api = $this->getJson(route('api.get.db.company.employee.read', [
            'companyId' => Hashids::encode($companyId),
            'search' => $search,
            'perPage' => $perPage
        ]));

        $api->assertSuccessful();
    }

    public function test_api_call_read_with_null_param()
    {
        $this->actingAs($this->user);

        $api = $this->getJson(route('api.get.db.company.employee.read', [
            'companyId' => null,
            'search' => null,
            'paginate' => null,
            'perPage' => null
        ]));

        $api->assertStatus(500);
    }

    public function test_api_call_save_with_all_field_filled()
    {
        $this->actingAs($this->user);

        $companyId = Company::inRandomOrder()->get()[0]->id;
        $name = $this->faker->name;
        $email = $this->faker->email();
        $address = $this->faker->address();
        $city = $this->faker->city();
        $postal_code = (new RandomGenerator())->generateNumber(10000, 99999);
        $country = $this->faker->country();
        $tax_id = $this->faker->creditCardNumber;
        $ic_num = (new RandomGenerator())->generateNumber(100000000000, 999999999999);
        $join_date = date("Y-m-d", mt_rand(1609459201,1640995201));
        $remarks = $this->faker->sentence;
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $api = $this->json('POST', route('api.post.db.company.employee.save'), [
            'company_id' => Hashids::encode($companyId),
            'name' => $name,
            'email' => $email,
            'address' => $address,
            'city' => $city,
            'postal_code' => $postal_code,
            'country' => $country,
            'tax_id' => $tax_id,
            'ic_num' => $ic_num,
            'join_date' => $join_date,
            'remarks' => $remarks,
            'status' => $status
        ]);

        $api->assertSuccessful();
    }

    public function test_api_call_save_with_minimal_field_filled()
    {
        $this->actingAs($this->user);

        $companyId = Company::inRandomOrder()->get()[0]->id;
        $name = $this->faker->name;
        $email = $this->faker->email();
        $address = '';
        $city = '';
        $postal_code = '';
        $country = $this->faker->country();
        $tax_id = $this->faker->creditCardNumber;
        $ic_num = (new RandomGenerator())->generateNumber(100000000000, 999999999999);
        $join_date = date("Y-m-d", mt_rand(1609459201,1640995201));
        $remarks = '';
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $api = $this->json('POST', route('api.post.db.company.employee.save'), [
            'company_id' => Hashids::encode($companyId),
            'name' => $name,
            'email' => $email,
            'address' => $address,
            'city' => $city,
            'postal_code' => $postal_code,
            'country' => $country,
            'tax_id' => $tax_id,
            'ic_num' => $ic_num,
            'join_date' => $join_date,
            'remarks' => $remarks,
            'status' => $status
        ]);

        $api->assertSuccessful();
    }

    public function test_api_call_save_with_null_param()
    {
        $this->actingAs($this->user);

        $companyId = null;
        $name = null;
        $email = null;
        $address = null;
        $city = null;
        $postal_code = null;
        $country = null;
        $tax_id = null;
        $ic_num = null;
        $join_date = null;
        $remarks = null;
        $status = null;

        $api = $this->json('POST', route('api.post.db.company.employee.save'), [
            'company_id' => Hashids::encode($companyId),
            'name' => $name,
            'email' => $email,
            'address' => $address,
            'city' => $city,
            'postal_code' => $postal_code,
            'country' => $country,
            'tax_id' => $tax_id,
            'ic_num' => $ic_num,
            'join_date' => $join_date,
            'remarks' => $remarks,
            'status' => $status
        ]);

        $responseCode = $api->status();
        if ($responseCode == 422 or $responseCode == 500) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

    public function test_api_call_edit_with_all_field_filled()
    {
        $this->actingAs($this->user);

        $companyId = Company::inRandomOrder()->get()[0]->id;
        $name = $this->faker->name;
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $first_name = '';
        $last_name = '';
        if ($name == trim($name) && strpos($name, ' ') !== false) {
            $pieces = explode(" ", $name);
            $first_name = $pieces[0];
            $last_name = $pieces[1];
        } else {
            $first_name = $name;
        }

        $rolesId = [];
        $container = Container::getInstance();
        $roleService = $container->make(RoleService::class);
        array_push($rolesId, $roleService->readBy('NAME', 'user')->id);

        $profile = array (
            'first_name' => $first_name,
            'last_name' => $last_name,
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'postal_code' => (new RandomGenerator())->generateNumber(10000, 99999),
            'country' => $this->faker->country(),
            'tax_id' => $this->faker->creditCardNumber,
            'ic_num' => (new RandomGenerator())->generateNumber(100000000000, 999999999999),
            'remarks' => $this->faker->sentence,
            'status' => $status,
        );
        
        $user = [];
        array_push($user, array (
            'name' => $name,
            'email' => $this->faker->email(),
            'password' => '',
            'rolesId' => $rolesId,
            'profile' => $profile
        ));

        $joinDate = date("Y-m-d", mt_rand(1609459201,1640995201));

        $container = Container::getInstance();
        $employeeService = $container->make(EmployeeService::class);
        
        $employeeId = $employeeService->create(
            $companyId,
            $user,
            $joinDate,
            $status
        );
        $employeeId = $employeeId->id;

        $api_edit = $this->json('POST', route('api.post.db.company.employee.edit', [ 'id' => $employeeId ]), [
            'company_id' => Hashids::encode(Company::inRandomOrder()->get()[0]->id),
            'name' => $this->faker->name,
            'email' => $this->faker->email(),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'postal_code' => (new RandomGenerator())->generateNumber(10000, 99999),
            'country' => $this->faker->country(),
            'tax_id' => $this->faker->creditCardNumber,
            'ic_num' => (new RandomGenerator())->generateNumber(100000000000, 999999999999),
            'join_date' => date("Y-m-d", mt_rand(1609459201,1640995201)),
            'remarks' => $this->faker->sentence,
            'status' => (new RandomGenerator())->generateNumber(0, 1)
        ]);

        $api_edit->assertSuccessful();
    }

    public function test_api_call_edit_with_minimal_field_filled()
    {
        $this->actingAs($this->user);

        $companyId = Company::inRandomOrder()->get()[0]->id;
        $name = $this->faker->name;
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $first_name = '';
        $last_name = '';
        if ($name == trim($name) && strpos($name, ' ') !== false) {
            $pieces = explode(" ", $name);
            $first_name = $pieces[0];
            $last_name = $pieces[1];
        } else {
            $first_name = $name;
        }

        $rolesId = [];
        $container = Container::getInstance();
        $roleService = $container->make(RoleService::class);
        array_push($rolesId, $roleService->readBy('NAME', 'user')->id);

        $profile = array (
            'first_name' => $first_name,
            'last_name' => $last_name,
            'address' => null,
            'city' => null,
            'postal_code' => null,
            'country' => $this->faker->country(),
            'tax_id' => $this->faker->creditCardNumber,
            'ic_num' => (new RandomGenerator())->generateNumber(100000000000, 999999999999),
            'remarks' => null,
            'status' => $status,
        );
        
        $user = [];
        array_push($user, array (
            'name' => $name,
            'email' => $this->faker->email(),
            'password' => '',
            'rolesId' => $rolesId,
            'profile' => $profile
        ));

        $joinDate = date("Y-m-d", mt_rand(1609459201,1640995201));

        $container = Container::getInstance();
        $employeeService = $container->make(EmployeeService::class);

        $employeeId = $employeeService->create(
            $companyId,
            $user,
            $joinDate,
            $status
        );
        $employeeId = $employeeId->id;

        $api_edit = $this->json('POST', route('api.post.db.company.employee.edit', [ 'id' => $employeeId ]), [
            'company_id' => Hashids::encode(Company::inRandomOrder()->get()[0]->id),
            'name' => $this->faker->name,
            'email' => $this->faker->email(),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'postal_code' => (new RandomGenerator())->generateNumber(10000, 99999),
            'country' => $this->faker->country(),
            'tax_id' => $this->faker->creditCardNumber,
            'ic_num' => (new RandomGenerator())->generateNumber(100000000000, 999999999999),
            'join_date' => date("Y-m-d", mt_rand(1609459201,1640995201)),
            'remarks' => $this->faker->sentence,
            'status' => (new RandomGenerator())->generateNumber(0, 1)
        ]);

        $api_edit->assertSuccessful();
    }

    public function test_api_call_edit_with_null_param()
    {
        $this->actingAs($this->user);

        $companyId = Company::inRandomOrder()->get()[0]->id;
        $name = $this->faker->name;
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $first_name = '';
        $last_name = '';
        if ($name == trim($name) && strpos($name, ' ') !== false) {
            $pieces = explode(" ", $name);
            $first_name = $pieces[0];
            $last_name = $pieces[1];
        } else {
            $first_name = $name;
        }

        $rolesId = [];
        $container = Container::getInstance();
        $roleService = $container->make(RoleService::class);
        array_push($rolesId, $roleService->readBy('NAME', 'user')->id);

        $profile = array (
            'first_name' => $first_name,
            'last_name' => $last_name,
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'postal_code' => (new RandomGenerator())->generateNumber(10000, 99999),
            'country' => $this->faker->country(),
            'tax_id' => $this->faker->creditCardNumber,
            'ic_num' => (new RandomGenerator())->generateNumber(100000000000, 999999999999),
            'remarks' => $this->faker->sentence,
            'status' => $status,
        );
        
        $user = [];
        array_push($user, array (
            'name' => $name,
            'email' => $this->faker->email(),
            'password' => '',
            'rolesId' => $rolesId,
            'profile' => $profile
        ));

        $joinDate = date("Y-m-d", mt_rand(1609459201,1640995201));

        $container = Container::getInstance();
        $employeeService = $container->make(EmployeeService::class);

        $employeeId = $employeeService->create(
            $companyId,
            $user,
            $joinDate,
            $status
        );
        $employeeId = $employeeId->id;

        $api_edit = $this->json('POST', route('api.post.db.company.employee.edit', [ 'id' => $employeeId ]), [
            'company_id' => null,
            'name' => null,
            'email' => null,
            'address' => null,
            'city' => null,
            'postal_code' => null,
            'country' => null,
            'tax_id' => null,
            'ic_num' => null,
            'join_date' => null,
            'remarks' => null,
            'status' => null,
        ]);

        $responseCode = $api_edit->status();
        if ($responseCode == 422 or $responseCode == 500) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

    public function test_api_call_delete()
    {
        $this->actingAs($this->user);

        $companyId = Company::inRandomOrder()->get()[0]->id;
        $name = $this->faker->name;
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $first_name = '';
        $last_name = '';
        if ($name == trim($name) && strpos($name, ' ') !== false) {
            $pieces = explode(" ", $name);
            $first_name = $pieces[0];
            $last_name = $pieces[1];
        } else {
            $first_name = $name;
        }

        $rolesId = [];
        $container = Container::getInstance();
        $roleService = $container->make(RoleService::class);
        array_push($rolesId, $roleService->readBy('NAME', 'user')->id);

        $profile = array (
            'first_name' => $first_name,
            'last_name' => $last_name,
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'postal_code' => (new RandomGenerator())->generateNumber(10000, 99999),
            'country' => $this->faker->country(),
            'tax_id' => $this->faker->creditCardNumber,
            'ic_num' => (new RandomGenerator())->generateNumber(100000000000, 999999999999),
            'remarks' => $this->faker->sentence,
            'status' => $status,
        );
        
        $user = [];
        array_push($user, array (
            'name' => $name,
            'email' => $this->faker->email(),
            'password' => '',
            'rolesId' => $rolesId,
            'profile' => $profile
        ));

        $joinDate = date("Y-m-d", mt_rand(1609459201,1640995201));

        $container = Container::getInstance();
        $employeeService = $container->make(EmployeeService::class);

        $employeeId = $employeeService->create(
            $companyId,
            $user,
            $joinDate,
            $status
        );
        $employeeId = $employeeId->id;

        $this->json('POST', route('api.post.db.company.employee.delete', $employeeId));
 
        $this->assertSoftDeleted('employees', [
            'id' => $employeeId
        ]);
    }
}
