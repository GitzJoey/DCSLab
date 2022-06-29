<?php

namespace Tests\Feature\API;

use Carbon\Carbon;
use App\Models\User;
use Tests\APITestCase;
use App\Models\Company;
use App\Models\Profile;
use App\Enums\UserRoles;
use App\Models\Employee;
use App\Enums\ActiveStatus;
use App\Services\RoleService;
use App\Services\UserService;
use App\Actions\RandomGenerator;
use App\Services\EmployeeService;
use Illuminate\Container\Container;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Foundation\Testing\WithFaker;

class EmployeeAPITest extends APITestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        Parent::setUp();
    }

    public function test_employee_api_call_require_authentication()
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

    public function test_employee_api_call_save_with_all_field_filled()
    {
        $this->actingAs($this->developer);

        $companyId = $this->developer->companies->random(1)->first()->id;
        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $email = $this->faker->unique()->email;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $postalCode = $this->faker->postcode();
        $country = $this->faker->randomElement(['Singapore', 'Indonesia']);
        $taxId = (new RandomGenerator())->generateNumber(100000000000, 900000000000);
        $icNum = (new RandomGenerator())->generateNumber(100000000000, 900000000000);
        $imgPath = '';
        $joinDate = $this->faker->date($format = 'Y-m-d', $max = 'now');
        $remarks = $this->faker->sentence();
        $status = $this->faker->randomElement(ActiveStatus::toArrayName());

        $branchCount = Company::find($companyId)->branches->count();
        $accessCount = $this->faker->numberBetween(1, $branchCount);
        $branchIds = Company::find($companyId)->branches()->inRandomOrder()->take($accessCount)->pluck('id');
        
        if ($accessCount == 0 || count($branchIds) == 0) {
            $this->markTestSkipped('Not Enough Branch To Run Test');
        }

        $accesses = [];

        for ($i = 0; $i < $accessCount ; $i++) {
            array_push($accesses, Hashids::encode($branchIds[$i]));
        }

        $api = $this->json('POST', route('api.post.db.company.employee.save'), [
            'company_id' => Hashids::encode($companyId),
            'code' => $code, 
            'name' => $name,
            'email' => $email,
            'address' => $address,
            'city' => $city,
            'postal_code' => $postalCode,
            'country' => $country,
            'tax_id' => $taxId,
            'ic_num' => $icNum,
            'img_path' => $imgPath,
            'join_date' => $joinDate,
            'remarks' => $remarks,
            'status' => $status,
            'accessBranchIds' => $accesses
        ]);

        $api->assertSuccessful();

        $employeeId = Employee::where('code', '=', $code)->first()->id;

        $this->assertDatabaseHas('employees', [
            'company_id' => $companyId,
            'join_date' => $joinDate,
            'code' => $code, 
            'status' => ActiveStatus::fromName($status)
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
            'status' => ActiveStatus::fromName($status),
            'remarks' => $remarks
        ]);

        for ($i = 0; $i < $accessCount ; $i++) {           
            $this->assertDatabaseHas('employee_accesses', [
                'employee_id' => $employeeId,
                'branch_id' => $branchIds[$i]
            ]);
        }
    }

    public function test_employee_api_call_save_with_empty_string_param()
    {
        {
            $this->actingAs($this->developer);
    
            $companyId = $this->developer->companies->random(1)->first()->id;
            $code = (new RandomGenerator())->generateAlphaNumeric(5);
            $name = '';
            $email = '';
            $address = '';
            $city = '';
            $postalCode = $this->faker->postcode();
            $country = $this->faker->randomElement(['Singapore', 'Indonesia']);
            $taxId = (new RandomGenerator())->generateNumber(100000000000, 900000000000);
            $icNum = (new RandomGenerator())->generateNumber(100000000000, 900000000000);
            $imgPath = '';
            $joinDate = $this->faker->date($format = 'Y-m-d', $max = 'now');
            $remarks = '';
            $status = $this->faker->randomElement(ActiveStatus::toArrayName());
    
            $api = $this->json('POST', route('api.post.db.company.employee.save'), [
                'company_id' => Hashids::encode($companyId),
                'code' => $code, 
                'name' => $name,
                'email' => $email,
                'address' => $address,
                'city' => $city,
                'postal_code' => $postalCode,
                'country' => $country,
                'tax_id' => $taxId,
                'ic_num' => $icNum,
                'img_path' => $imgPath,
                'join_date' => $joinDate,
                'remarks' => $remarks,
                'status' => $status
            ]);
    
            $api->assertStatus(422);
            $api->assertJsonStructure([
                'message'
            ]);
        }
    }

    public function test_employee_api_call_read_with_empty_search()
    {
        $this->actingAs($this->developer);

        $company = $this->developer->companies->random(1)->first();
                
        $companyId = $company->id;
        $search = '';
        $paginate = 1;
        $page = 1;
        $perPage = 10;

        $api = $this->getJson(route('api.get.db.company.employee.read', [
            'companyId' => Hashids::encode($companyId),
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

    public function test_employee_api_call_read_with_special_char_in_search()
    {
        $this->actingAs($this->developer);

        $company = $this->developer->companies->random(1)->first();

        $companyId = $company->id;
        $search = " !#$%&'()*+,-./:;<=>?@[\]^_`{|}~";
        $paginate = 1;
        $page = 1;
        $perPage = 10;
        $refresh = '';

        $api = $this->getJson(route('api.get.db.company.employee.read', [
            'companyId' => Hashids::encode($companyId),
            'search' => $search,
            'paginate' => $paginate,
            'page' => $page,
            'perPage' => $perPage,
            'refresh' => $refresh
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

    public function test_employee_api_call_read_with_negative_value_in_perpage_param()
    {
        $this->actingAs($this->developer);

        $company = $this->developer->companies->random(1)->first();

        $companyId = $company->id;
        $search = "";
        $paginate = 1;
        $page = 1;
        $perPage = -10;
        $refresh = '';

        $api = $this->getJson(route('api.get.db.company.employee.read', [
            'companyId' => Hashids::encode($companyId),
            'search' => $search,
            'paginate' => $paginate,
            'page' => $page,
            'perPage' => $perPage,
            'refresh' => $refresh
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
    public function test_employee_api_call_read_without_pagination()
    {
        $this->actingAs($this->developer);

        $company = $this->developer->companies->random(1)->first();

        $companyId = $company->id;
        $search = "";
        $page = 1;
        $perPage = 10;
        $refresh = '';

        $api = $this->getJson(route('api.get.db.company.employee.read', [
            'companyId' => Hashids::encode($companyId),
            'search' => $search,
            'page' => $page,
            'perPage' => $perPage,
            'refresh' => $refresh
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

    public function test_employee_api_call_edit_with_all_field_filled()
    {
        $this->actingAs($this->developer);

        $container = Container::getInstance();
        $setting = $container->make(UserService::class)->createDefaultSetting();
        $roles = $container->make(RoleService::class)->readBy('NAME', UserRoles::USER->value);

        $user = User::factory()->make();
        $user->created_at = Carbon::now();
        $user->updated_at = Carbon::now();
        $user->save();

        $profile = Profile::factory()->setFirstName($user->name);
        $profile->created_at = Carbon::now();
        $profile->updated_at = Carbon::now();
        $user->profile()->save($profile);
        
        $companyId = $this->developer->companies->random(1)->first()->id;
        $user->companies()->attach($companyId);
        $user->attachRoles([$roles->id]);
        $user->settings()->saveMany($setting);

        $employee = Employee::factory()->create([
            'company_id' => $companyId,
            'user_id' => $user->id
        ]);
        $employeeId = $employee->id;

        $newCode = (new RandomGenerator())->generateAlphaNumeric(5) . 'new';
        $newName = $this->faker->name;
        $newAddress = $this->faker->address;
        $newCity = $this->faker->city;
        $newPostalCode = $this->faker->postcode();
        $newCountry = $this->faker->randomElement(['Singapore', 'Indonesia']);
        $newTaxId = (new RandomGenerator())->generateNumber(100000000000, 900000000000);
        $newIcNum = (new RandomGenerator())->generateNumber(100000000000, 900000000000);
        $newImgPath = '';
        $newRemarks = $this->faker->sentence();
        $newStatus = $this->faker->randomElement(ActiveStatus::toArrayName());

        $api_edit = $this->json('POST', route('api.post.db.company.employee.edit', [ 'id' => Hashids::encode($employeeId) ]), [
            'company_id' => Hashids::encode($companyId),
            'code' => $newCode,
            'name' => $newName,
            'address' => $newAddress,
            'city' => $newCity,
            'postal_code' => $newPostalCode,
            'country' => $newCountry,
            'tax_id' => $newTaxId,
            'ic_num' => $newIcNum,
            'img_path' => $newImgPath,
            'join_date' => '',
            'remarks' => $newRemarks,
            'status' => $newStatus
        ]);

        $api_edit->assertSuccessful();

        $this->assertDatabaseHas('employees', [
            'company_id' => $companyId,
            'code' => $newCode, 
            'status' => ActiveStatus::fromName($newStatus)
        ]);
        
        $this->assertDatabaseHas('users', [
            'name' => $newName,
        ]);

        $this->assertDatabaseHas('profiles', [
            'address' => $newAddress,
            'city' => $newCity,
            'postal_code' => $newPostalCode,
            'country' => $newCountry,
            'tax_id' => $newTaxId,
            'ic_num' => $newIcNum,
            'status' => ActiveStatus::fromName($newStatus),
            'remarks' => $newRemarks
        ]);
    }

    public function test_employee_api_call_delete()
    {
        $this->actingAs($this->developer);

        $employee = Employee::inRandomOrder()->first();
        if (!$employee)
            $this->markTestSkipped('Employee not found');

        $employeeId = $employee->id;

        $api = $this->json('POST', route('api.post.db.company.employee.delete', Hashids::encode($employeeId)));

        $api->assertSuccessful();
        $this->assertSoftDeleted('employees', [
            'id' => $employeeId
        ]);
    }

    public function test_employee_api_call_delete_nonexistance_id()
    {
        $this->actingAs($this->developer);

        $api = $this->json('POST', route('api.post.db.company.employee.delete', (new RandomGenerator())->generateAlphaNumeric(5)));

        $api->assertStatus(500);
    }
}