<?php

namespace Tests\Feature\API;

use Tests\APITestCase;
use App\Models\Company;
use App\Models\Employee;
use App\Enums\ActiveStatus;
use App\Actions\RandomGenerator;
use App\Services\EmployeeService;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Foundation\Testing\WithFaker;

class EmployeeAPITest extends APITestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        Parent::setUp();
    }

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

    public function test_api_call_save_with_all_field_filled()
    {
        $this->actingAs($this->developer);

        $companyId = $this->developer->companies->random(1)->first()->id;
        $name = $this->faker->name;
        $email = $this->faker->unique()->email;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $postalCode = $this->faker->postcode();
        $country = $this->faker->randomElement(['Singapore', 'Indonesia']);
        $taxId = $this->faker->randomDigit();
        $icNum = (new RandomGenerator())->generateNumber(100000000000, 900000000000);
        $joinDate = $this->faker->date($format = 'Y-m-d', $max = 'now');
        $remarks = $this->faker->sentence();
        $status = $this->faker->randomElement(ActiveStatus::toArrayName());

        $api = $this->json('POST', route('api.post.db.company.employee.save'), [
            'company_id' => Hashids::encode($companyId),
            'name' => $name,
            'email' => $email,
            'address' => $address,
            'city' => $city,
            'postal_code' => $postalCode,
            'country' => $country,
            'tax_id' => $taxId,
            'ic_num' => $icNum,
            'join_date' => $joinDate,
            'remarks' => $remarks,
            'status' => $status
        ]);

        $api->assertSuccessful();
        // $this->assertDatabaseHas('employees', [
        //     'company_id' => $companyId,
        //     'name' => $name,
        //     'address' => $address,
        //     'city' => $city,
        //     'postal_code' => $postalCode,
        //     'country' => $country,
        //     'tax_id' => $taxId,
        //     'ic_num' => $icNum,
        //     'join_date' => $joinDate,
        //     'remarks' => $remarks,
        //     'status' => ActiveStatus::fromName($status)
        // ]);
    }
}