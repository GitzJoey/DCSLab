<?php

namespace App\Services\ChartOfAccount;

use App\Enums\ChartOfAccountScopeEnum;
use App\Models\ChartOfAccount;
use App\Models\Company;

class ChartOfAccountSyncService
{
    public function __construct()
    {
    }

    public function sync(Company $company): void
    {
        $defaultChartOfAccounts = config('chart_of_accounts', []);

        $this->upsertChartOfAccountNodes($company, $defaultChartOfAccounts, null);
    }

    private function upsertChartOfAccountNodes(Company $company, array $nodes, ?ChartOfAccount $parent): void
    {
        foreach ($nodes as $node) {
            $chartOfAccount = ChartOfAccount::query()->firstOrNew([
                'company_id' => $company->id,
                'system_key' => $node['system_key'],
            ]);

            $chartOfAccount->company_id = $company->id;
            $chartOfAccount->scope = ChartOfAccountScopeEnum::SYSTEM;
            $chartOfAccount->system_key = $node['system_key'];
            $chartOfAccount->parent_id = $parent?->id;
            $chartOfAccount->source_type = null;
            $chartOfAccount->source_id = null;
            $chartOfAccount->code = $node['code'];
            $chartOfAccount->name = $node['name'];
            $chartOfAccount->account_type = $node['account_type'];
            $chartOfAccount->normal_balance = $node['normal_balance'];
            $chartOfAccount->level = $parent ? $parent->level + 1 : 1;
            $chartOfAccount->is_group = $node['is_group'];
            $chartOfAccount->is_active = $node['is_active'] ?? true;
            $chartOfAccount->remarks = $node['remarks'] ?? null;
            $chartOfAccount->save();

            $this->upsertChartOfAccountNodes($company, $node['children'] ?? [], $chartOfAccount);
        }
    }
}
