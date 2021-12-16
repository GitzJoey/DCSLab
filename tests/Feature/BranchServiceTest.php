<?php

namespace Tests\Feature;

use App\Models\Branch;

use App\Services\BranchService;
use App\Models\Company;
use Illuminate\Support\Facades\Config;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Contracts\Pagination\Paginator;
use App\Actions\RandomGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BranchServiceTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(BranchService::class);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_read()
    {
        $selectedCompanyId = Company::inRandomOrder()->get()[0]->id;
        session()->put(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'), Hashids::encode($selectedCompanyId));
        $response = $this->service->read();

        $this->assertInstanceOf(Paginator::class, $response);
        $this->assertTrue(!is_null($response));
    }
    
    public function test_create()
    {
        $company_id = Company::inRandomOrder()->get()[0]->id;
        $code = (new RandomGenerator())->generateNumber(1, 9999);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $contact = $this->faker->e164PhoneNumber;
        $remarks = null;
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $this->service->create(
            $company_id,
            $code,
            $name,
            $address,
            $city,
            $contact,
            $remarks,
            $status
        );

        $this->assertDatabaseHas('branches', [
            'company_id' => $company_id,
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'city' => $city,
            'contact' => $contact,
            'remarks' => $remarks,
            'status' => $status,
        ]);
    }

    public function test_update()
    {
        $company_id = Company::inRandomOrder()->get()[0]->id;
        $code = (new RandomGenerator())->generateNumber(1,9999);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $contact = $this->faker->e164PhoneNumber;
        $remarks = null;
        $status = (new RandomGenerator())->generateNumber(0, 1);
        $response = $this->service->create(
            $company_id,
            $code,
            $name,
            $address,
            $city,
            $contact,
            $remarks,
            $status
        );
        $rId = Hashids::decode($response)[0];

        $company_id_new = Company::inRandomOrder()->get()[0]->id;
        $code_new = (new RandomGenerator())->generateNumber(1,9999);
        $name_new = $this->faker->name;
        $address_new = $this->faker->address;
        $city_new = $this->faker->city;
        $contact_new = $this->faker->e164PhoneNumber;
        $remarks_new = null;
        $status_new = (new RandomGenerator())->generateNumber(0, 1);
        $response = $this->service->update(
            $rId, 
            $company_id_new, 
            $code_new, 
            $name_new,
            $address_new,
            $city_new,
            $contact_new,
            $remarks_new,
            $status_new
        );

        $this->assertDatabaseHas('branches', [
            'id' => $rId,
            'company_id' => $company_id,
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'city' => $city,
            'contact' => $contact,
            'remarks' => $remarks,
            'status' => $status,
        ]);
    }

    public function test_delete()
    {
        $company_id = Company::inRandomOrder()->get()[0]->id;
        $code = (new RandomGenerator())->generateNumber(1,9999);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $contact = $this->faker->e164PhoneNumber;
        $remarks = null;
        $status = (new RandomGenerator())->generateNumber(0, 1);
        $response = $this->service->create(
            $company_id,
            $code,
            $name,
            $address,
            $city,
            $contact,
            $remarks,
            $status
        );
        $rId = Hashids::decode($response)[0];

        $response = $this->service->delete($rId);
        $deleted_at = Branch::withTrashed()->find($rId)->deleted_at->format('Y-m-d H:i:s');
        
        $this->assertDatabaseHas('branches', [
            'id' => $rId,
            'deleted_at' => $deleted_at
        ]);
    }
}