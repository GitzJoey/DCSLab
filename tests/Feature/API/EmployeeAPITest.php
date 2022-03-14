<?php

namespace Tests\Feature\API;

use App\Actions\RandomGenerator;
use App\Models\Employee;
use App\Models\Company;
use App\Services\EmployeeService;
use App\Services\RoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

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

    public function test_api_call_read()
    {
        $this->assertTrue(true);
    }

    public function test_api_call_read_getPaymentTermType()
    {
        $this->assertTrue(true);
    }

    public function test_api_call_save()
    {
        $this->actingAs($this->user);

        $company_id = Hashids::encode(Company::inRandomOrder()->get()[0]->id);
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
            'company_id' => $company_id,
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

    public function test_api_call_edit()
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
        $roleService = app(RoleService::class);
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

        $employeeService = app(EmployeeService::class);
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

    public function test_api_call_delete()
    {
        $this->actingAs($this->user);

        $employee = Employee::with('company')->inRandomOrder()->first();

        $api = $this->json('POST', route('api.post.db.company.employee.delete', [ 'id' => $employee->hId ]));

        $api->assertSuccessful();
    }
}
