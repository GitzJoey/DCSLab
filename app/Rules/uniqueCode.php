<?php

namespace App\Rules;

use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Services\BranchService;
use App\Services\CompanyService;
use App\Services\WarehouseService;
use App\Services\CashService;
use App\Services\SupplierService;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductGroup;
use App\Models\ProductUnit;
use Illuminate\Container\Container;
use Illuminate\Contracts\Validation\Rule;

class uniqueCode implements Rule
{
    private $crud_status;
    private $id;
    private $table;

    private $companyService;
    private $branchService;
    private $warehouseService;
    private $CashService;
    private $SupplierService;
    private $productGroupService;
    private $productBrandService;
    private $productUnitService;
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
            case 'branches':
                $this->branchService = Container::getInstance()->make(BranchService::class);
                break;
            case 'warehouses':
                $this->warehouseService = Container::getInstance()->make(WarehouseService::class);
                break;
            case 'finance_cash': 
                $this->CashService = Container::getInstance()->make(CashService::class);
                break;
            case 'suppliers': 
                $this->SupplierService = Container::getInstance()->make(SupplierService::class);
                break;
            case 'product_groups': 
                $this->productGroupService = Container::getInstance()->make(ProductGroup::class);
                break;
            case 'product_brands': 
                $this->productBrandService = Container::getInstance()->make(ProductBrand::class);
                break;
            case 'product_units': 
                $this->productUnitService = Container::getInstance()->make(ProductUnit::class);
                break;
            case 'products': 
                $this->productService = Container::getInstance()->make(Product::class);
                break;
            case 'customer_groups': 
                $this->CustomerGroupService = Container::getInstance()->make(CustomerGroup::class);
                break;
            case 'customers': 
                $this->CustomerService = Container::getInstance()->make(Customer::class);
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
            case 'branches': 
                $count = $this->branchService->checkDuplicatedCode($this->id, $code);
                break;
            case 'warehouses': 
                $count = $this->warehouseService->checkDuplicatedCode($this->id, $code);
                break;
            case 'finance_cash': 
                $count = $this->CashService->checkDuplicatedCode($this->id, $code);
                break;
            case 'suppliers': 
                $count = $this->SupplierService->checkDuplicatedCode($this->id, $code);
                break;
            case 'product_groups': 
                $count = $this->productGroupService->checkDuplicatedCode($this->id, $code);
                break;
            case 'product_brands': 
                $count = $this->productBrandService->checkDuplicatedCode($this->id, $code);
                break;
            case 'product_units': 
                $count = $this->productUnitService->checkDuplicatedCode($this->id, $code);
                break;
            case 'product': 
                $count = $this->productService->checkDuplicatedCode($this->id, $code);
                break;
            case 'customer_groups': 
                $count = $this->CustomerGroupService->checkDuplicatedCode($this->id, $code);
                break;
            case 'customers': 
                $count = $this->CustomerService->checkDuplicatedCode($this->id, $code);
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
