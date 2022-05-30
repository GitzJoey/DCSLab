<?php

namespace Tests\Feature\Service;

use App\Models\Company;
use App\Models\Employee;
use Tests\ServiceTestCase;
use App\Services\RoleService;
use App\Actions\RandomGenerator;
use App\Services\EmployeeService;
use Illuminate\Container\Container;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Contracts\Pagination\Paginator;

class EmployeeServiceTest extends ServiceTestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(EmployeeService::class);

        if (Company::count() < 2) {
            $this->artisan('db:seed', ['--class' => 'CompanyTableSeeder']);
        }
    }

    public function test_call_save()
    {
        $companyId = Company::inRandomOrder()->first()->id;
        
            $name = $this->faker->name;
            
            $first_name = '';
            $last_name = '';
            if ($name == trim($name) && strpos($name, ' ') !== false) {
                $pieces = explode(" ", $name);
                $first_name = $pieces[0];
                $last_name = $pieces[1];
            } else {
                $first_name = $name;
            }

            $container = Container::getInstance();
            $roleService = $container->make(RoleService::class);
            $rolesId = [];
            array_push($rolesId, $roleService->readBy('NAME', 'user')->id);

            $address = $this->faker->address;
            $city = $this->faker->city;
            $postalCode = $this->faker->postcode;
            $country = $this->faker->country;
            $taxId = (new RandomGenerator())->generateNumber(10000000, 999999999);
            $icNum = (new RandomGenerator())->generateNumber(10000000, 999999999);
            $remarks = $this->faker->sentence;
            $status = (new RandomGenerator())->generateNumber(0, 1);
            $profile = array (
                'first_name' => $first_name,
                'last_name' => $last_name,
                'address' => $address,
                'city' => $city,
                'postal_code' => $postalCode,
                'country' => $country,
                'tax_id' => $taxId,
                'ic_num' => $icNum,
                'remarks' => $remarks,
                'status' => $status,
            );

        $email = $this->faker->email;
        $user = [];
        array_push($user, array (
            'name' => $name,
            'email' => $email,
            'password' => '',
            'rolesId' => $rolesId,
            'profile' => $profile
        ));

        $joinDate = $this->faker->date($format = 'Y-m-d', $max = 'now');

        $this->service->create(
            company_id: $companyId,
            user: $user,
            join_date: $joinDate,
            status: $status
        );

        $this->assertDatabaseHas('employees', [
            'company_id' => $companyId
        ]);
    }

    public function test_call_read()
    {
        $companyId = Company::has('employees')->inRandomOrder()->first()->id;

        $response = $this->service->read(
            companyId: $companyId, 
            search: '', 
            paginate: true, 
            page: 1,
            perPage: 10,
            useCache: false
        );

        $this->assertInstanceOf(Paginator::class, $response);
        $this->assertNotNull($response);
    }

    public function test_call_edit()
    {
        $employeeId = Employee::find(1)->inRandomOrder()->first()->id;
        $companyId = Employee::where('id', '=', $employeeId)->first()->company_id;
        $userId = Employee::where('id', '=', $employeeId)->first()->user_id;
        
        $newStatus = Employee::where('id', '=', $employeeId)->first()->status;
        $newStatus = $newStatus == 0 ? 1 : $newStatus;

        $this->service->update(
            id: $employeeId,
            company_id: $companyId,
            user_id: $userId,
            status: $newStatus
        );

        $this->assertDatabaseHas('employees', [
            'id' => $employeeId,
            'company_id' => $companyId,
            'user_id' => $userId,
            'status' => $newStatus
        ]);
    }

    public function test_call_delete()
    {
        $employeeId = Employee::find(1)->inRandomOrder()->first()->id;

        $this->service->delete($employeeId);

        $this->assertSoftDeleted('employees', [
            'id' => $employeeId
        ]);
    }


}
