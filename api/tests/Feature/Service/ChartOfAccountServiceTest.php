<?php

namespace Tests\Feature\Service;

use Exception;
use App\Models\User;
use App\Models\ChartOfAccount;
use App\Models\Company;
use Tests\ServiceTestCase;
use App\Services\ChartOfAccountService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Factories\Sequence;

class ChartOfAccountServiceTest extends ServiceTestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->chartOfAccountService = app(ChartOfAccountService::class);
    }

    /* #region create */
    public function test_chart_of_account_service_call_create_root_account_expect_successful()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();
        
        $companyId = $user->companies->first()->id;

        $result = $this->chartOfAccountService->createRootAccount($companyId);

        for ($i = 0; $i < count($result); $i++) {
            $this->assertDatabaseHas('chart_of_accounts', [
                'id' => $result[$i]->id,
                'company_id' => $result[$i]->company_id,
                'parent_id' => $result[$i]->parent_id,
                'code' => $result[$i]->code,
                'name' => $result[$i]->name,
                'account_type' => $result[$i]->account_type,
                'remarks' => $result[$i]->remarks,
            ]);
        }
    }
    /* #endregion */

    /* #region list */
    
    /* #endregion */

    /* #region read */
    
    /* #endregion */

    /* #region update */
    
    /* #endregion */

    /* #region delete */
    
    /* #endregion */

    /* #region others */
    
    /* #endregion */
}