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
        $isMain = (new RandomGenerator())->generateNumber(0, 1);
        $remarks = $this->faker->sentence;
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $this->service->create(
            company_id: $company_id,
            code: $code,
            name: $name,
            address: $address,
            city: $city,
            contact: $contact,
            isMain : $isMain,
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
        $isMain = (new RandomGenerator())->generateNumber(0, 1);
        $remarks = null;
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $this->service->create(
            company_id: $company_id,
            code: $code,
            name: $name,
            address: $address,
            city: $city,
            contact: $contact,
            isMain : $isMain,
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
        $isMain = (new RandomGenerator())->generateNumber(0, 1);
        $remarks = $this->faker->sentence;
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $branch = Branch::create([
            'company_id' => $company_id,
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'city' => $city,
            'contact' => $contact,
            'is_main' => $isMain,
            'remarks' => $remarks,
            'status' => $status
        ]);
        $id = $branch->id;

        $newCode = (new RandomGenerator())->generateAlphaNumeric(5);
        $newName = $this->faker->name;
        $newAddress = $this->faker->address;
        $newCity = $this->faker->city;
        $newContact = $this->faker->e164PhoneNumber;
        $newIsMain = (new RandomGenerator())->generateNumber(0, 1);
        $newRemarks = $this->faker->sentence;
        $newStatus = (new RandomGenerator())->generateNumber(0, 1);

        $this->service->update(
            id: $id,
            company_id: $company_id,
            code: $newCode,
            name: $newName,
            address: $newAddress,
            city: $newCity,
            contact: $newContact,
            isMain: $newIsMain,
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
        $isMain = (new RandomGenerator())->generateNumber(0, 1);
        $remarks = null;
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $branch = Branch::create([
            'company_id' => $company_id,
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'city' => $city,
            'contact' => $contact,
            'is_main' => $isMain,
            'remarks' => $remarks,
            'status' => $status
        ]);
        $id = $branch->id;

        $newCode = (new RandomGenerator())->generateAlphaNumeric(5);
        $newName = $this->faker->name;
        $newAddress = null;
        $newCity = null;
        $newContact = null;
        $newIsMain = (new RandomGenerator())->generateNumber(0, 1);
        $newRemarks = null;
        $newStatus = (new RandomGenerator())->generateNumber(0, 1);

        $this->service->update(
            id: $id,
            company_id: $company_id,
            code: $newCode,
            name: $newName,
            address: $newAddress,
            city: $newCity,
            contact: $newContact,
            isMain: $newIsMain,
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
        $isMain = (new RandomGenerator())->generateNumber(0, 1);
        $remarks = $this->faker->sentence;
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $branch = Branch::create([
            'company_id' => $company_id,
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'city' => $city,
            'contact' => $contact,
            'is_main' => $isMain,
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
