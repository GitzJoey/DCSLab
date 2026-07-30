<?php

namespace App\Services\Company;

use App\Actions\Branch\BranchActions;
use App\Actions\CashAccount\CashAccountActions;
use App\Actions\ExpenseCategory\ExpenseCategoryActions;
use App\Actions\Investor\InvestorActions;
use App\Actions\StockAdjustmentCategory\StockAdjustmentCategoryActions;
use App\Actions\Supplier\SupplierActions;
use App\Actions\VatProfile\VatProfileActions;
use App\Actions\Warehouse\WarehouseActions;
use App\DTOs\BranchCreateDTO;
use App\DTOs\CashAccountCreateDTO;
use App\DTOs\ExpenseCategoryCreateDTO;
use App\DTOs\InvestorCreateDTO;
use App\DTOs\StockAdjustmentCategoryCreateDTO;
use App\DTOs\SupplierCreateDTO;
use App\DTOs\VatProfileCreateDTO;
use App\DTOs\WarehouseCreateDTO;
use App\Enums\ChartOfAccountScopeEnum;
use App\Enums\ExpenseCategoryTypeEnum;
use App\Enums\PaymentTermTypeEnum;
use App\Enums\RecordStatusEnum;
use App\Models\Branch;
use App\Models\ChartOfAccount;
use App\Models\Company;

class CompanyInitializationService
{
    public function __construct(
        private BranchActions $branchActions,
        private WarehouseActions $warehouseActions,
        private CashAccountActions $cashAccountActions,
        private VatProfileActions $vatProfileActions,
        private ExpenseCategoryActions $expenseCategoryActions,
        private InvestorActions $investorActions,
        private SupplierActions $supplierActions,
        private StockAdjustmentCategoryActions $stockAdjustmentCategoryActions,
    ) {
    }

    public function initializeDefaultData(Company $company): void
    {
        $mainBranch = $this->createMainBranch($company);
        $this->createDefaultWarehouses($company, $mainBranch);

        $this->createDefaultChartOfAccounts($company);
        $this->createDefaultInvestors($company);
        $this->createDefaultCashAccounts($company, $mainBranch);
        $this->createDefaultOperationalExpenseCategory($company);
        $this->createDefaultOtherExpenseCategory($company);

        $this->createDefaultVatProfiles($company);

        $this->createDefaultSuppliers($company);

        $this->createDefaultStockAdjustmentCategories($company);
    }

    private function createMainBranch(Company $company): Branch
    {
        return $this->branchActions->create(
            new BranchCreateDTO(
                companyId: $company->id,
                code: config('dcslab.KEYWORDS.AUTO'),
                name: 'Cab. Utama',
                address: null,
                city: null,
                contact: null,
                isMain: true,
                remarks: 'Cabang utama default saat perusahaan dibuat',
                status: RecordStatusEnum::ACTIVE,
            )
        );
    }

    private function createDefaultWarehouses(Company $company, Branch $branch): void
    {
        $dto = new WarehouseCreateDTO(
            companyId: $company->id,
            branchId: $branch->id,
            code: config('dcslab.KEYWORDS.AUTO'),
            name: 'G. Toko',
            address: null,
            city: null,
            contact: null,
            remarks: 'Gudang utama default saat perusahaan dibuat',
            status: RecordStatusEnum::ACTIVE->value,
        );
        $this->warehouseActions->create($dto);
    }

    private function createDefaultChartOfAccounts(Company $company): void
    {
        $defaultChartOfAccounts = config('chart_of_accounts', []);

        $upsertChartOfAccountNode = function (array $node, ?ChartOfAccount $parent) use ($company, &$upsertChartOfAccountNode): void {
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

            foreach ($node['children'] ?? [] as $childNode) {
                $upsertChartOfAccountNode($childNode, $chartOfAccount);
            }
        };

        foreach ($defaultChartOfAccounts as $node) {
            $upsertChartOfAccountNode($node, null);
        }
    }

    private function createDefaultCashAccounts(Company $company, Branch $branch): void
    {
        $defaultCashAccounts = [
            [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'code' => config('dcslab.KEYWORDS.AUTO'),
                'name' => 'Kas Tunai Toko',
                'is_bank' => false,
                'is_active' => true,
                'remarks' => 'Kas utama default saat perusahaan dibuat',
            ],
            [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'code' => config('dcslab.KEYWORDS.AUTO'),
                'name' => 'Kas Bank',
                'is_bank' => true,
                'is_active' => true,
                'remarks' => 'Bank utama default saat perusahaan dibuat',
            ],
        ];

        foreach ($defaultCashAccounts as $defaultCashAccount) {
            $dto = new CashAccountCreateDTO(
                companyId: $defaultCashAccount['company_id'],
                branchId: $defaultCashAccount['branch_id'],
                code: $defaultCashAccount['code'],
                name: $defaultCashAccount['name'],
                isBank: $defaultCashAccount['is_bank'],
                isActive: $defaultCashAccount['is_active'],
                remarks: $defaultCashAccount['remarks'],
            );
            $this->cashAccountActions->create($dto);
        }
    }

    private function createDefaultOperationalExpenseCategory(Company $company): void
    {
        $defaultExpenseCategories = [
            [
                'name' => 'Biaya Operasional Tetap',
                'sequence' => 0,
                'children' => [
                    [
                        'name' => 'Sewa',
                        'sequence' => 0,
                    ],
                    [
                        'name' => 'Gaji dan Upah',
                        'sequence' => 1,
                    ],
                    [
                        'name' => 'BPJS dan Tunjangan Tetap',
                        'sequence' => 2,
                    ],
                    [
                        'name' => 'Internet dan Langganan Software',
                        'sequence' => 3,
                    ],
                ],
            ],
            [
                'name' => 'Biaya Operasional Variabel',
                'sequence' => 1,
                'children' => [
                    [
                        'name' => 'Listrik dan Air',
                        'sequence' => 0,
                    ],
                    [
                        'name' => 'Bahan Habis Pakai',
                        'sequence' => 1,
                    ],
                    [
                        'name' => 'Transportasi Operasional',
                        'sequence' => 2,
                    ],
                    [
                        'name' => 'Perawatan dan Perbaikan',
                        'sequence' => 3,
                    ],
                    [
                        'name' => 'Biaya Pengiriman',
                        'sequence' => 4,
                    ],
                ],
            ],
            [
                'name' => 'Biaya Operasional Umum',
                'sequence' => 2,
                'children' => [
                    [
                        'name' => 'ATK dan Perlengkapan Kantor',
                        'sequence' => 0,
                    ],
                    [
                        'name' => 'Biaya Administrasi Bank',
                        'sequence' => 1,
                    ],
                    [
                        'name' => 'Biaya Telepon',
                        'sequence' => 2,
                    ],
                    [
                        'name' => 'Biaya Kebersihan',
                        'sequence' => 3,
                    ],
                    [
                        'name' => 'Biaya Keamanan',
                        'sequence' => 4,
                    ],
                    [
                        'name' => 'Biaya Operasional Umum Lainnya',
                        'sequence' => 5,
                    ],
                ],
            ],
        ];

        foreach ($defaultExpenseCategories as $defaultExpenseCategory) {
            $parentDTO = new ExpenseCategoryCreateDTO(
                companyId: $company->id,
                parentId: null,
                categoryType: ExpenseCategoryTypeEnum::EXPENSE->value,
                code: config('dcslab.KEYWORDS.AUTO'),
                name: $defaultExpenseCategory['name'],
                sequence: $defaultExpenseCategory['sequence'],
            );
            $parentExpenseCategory = $this->expenseCategoryActions->create($parentDTO);

            foreach ($defaultExpenseCategory['children'] as $defaultExpenseCategoryChild) {
                $childDTO = new ExpenseCategoryCreateDTO(
                    companyId: $company->id,
                    parentId: $parentExpenseCategory->id,
                    categoryType: null,
                    code: config('dcslab.KEYWORDS.AUTO'),
                    name: $defaultExpenseCategoryChild['name'],
                    sequence: $defaultExpenseCategoryChild['sequence'],
                );
                $this->expenseCategoryActions->create($childDTO);
            }
        }
    }

    private function createDefaultOtherExpenseCategory(Company $company): void
    {
        $defaultExpenseCategories = [
            [
                'name' => 'Biaya Lain-lain Umum',
                'sequence' => 0,
                'children' => [],
            ],
        ];

        foreach ($defaultExpenseCategories as $defaultExpenseCategory) {
            $parentDTO = new ExpenseCategoryCreateDTO(
                companyId: $company->id,
                parentId: null,
                categoryType: ExpenseCategoryTypeEnum::OTHER_EXPENSE->value,
                code: config('dcslab.KEYWORDS.AUTO'),
                name: $defaultExpenseCategory['name'],
                sequence: $defaultExpenseCategory['sequence'],
            );
            $parentExpenseCategory = $this->expenseCategoryActions->create($parentDTO);

            foreach ($defaultExpenseCategory['children'] as $defaultExpenseCategoryChild) {
                $childDTO = new ExpenseCategoryCreateDTO(
                    companyId: $company->id,
                    parentId: $parentExpenseCategory->id,
                    categoryType: null,
                    code: config('dcslab.KEYWORDS.AUTO'),
                    name: $defaultExpenseCategoryChild['name'],
                    sequence: $defaultExpenseCategoryChild['sequence'],
                );
                $this->expenseCategoryActions->create($childDTO);
            }
        }
    }

    private function createDefaultVatProfiles(Company $company): void
    {
        $defaultVatProfiles = [
            [
                'company_id' => $company->id,
                'code' => config('dcslab.KEYWORDS.AUTO'),
                'name' => 'Non PPN',
                // vat_rate 0 already means "no VAT"; the base fraction must stay
                // 1/1 because every VAT profile request enforces numerator >= 1.
                'vat_rate' => 0,
                'vat_base_numerator' => 1,
                'vat_base_denominator' => 1,
                'remarks' => 'Default profil tanpa PPN saat perusahaan dibuat',
                'is_active' => true,
            ],
            [
                'company_id' => $company->id,
                'code' => config('dcslab.KEYWORDS.AUTO'),
                'name' => 'PPN Efektif 11%',
                'vat_rate' => 12,
                'vat_base_numerator' => 11,
                'vat_base_denominator' => 12,
                'remarks' => 'Default profil PPN efektif 11% saat perusahaan dibuat',
                'is_active' => true,
            ],
            [
                'company_id' => $company->id,
                'code' => config('dcslab.KEYWORDS.AUTO'),
                'name' => 'PPN 12%',
                'vat_rate' => 12,
                'vat_base_numerator' => 1,
                'vat_base_denominator' => 1,
                'remarks' => 'Default profil PPN 12% saat perusahaan dibuat',
                'is_active' => true,
            ],
        ];

        foreach ($defaultVatProfiles as $defaultVatProfile) {
            $dto = new VatProfileCreateDTO(
                companyId: $defaultVatProfile['company_id'],
                code: $defaultVatProfile['code'],
                name: $defaultVatProfile['name'],
                vatRate: $defaultVatProfile['vat_rate'],
                vatBaseNumerator: $defaultVatProfile['vat_base_numerator'],
                vatBaseDenominator: $defaultVatProfile['vat_base_denominator'],
                remarks: $defaultVatProfile['remarks'],
                isActive: $defaultVatProfile['is_active'],
            );
            $this->vatProfileActions->create($dto);
        }
    }

    private function createDefaultSuppliers(Company $company): void
    {
        $defaultSuppliers = [
            [
                'company_id' => $company->id,
                'code' => config('dcslab.KEYWORDS.AUTO'),
                'name' => 'Supplier Umum',
                'address' => null,
                'city' => null,
                'payment_term_type' => PaymentTermTypeEnum::CASH_ON_DELIVERY,
                'payment_term' => 0,
                'taxable_enterprise' => false,
                'tax_id' => null,
                'status' => RecordStatusEnum::ACTIVE,
                'remarks' => 'Supplier default saat perusahaan dibuat',
            ],
        ];

        foreach ($defaultSuppliers as $defaultSupplier) {
            $this->supplierActions->create(
                new SupplierCreateDTO(
                    companyId: $defaultSupplier['company_id'],
                    code: $defaultSupplier['code'],
                    name: $defaultSupplier['name'],
                    address: $defaultSupplier['address'],
                    city: $defaultSupplier['city'],
                    paymentTermType: $defaultSupplier['payment_term_type'],
                    paymentTerm: $defaultSupplier['payment_term'],
                    taxableEnterprise: $defaultSupplier['taxable_enterprise'],
                    taxId: $defaultSupplier['tax_id'],
                    remarks: $defaultSupplier['remarks'],
                    status: $defaultSupplier['status'],
                )
            );
        }
    }

    private function createDefaultInvestors(Company $company): void
    {
        $defaultInvestors = [
            [
                'company_id' => $company->id,
                'code' => config('dcslab.KEYWORDS.AUTO'),
                'name' => 'Investor Umum',
                'remarks' => 'Investor default saat perusahaan dibuat',
            ],
        ];

        foreach ($defaultInvestors as $defaultInvestor) {
            $dto = new InvestorCreateDTO(
                companyId: $defaultInvestor['company_id'],
                code: $defaultInvestor['code'],
                name: $defaultInvestor['name'],
                remarks: $defaultInvestor['remarks'],
            );
            $this->investorActions->create($dto);
        }
    }

    private function createDefaultStockAdjustmentCategories(Company $company): void
    {
        $defaultStockAdjustmentCategories = [
            [
                'company_id' => $company->id,
                'code' => config('dcslab.KEYWORDS.AUTO'),
                'name' => 'Koreksi Pencatatan',
            ],
            [
                'company_id' => $company->id,
                'code' => config('dcslab.KEYWORDS.AUTO'),
                'name' => 'Stock Opname',
            ],
            [
                'company_id' => $company->id,
                'code' => config('dcslab.KEYWORDS.AUTO'),
                'name' => 'Hilang',
            ],
            [
                'company_id' => $company->id,
                'code' => config('dcslab.KEYWORDS.AUTO'),
                'name' => 'Rusak',
            ],
        ];

        foreach ($defaultStockAdjustmentCategories as $defaultStockAdjustmentCategory) {
            $dto = new StockAdjustmentCategoryCreateDTO(
                companyId: $defaultStockAdjustmentCategory['company_id'],
                code: $defaultStockAdjustmentCategory['code'],
                name: $defaultStockAdjustmentCategory['name'],
            );
            $this->stockAdjustmentCategoryActions->create($dto);
        }
    }
}
