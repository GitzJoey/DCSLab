<?php

namespace App\Services\Company;

use App\Actions\Branch\BranchActions;
use App\Actions\CashAccount\CashAccountActions;
use App\Actions\VatProfile\VatProfileActions;
use App\Actions\Warehouse\WarehouseActions;
use App\Enums\RecordStatusEnum;
use App\Models\Branch;
use App\Models\Company;

class CompanyInitializationService
{
    public function __construct(
        private BranchActions $branchActions,
        private WarehouseActions $warehouseActions,
        private CashAccountActions $cashAccountActions,
        private VatProfileActions $vatProfileActions,
    ) {
    }

    public function initializeDefaultData(Company $company): void
    {
        $mainBranch = $this->createMainBranch($company);
        $this->createDefaultWarehouses($company, $mainBranch);
        $this->createDefaultCashAccounts($company, $mainBranch);
        $this->createDefaultVatProfiles($company);
    }

    private function createMainBranch(Company $company): Branch
    {
        return $this->branchActions->create([
            'company_id' => $company->id,
            'code' => config('dcslab.KEYWORDS.AUTO'),
            'name' => 'Cabang Utama',
            'address' => $company->address,
            'city' => null,
            'contact' => null,
            'is_main' => true,
            'remarks' => 'Cabang utama default saat perusahaan dibuat',
            'status' => RecordStatusEnum::ACTIVE,
        ]);
    }

    private function createDefaultWarehouses(Company $company, Branch $branch): void
    {
        $defaultWarehouses = [
            [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'code' => config('dcslab.KEYWORDS.AUTO'),
                'name' => 'Gudang Utama',
                'address' => $company->address,
                'city' => null,
                'contact' => null,
                'remarks' => 'Gudang utama default saat perusahaan dibuat',
                'status' => RecordStatusEnum::ACTIVE,
            ],
        ];

        foreach ($defaultWarehouses as $defaultWarehouse) {
            $this->warehouseActions->create($defaultWarehouse);
        }
    }

    private function createDefaultCashAccounts(Company $company, Branch $branch): void
    {
        $defaultCashAccounts = [
            [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'code' => config('dcslab.KEYWORDS.AUTO'),
                'name' => 'Kas Utama',
                'is_bank' => false,
                'is_active' => true,
                'remarks' => 'Kas utama default saat perusahaan dibuat',
            ],
            [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'code' => config('dcslab.KEYWORDS.AUTO'),
                'name' => 'Bank Utama',
                'is_bank' => true,
                'is_active' => true,
                'remarks' => 'Bank utama default saat perusahaan dibuat',
            ],
        ];

        foreach ($defaultCashAccounts as $defaultCashAccount) {
            $this->cashAccountActions->create($defaultCashAccount);
        }
    }

    private function createDefaultVatProfiles(Company $company): void
    {
        $defaultVatProfiles = [
            [
                'company_id' => $company->id,
                'code' => config('dcslab.KEYWORDS.AUTO'),
                'name' => 'Non PPN',
                'vat_rate' => 0,
                'vat_base_numerator' => 0,
                'vat_base_denominator' => 1,
                'remarks' => 'Default profil tanpa PPN saat perusahaan dibuat',
                'is_active' => true,
            ],
            [
                'company_id' => $company->id,
                'code' => config('dcslab.KEYWORDS.AUTO'),
                'name' => 'PPN Efektif 11%',
                'vat_rate' => 0.12,
                'vat_base_numerator' => 11,
                'vat_base_denominator' => 12,
                'remarks' => 'Default profil PPN efektif 11% saat perusahaan dibuat',
                'is_active' => true,
            ],
            [
                'company_id' => $company->id,
                'code' => config('dcslab.KEYWORDS.AUTO'),
                'name' => 'PPN 12%',
                'vat_rate' => 0.12,
                'vat_base_numerator' => 1,
                'vat_base_denominator' => 1,
                'remarks' => 'Default profil PPN 12% saat perusahaan dibuat',
                'is_active' => true,
            ],
        ];

        foreach ($defaultVatProfiles as $defaultVatProfile) {
            $this->vatProfileActions->create($defaultVatProfile);
        }
    }
}
