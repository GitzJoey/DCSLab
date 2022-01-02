<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Actions\RandomGenerator;
use App\Services\CompanyService;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CompanyServiceTest extends TestCase
{
    use WithFaker;
    
    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(CompanyService::class);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_read()
    {
        $userId = User::inRandomOrder()->get()[0]->id;
        $response = $this->service->read($userId);

        $this->assertInstanceOf(Paginator::class, $response);
        $this->assertTrue(!is_null($response));
    }
    
    public function test_create()
    {
        $code = (new RandomGenerator())->generateNumber(1, 9999);
        $name = $this->faker->name;
        $default = 0;
        $status = (new RandomGenerator())->generateNumber(0, 1);
        $userId = User::inRandomOrder()->get()[0]->id;

        $this->service->create(
            $code,
            $name,
            $default,
            $status,
            $userId
        );

        $this->assertDatabaseHas('companies', [
            'code' => $code,
            'name' => $name,
            'status' => $status
        ]);
    }

    public function test_update()
    {
        $code = (new RandomGenerator())->generateNumber(1, 9999);
        $name = $this->faker->name;
        $default = 0;
        $status = (new RandomGenerator())->generateNumber(0, 1);
        $userId = User::inRandomOrder()->get()[0]->id;

        $response = $this->service->create(
            $code,
            $name,
            $default,
            $status,
            $userId
        );
        $rId = Hashids::decode($response)[0];

        $code_new = (new RandomGenerator())->generateNumber(1, 9999);
        $name_new = $this->faker->name;
        $default_new = 0;
        $status_new = (new RandomGenerator())->generateNumber(0, 1);
        $userId_new = User::inRandomOrder()->get()[0]->id;
        $response = $this->service->update(
            $rId,
            $code_new,
            $name_new,
            $default_new,
            $status_new
        );


        $this->assertDatabaseHas('companies', [
            'id' => $rId,
            'code' => $code_new,
            'name' => $name_new,
            'default' => $default_new,
            'status' => $status_new
        ]);
    }

    public function test_delete()
    {
        $code = (new RandomGenerator())->generateNumber(1, 9999);
        $name = $this->faker->name;
        $default = 0;
        $status = (new RandomGenerator())->generateNumber(0, 1);
        $userId = User::inRandomOrder()->get()[0]->id;

        $response = $this->service->create(
            $code,
            $name,
            $default,
            $status,
            $userId
        );
        $rId = Hashids::decode($response)[0];

        $response = $this->service->delete($userId, $rId);
        $deleted_at = Company::withTrashed()->find($rId)->deleted_at->format('Y-m-d H:i:s');
        
        $this->assertDatabaseHas('companies', [
            'id' => $rId,
            'deleted_at' => $deleted_at
        ]);
    }
}
