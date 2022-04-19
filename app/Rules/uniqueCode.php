<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Config;

use App\Services\CompanyService;
use App\Services\BranchService;
use App\Services\ProductService;
use App\Services\SupplierService;
use App\Services\WarehouseService;

class uniqueCode implements Rule
{
    private ?int $userId;
    private ?int $companyId;
    private ?int $exceptId;
    private string $table;

    private CompanyService $companyService;
    private BranchService $branchService;
    private WarehouseService $warehouseService;
    private SupplierService $supplierService;
    private ProductService $productService;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(string $table, ?int $userId = null, ?int $companyId = null, ?int $exceptId = null)
    {
        $this->table = $table;
        $this->userId = $userId ? $userId : null;
        $this->companyId = $companyId ? $companyId : null;
        $this->exceptId = $exceptId ? $exceptId : null;

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
            case 'suppliers':
                $this->supplierService = Container::getInstance()->make(SupplierService::class);
                break;
            case 'products':
                $this->productService = Container::getInstance()->make(ProductService::class);
                break;
            default:
                break;
        }
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if ($value == Config::get('const.DEFAULT.KEYWORDS.AUTO')) return true;

        $is_duplicate = false;

        switch($this->table) {
            case 'companies':
                if (!is_null($this->userId)) 
                    $is_duplicate = $this->companyService->isUniqueCode($value, $this->userId, $this->exceptId);
                break;            
            case 'branches':
                if (!is_null($this->companyId))
                    $is_duplicate = $this->branchService->isUniqueCode($value, $this->companyId, $this->exceptId);
                break;
            case 'warehouses':
                if (!is_null($this->companyId))
                    $is_duplicate = $this->warehouseService->isUniqueCode($value, $this->companyId, $this->exceptId);
                break;    
            case 'suppliers':
                if (!is_null($this->companyId))
                    $is_duplicate = $this->supplierService->isUniqueCode($value, $this->companyId, $this->exceptId);
                break;
            case 'products':
                if (!is_null($this->companyId))
                    $is_duplicate = $this->productService->isUniqueCodeForProduct($value, $this->companyId, $this->exceptId);
                break;
            default:
                break;
        }

        return $is_duplicate;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('rules.unique_code');
    }
}
