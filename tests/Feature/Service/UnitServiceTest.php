<?php

namespace Tests\Feature\Service;

use App\Models\Unit;
use App\Models\Company;
use Tests\ServiceTestCase;
use App\Services\UnitService;
use App\Actions\RandomGenerator;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Contracts\Pagination\Paginator;

class UnitServiceTest extends ServiceTestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(UnitService::class);
    }

    public function test_call_save()
    {
        $company_id = Company::inRandomOrder()->first()->id;
        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $description = $this->faker->sentence;
        $category = (new RandomGenerator())->generateNumber(1, 3);

        $this->service->create(
            company_id: $company_id,
            code: $code,
            name: $name,
            description: $description,
            category: $category
        );

        $this->assertDatabaseHas('units', [
            'company_id' => $company_id,
            'code' => $code,
            'name' => $name,
            'description' => $description,
            'category' => $category
        ]);
    }

    public function test_call_edit()
    {
        $company_id = Company::inRandomOrder()->first()->id;
        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $description = $this->faker->sentence;
        $category = (new RandomGenerator())->generateNumber(1, 3);

        $unit = Unit::create([
            'company_id' => $company_id,
            'code' => $code,
            'name' => $name,
            'description' => $description,
            'category' => $category
        ]);
        $id = $unit->id;

        $newCode = (new RandomGenerator())->generateAlphaNumeric(5);
        $newName = $this->faker->name;
        $newDescription = $this->faker->sentence;
        $newCategory = (new RandomGenerator())->generateNumber(1, 3);

        $this->service->update(
            id: $id,
            company_id: $company_id,
            code: $newCode,
            name: $newName,
            description: $newDescription,
            category: $newCategory
        );

        $this->assertDatabaseHas('units', [
            'id' => $id,
            'company_id' => $company_id,
            'code' => $newCode,
            'name' => $newName,
            'description' => $newDescription,
            'category' => $newCategory
        ]);
    }

    public function test_call_delete()
    {
        $company_id = Company::inRandomOrder()->first()->id;
        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $description = $this->faker->sentence;
        $category = (new RandomGenerator())->generateNumber(1, 3);

        $unit = Unit::create([
            'company_id' => $company_id,
            'code' => $code,
            'name' => $name,
            'description' => $description,
            'category' => $category
        ]);
        $id = $unit->id;

        $this->service->delete($id);

        $this->assertSoftDeleted('units', [
            'id' => $id
        ]);
    }

    public function test_call_read_when_user_have_units_read_with_empty_search()
    {
        $companyId = Company::has('units')->inRandomOrder()->first()->id;

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

    public function test_call_read_when_user_have_units_with_special_char_in_search()
    {
        $companyId = Company::has('units')->inRandomOrder()->first()->id;
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