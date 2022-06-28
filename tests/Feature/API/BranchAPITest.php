<?php

namespace Tests\Feature\API;

use App\Models\Branch;
use Tests\APITestCase;
use App\Enums\ActiveStatus;
use App\Actions\RandomGenerator;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BranchAPITest extends APITestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        Parent::setUp();
    }

    #region store

    public function test_branch_api_call_store_expect_successfull()
    {
        $this->markTestSkipped('Under Constructions');
    }

    public function test_branch_api_call_store_with_existing_code_in_same_company_expect_failed()
    {
        $this->markTestSkipped('Under Constructions');
    }

    public function test_branch_api_call_store_with_existing_code_in_different_company_expect_successfull()
    {
        $this->markTestSkipped('Under Constructions');
    }

    public function test_branch_api_call_store_with_empty_string_parameters_expect_failed()
    {
        $this->markTestSkipped('Under Constructions');
    }

    #endregion

    #region list

    #endregion

    #region read

    #endregion

    #region update

    #endregion

    #region delete

    #endregion
}