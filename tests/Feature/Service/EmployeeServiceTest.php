<?php

namespace Tests\Feature\Service;

use App\Models\User;
use App\Models\Company;
use App\Models\Employee;
use Tests\ServiceTestCase;
use App\Enums\ActiveStatus;
use App\Services\RoleService;
use App\Actions\RandomGenerator;
use App\Services\EmployeeService;
use Illuminate\Support\Facades\DB;
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
        $code = (new RandomGenerator())->generateAlphaNumeric(5);  
        $name = $this->faker->name;
        $email = $this->faker->email;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $postalCode = $this->faker->postcode;
        $country = $this->faker->country;
        $taxId = (new RandomGenerator())->generateNumber(10000000, 999999999);
        $icNum = (new RandomGenerator())->generateNumber(10000000, 999999999);
        $imgPath = '';
        $joinDate = $this->faker->date($format = 'Y-m-d', $max = 'now');
        $remarks = $this->faker->sentence;
        $status = (new RandomGenerator())->generateNumber(0, 1);       

        $this->service->create(
            company_id: $companyId,
            code: $code,
            name: $name,
            email: $email,
            address: $address,
            city: $city,
            postal_code: $postalCode,
            country: $country,
            tax_id: $taxId,
            ic_num: $icNum,
            img_path: $imgPath,
            join_date: $joinDate,
            remarks: $remarks,
            status: $status
        );

        $this->assertDatabaseHas('employees', [
            'company_id' => $companyId,
            'code' => $code,
        ]);

        $this->assertDatabaseHas('users', [
            'name' => $name,
            'email' => $email
        ]);

        $this->assertDatabaseHas('profiles', [
            'address' => $address,
            'city' => $city,
            'postal_code' => $postalCode,
            'country' => $country,
            'tax_id' => $taxId,
            'ic_num' => $icNum,
            'status' => $status,
            'remarks' => $remarks
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
        $employeeId = Employee::inRandomOrder()->first()->id;
        $newCode = (new RandomGenerator())->generateAlphaNumeric(5);
        $newName = $this->faker->name;
        $newAddress = $this->faker->address;
        $newCity = $this->faker->city;
        $newPostalCode = $this->faker->postcode;
        $newCountry = $this->faker->country;
        $newTaxId = (new RandomGenerator())->generateNumber(10000000, 999999999);
        $newIcNum = (new RandomGenerator())->generateNumber(10000000, 999999999);
        $newImgPath = '';
        $newRemarks = $this->faker->sentence;
        $newStatus = Employee::where('id', '=', $employeeId)->first()->status == 0 ? 1 : 0;

        $this->service->update(
            id: $employeeId,
            code: $newCode,
            name: $newName,
            address: $newAddress,
            city: $newCity,
            postal_code: $newPostalCode,
            country: $newCountry,
            tax_id: $newTaxId,
            ic_num: $newIcNum,
            img_path: $newImgPath,
            join_date: null,
            remarks: $newRemarks,
            status: $newStatus
        );

        $this->assertDatabaseHas('employees', [
            'id' => $employeeId,
            'code' => $newCode,
            'status' => $newStatus
        ]);
    }

    public function test_call_delete()
    {
        $employeeId = Employee::inRandomOrder()->first()->id;
        $this->service->delete($employeeId);

        $this->assertSoftDeleted('employees', [
            'id' => $employeeId
        ]);
    }
}
