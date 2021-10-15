<?php

namespace App\Rules;

use App\Services\BranchService;
use App\Services\CapitalGroupService;
use App\Services\CompanyService;
use App\Services\EmployeeService;
use App\Services\WarehouseService;
use App\Services\CashService;
use App\Services\CapitalService;
use App\Services\InvestorService;
use App\Services\SupplierService;
use App\Services\CustomerService;
use App\Services\CustomerGroupService;
use App\Services\ExpenseGroupService;
use App\Services\IncomeGroupService;
use App\Services\ProductService;
use App\Services\ProductBrandService;
use App\Services\ProductGroupService;
use App\Services\UnitService;
use Illuminate\Container\Container;
use Illuminate\Contracts\Validation\Rule;

class uniqueCode implements Rule
{
    private $crud_status;
    private $id;
    private $table;

    private $companyService;
    private $EmployeeService;
    private $branchService;
    private $warehouseService;
    private $CashService;
    private $investorService;
    private $capitalGroupService;
    private $capitalService;
    private $incomeGroupService;
    private $expenseGroupService;
    private $SupplierService;
    private $productGroupService;
    private $productBrandService;
    private $UnitService;
    private $productService;
    private $CustomerGroupService;
    private $CustomerService;
    

    public function __construct($crud_status, $id, $table)
    {
        $this->crud_status = $crud_status;
        $this->id = $id;
        $this->table = $table;

        switch($this->table) {
            case 'companies': 
                $this->companyService = Container::getInstance()->make(CompanyService::class);
                break;
            case 'employees': 
                $this->EmployeeService = Container::getInstance()->make(EmployeeService::class);
                break;
            case 'branches':
                $this->branchService = Container::getInstance()->make(BranchService::class);
                break;
            case 'warehouses':
                $this->warehouseService = Container::getInstance()->make(WarehouseService::class);
                break;
            case 'cashes': 
                $this->CashService = Container::getInstance()->make(CashService::class);
                break;
            case 'investors': 
                $this->investorService = Container::getInstance()->make(InvestorService::class);
                break;
            case 'capitals': 
                $this->capitalService = Container::getInstance()->make(CapitalService::class);
                break;
            case 'capitalgroups': 
                $this->capitalGroupService = Container::getInstance()->make(CapitalGroupService::class);
                break;
            case 'incomegroups': 
                $this->incomeGroupService = Container::getInstance()->make(IncomeGroupService::class);
                break;
            case 'expensegroups': 
                $this->expenseGroupService = Container::getInstance()->make(ExpenseGroupService::class);
                break;
            case 'suppliers': 
                $this->SupplierService = Container::getInstance()->make(SupplierService::class);
                break;
            case 'productgroups': 
                $this->productGroupService = Container::getInstance()->make(ProductGroupService::class);
                break;
            case 'productbrands': 
                $this->productBrandService = Container::getInstance()->make(ProductBrandService::class);
                break;
            case 'units': 
                $this->UnitService = Container::getInstance()->make(UnitService::class);
                break;
            case 'products': 
                $this->productService = Container::getInstance()->make(ProductService::class);
                break;
            case 'customergroups': 
                $this->CustomerGroupService = Container::getInstance()->make(CustomerGroupService::class);
                break;
            case 'customers': 
                $this->CustomerService = Container::getInstance()->make(CustomerService::class);
                break;
            default:
                break;
        }
    }


    public function passes($attribute, $code)
    {
        $count = 0;
        switch($this->table) {
            case 'companies': 
                $count = $this->companyService->checkDuplicatedCode($this->crud_status, $this->id, $code);
                break;
            case 'employees': 
                $count = $this->EmployeeService->checkDuplicatedCode($this->crud_status, $this->id, $code);
                break;
            case 'branches': 
                $count = $this->branchService->checkDuplicatedCode($this->crud_status, $this->id, $code);
                break;
            case 'warehouses': 
                $count = $this->warehouseService->checkDuplicatedCode($this->crud_status, $this->id, $code);
                break;
            case 'cashes': 
                $count = $this->CashService->checkDuplicatedCode($this->crud_status, $this->id, $code);
                break;
            case 'investors': 
                $count = $this->investorService->checkDuplicatedCode($this->crud_status, $this->id, $code);
                break;
            case 'capitals': 
                $count = $this->capitalService->checkDuplicatedCode($this->crud_status, $this->id, $code);
                break;
            case 'capitalgroups': 
                $count = $this->capitalGroupService->checkDuplicatedCode($this->crud_status, $this->id, $code);
                break;
            case 'incomegroups': 
                $count = $this->incomeGroupService->checkDuplicatedCode($this->crud_status, $this->id, $code);
                break;
            case 'expensegroups': 
                $count = $this->expenseGroupService->checkDuplicatedCode($this->crud_status, $this->id, $code);
                break;
            case 'suppliers': 
                $count = $this->SupplierService->checkDuplicatedCode($this->crud_status, $this->id, $code);
                break;
            case 'productgroups': 
                $count = $this->productGroupService->checkDuplicatedCode($this->crud_status, $this->id, $code);
                break;
            case 'productbrands': 
                $count = $this->productBrandService->checkDuplicatedCode($this->crud_status, $this->id, $code);
                break;
            case 'units': 
                $count = $this->UnitService->checkDuplicatedCode($this->crud_status, $this->id, $code);
                break;
            case 'products': 
                $count = $this->productService->checkDuplicatedCode($this->crud_status, $this->id, $code);
                break;
            case 'customergroups': 
                $count = $this->CustomerGroupService->checkDuplicatedCode($this->crud_status, $this->id, $code);
                break;
            case 'customers': 
                $count = $this->CustomerService->checkDuplicatedCode($this->crud_status, $this->id, $code);
                break;
            
            default:
                break;
        }
        return $count == 0 ? true : false;
    }

    public function message()
    {
        return trans('rules.unique_code');
    }
}
