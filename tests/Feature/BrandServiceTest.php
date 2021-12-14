<?php

namespace Tests\Feature;

use App\Actions\RandomGenerator;
use App\Services\BrandService;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Vinkla\Hashids\Facades\Hashids;

class BrandServiceTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(BrandService::class);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_read()
    {
        $id = User::find(1)->id;

        $response = $this->service->withSession(['__SELECTED__COMPANY' => ''])->read('');

        $this->assertInstanceOf(Paginator::class, $response);
        $this->assertTrue(!is_null($response));
        $this->assertTrue(true);
    }

    public function test_create()
    {
        $company_id = 1;
        $code = (new RandomGenerator())->generateNumber(1,9999);
        $name = $this->faker->name;

        $response = $this->service->create($company_id, $code, $name);
    
        $this->assertDatabaseHas('brands', [
            'name' => $this->faker->name
        ]);
    }

    public function test_update()
    {
        $id = '';
        $company_id = 1;
        $code = (new RandomGenerator())->generateNumber(1,9999);
        $name = $this->faker->name;
        $response = $this->service->create($company_id, $code, $name);

        $rId = Hashids::decode($response)[0];

        $name_new = $this->faker->name;
        $response = $this->service->update($id, $company_id, $code, $name);

        $this->assertDatabaseHas('brands', [
            'name' => $this->faker->name
        ]);
    }

    public function test_delete()
    {
        $this->assertDatabaseHas('brands', [
        ]);

    }
}
