<?php

namespace Tests\Feature\Service;

use TypeError;
use App\Models\User;
use App\Models\Company;
use Tests\ServiceTestCase;
use App\Services\UserService;
use App\Actions\RandomGenerator;
use App\Services\CompanyService;
use Database\Seeders\CompanyTableSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Contracts\Pagination\Paginator;

class CompanyServiceTest extends ServiceTestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(CompanyService::class);
        $this->userService = app(UserService::class);

        if (User::count() == 0)
            $this->artisan('db:seed', ['--class' => 'UserTableSeeder']);

        if (User::has('companies')->count() == 0) {
            $companyPerUser = 3;
            $companySeeder = new CompanyTableSeeder();
            $companySeeder->callWith(CompanyTableSeeder::class, [$companyPerUser]);    
        }
    }

    public function test_company_service_call_save_with_all_field_filled()
    {
        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $default = $this->faker->boolean();
        $status = $this->faker->boolean();
        $userId = User::get()->first()->id;

        $this->service->create(
            code: $code,
            name: $name,
            address: $address,
            default: $default,
            status: $status,
            userId : $userId
        );

        $companyId = Company::where('code', '=', $code)->first()->id;

        $this->assertDatabaseHas('companies', [
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'default' => $default,
            'status' => $status
        ]);

        $this->assertDatabaseHas('company_user', [
            'company_id' => $companyId,
            'user_id' => $userId
        ]);
    }

    public function test_company_service_call_read_when_user_have_companies()
    {
        $userId = User::has('companies')->get()->first()->id;

        $response = $this->service->read(
            userId: $userId, 
            search: '', 
            paginate: true, 
            page: 1,
            perPage: 10,
            useCache: false
        );

        $this->assertInstanceOf(Paginator::class, $response);
        $this->assertNotNull($response);
    }

    public function test_company_service_call_read_when_user_doesnt_have_companies()
    {
        $user = User::doesnthave('companies')->get();

        if ($user->count() == 0) {
            $email = $this->faker->email;
            $selectedUser = $this->userService->register(
                name: 'testing', 
                email: $email, 
                password: 'password', 
                terms: 'on'
            );
        } else {
            $selectedUser = $user->shuffle()->first();
        }

        $response = $this->service->read(
            userId: $selectedUser->id, 
            search: '', 
            paginate: true, 
            page: 1,
            perPage: 10,
            useCache: false
        );

        $this->assertInstanceOf(Paginator::class, $response);
        $this->assertNotNull($response);
    }

    public function test_company_service_call_edit_with_all_field_filled()
    {
        $id = Company::inRandomOrder()->first()->id;

        $newCode = (new RandomGenerator())->generateAlphaNumeric(5);
        $newName = $this->faker->name;
        $newAddress = $this->faker->address;
        $newDefault = $this->faker->boolean();
        $newStatus = $this->faker->boolean();

        $this->service->update(
            id: $id,
            code: $newCode,
            name: $newName,
            address: $newAddress,
            default: $newDefault,
            status: $newStatus
        );

        $this->assertDatabaseHas('companies', [
            'code' => $newCode,
            'name' => $newName,
            'address' => $newAddress,
            'default' => $newDefault,
            'status' => $newStatus
        ]);
    }

    public function test_company_service_call_delete()
    {
        $user = User::has('companies')->InRandomOrder()->first();

        $userId = $user->id;
        $companyId = $user->companies()->where('default', '=', 0)->InRandomOrder()->first()->id;

        $this->service->delete(
            userId: $userId,
            id: $companyId
        );

        $this->assertSoftDeleted('companies', [
            'id' => $companyId
        ]);
    }
}
