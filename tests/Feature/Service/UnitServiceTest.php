<?php

namespace Tests\Feature\Service;

use App\Actions\RandomGenerator;
use App\Models\Company;
use App\Models\Unit;
use App\Services\UnitService;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\ServiceTestCase;

class UnitServiceTest extends ServiceTestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unitService = app(UnitService::class);
    }

    //region create
    public function test_unit_service_call_create_expect_db_has_record()
    {
        $this->markTestSkipped('Under Constructions');
    }

    public function test_unit_service_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->markTestSkipped('Under Constructions');
    }

    //endregion

    //region list

    public function test_unit_service_call_list_with_paginate_true_expect_Paginator_object()
    {
        $this->markTestSkipped('Under Constructions');
    }

    public function test_unit_service_call_list_with_paginate_false_expect_Collection_object()
    {
        $this->markTestSkipped('Under Constructions');
    }

    public function test_unit_service_call_list_with_nonexistance_companyId_expect_empty_collection()
    {
        $this->markTestSkipped('Under Constructions');
    }

    public function test_unit_service_call_list_with_search_parameter_expect_filtered_results()
    {
        $this->markTestSkipped('Under Constructions');
    }

    public function test_unit_service_call_list_with_page_parameter_negative_expect_results()
    {
        $this->markTestSkipped('Under Constructions');
    }

    public function test_unit_service_call_list_with_perpage_parameter_negative_expect_results()
    {
        $this->markTestSkipped('Under Constructions');
    }

    //endregion

    //region read

    public function test_unit_service_call_read_expect_object()
    {
        $this->markTestSkipped('Under Constructions');
    }

    //endregion

    //region update

    public function test_unit_service_call_update_expect_db_updated()
    {
        $this->markTestSkipped('Under Constructions');
    }

    public function test_unit_service_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->markTestSkipped('Under Constructions');
    }

    //endregion

    //region delete

    public function test_unit_service_call_delete_expect_bool()
    {
        $this->markTestSkipped('Under Constructions');
    }

    //endregion

    //region others

    //endregion
}
