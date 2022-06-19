<?php

namespace Tests\Feature\Service;

use App\Models\Branch;
use App\Models\Company;
use Tests\ServiceTestCase;
use App\Services\BranchService;
use App\Actions\RandomGenerator;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Contracts\Pagination\Paginator;

class BranchServiceTest extends ServiceTestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(BranchService::class);

        if (Branch::count() < 2)
            $this->artisan('db:seed', ['--class' => 'BranchTableSeeder']);
    }

    public function test_call_save_with_all_field_filled()
    {
        $company_id = Company::has('branches')->inRandomOrder()->first()->id;
        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $contact = $this->faker->e164PhoneNumber;
        $is_main = $this->faker->boolean();
        $remarks = $this->faker->sentence;
        $status = $this->faker->boolean();

        $this->service->create(
            company_id: $company_id,
            code: $code,
            name: $name,
            address: $address,
            city: $city,
            contact: $contact,
            is_main : $is_main,
            remarks: $remarks,
            status: $status
        );

        $this->assertDatabaseHas('branches', [
            'company_id' => $company_id,
            'code' => $code,
            'name' => $name
        ]);
    }

    public function test_call_save_with_minimal_field_filled()
    {
        $company_id = Company::has('branches')->inRandomOrder()->first()->id;
        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $address = null;
        $city = null;
        $contact = null;
        $is_main = $this->faker->boolean();
        $remarks = null;
        $status = $this->faker->boolean();

        $this->service->create(
            company_id: $company_id,
            code: $code,
            name: $name,
            address: $address,
            city: $city,
            contact: $contact,
            is_main : $is_main,
            remarks: $remarks,
            status: $status
        );

        $this->assertDatabaseHas('branches', [
            'company_id' => $company_id,
            'code' => $code,
            'name' => $name
        ]);
    }

    public function test_call_edit_with_all_field_filled()
    {
        $company_id = Company::has('branches')->inRandomOrder()->first()->id;
        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $contact = $this->faker->e164PhoneNumber;
        $is_main = $this->faker->boolean();
        $remarks = $this->faker->sentence;
        $status = $this->faker->boolean();

        $branch = Branch::create([
            'company_id' => $company_id,
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'city' => $city,
            'contact' => $contact,
            'is_main' => $is_main,
            'remarks' => $remarks,
            'status' => $status
        ]);
        $id = $branch->id;

        $newCode = (new RandomGenerator())->generateAlphaNumeric(5);
        $newName = $this->faker->name;
        $newAddress = $this->faker->address;
        $newCity = $this->faker->city;
        $newContact = $this->faker->e164PhoneNumber;
        $newIsMain = $this->faker->boolean();
        $newRemarks = $this->faker->sentence;
        $newStatus = $this->faker->boolean();

        $this->service->update(
            id: $id,
            company_id: $company_id,
            code: $newCode,
            name: $newName,
            address: $newAddress,
            city: $newCity,
            contact: $newContact,
            is_main: $newIsMain,
            remarks: $newRemarks,
            status: $newStatus
        );

        $this->assertDatabaseHas('branches', [
            'id' => $id,
            'company_id' => $company_id,
            'code' => $newCode,
            'name' => $newName
        ]);
    }

    public function test_call_edit_with_minimal_field_filled()
    {
        $company_id = Company::has('branches')->inRandomOrder()->first()->id;
        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $address = null;
        $city = null;
        $contact = null;
        $is_main = $this->faker->boolean();
        $remarks = null;
        $status = $this->faker->boolean();

        $branch = Branch::create([
            'company_id' => $company_id,
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'city' => $city,
            'contact' => $contact,
            'is_main' => $is_main,
            'remarks' => $remarks,
            'status' => $status
        ]);
        $id = $branch->id;

        $newCode = (new RandomGenerator())->generateAlphaNumeric(5);
        $newName = $this->faker->name;
        $newAddress = null;
        $newCity = null;
        $newContact = null;
        $newIsMain = $this->faker->boolean();
        $newRemarks = null;
        $newStatus = $this->faker->boolean();

        $this->service->update(
            id: $id,
            company_id: $company_id,
            code: $newCode,
            name: $newName,
            address: $newAddress,
            city: $newCity,
            contact: $newContact,
            is_main: $newIsMain,
            remarks: $newRemarks,
            status: $newStatus
        );

        $this->assertDatabaseHas('branches', [
            'id' => $id,
            'company_id' => $company_id,
            'code' => $newCode,
            'name' => $newName
        ]);
    }

    public function test_call_delete()
    {
        $company_id = Company::has('branches')->inRandomOrder()->first()->id;
        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $contact = $this->faker->e164PhoneNumber;
        $is_main = $this->faker->boolean();
        $remarks = $this->faker->sentence;
        $status = $this->faker->boolean();

        $branch = Branch::create([
            'company_id' => $company_id,
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'city' => $city,
            'contact' => $contact,
            'is_main' => $is_main,
            'remarks' => $remarks,
            'status' => $status
        ]);
        $id = $branch->id;

        $this->service->delete($id);

        $this->assertSoftDeleted('branches', [
            'id' => $id
        ]);
    }

    public function test_call_read_when_user_have_branches_read_with_empty_search()
    {
        $companyId = Company::has('branches')->inRandomOrder()->first()->id;

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

    public function test_call_read_when_user_have_branches_with_special_char_in_search()
    {
        $companyId = Company::has('branches')->inRandomOrder()->first()->id;
        $search = " !#$%&'()*+,-./:;<=>?@[\]^_`{|}~";
        $paginate = true;
        $page = 1;
        $perPage = 10;
        $useCache = false;

        $response = $this->service->read(
            companyId: $companyId, 
            search: $search, 
            paginate: $paginate, 
            page: $page,
            perPage: $perPage,
            useCache: $useCache
        );

        $this->assertInstanceOf(Paginator::class, $response);
        $this->assertNotNull($response);
    }
}
