<?php

namespace Tests\Feature\Service;

use TypeError;
use App\Models\User;
use Tests\ServiceTestCase;
use App\Services\UserService;
use App\Services\CompanyService;
use Illuminate\Support\Collection;
use Illuminate\Container\Container;
use Database\Seeders\CompanyTableSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CompanyServiceTest extends ServiceTestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $container = Container::getInstance();
        $this->service = $container->make(CompanyService::class);
        $this->userService = $container->make(UserService::class);

        if (User::count() == 0)
            $this->artisan('db:seed', ['--class' => 'UserTableSeeder']);

        if (User::has('companies')->count() == 0) {
            $companyPerUser = 3;
            $companySeeder = new CompanyTableSeeder();
            $companySeeder->callWith(CompanyTableSeeder::class, [$companyPerUser]);    
        }
    }

    public function test_call_read_when_user_have_companies()
    {
        $usr = User::has('companies')->get()->first();

        $response = $this->service->read($usr->id, '', true, 10);

        $this->assertInstanceOf(Paginator::class, $response);
        $this->assertNotNull($response);
    }

    public function test_call_read_when_user_doesnt_have_companies()
    {
        $usr = User::doesnthave('companies')->get();

        if ($usr->count() == 0) {
            $email = $this->faker->email;
            $selectedUsr = $this->userService->register('testing', $email, 'password', 'on');
        } else {
            $selectedUsr = $usr->shuffle()->first();
        }

        $response = $this->service->read($selectedUsr->id, '', true, 10);

        $this->assertInstanceOf(Paginator::class, $response);
        $this->assertNotNull($response);
    }
}
