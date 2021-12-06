<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

use Illuminate\Container\Container;
use App\Services\CompanyService;
use App\Services\SupplierService;

class uniqueCode implements Rule
{
    private $userId;
    private $exceptId;
    private $table;

    private $companyService;
    private $supplierService;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($table, $userId, $exceptId = null)
    {
        $this->userId = $userId;
        $this->exceptId = $exceptId;
        $this->table = $table;

        switch($this->table) {
            case 'companies':
                $this->companyService = Container::getInstance()->make(CompanyService::class);
                break;
            case 'suppliers':
                $this->supplierService = Container::getInstance()->make(SupplierService::class);
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
        $is_duplicate = false;
        switch($this->table) {
            case 'companies':
                $is_duplicate = $this->companyService->isUniqueCode($value, $this->userId, $this->exceptId);
                break;
            case 'suppliers':
                $is_duplicate = $this->supplierService->isUniqueCode($value, $this->userId, $this->exceptId);
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
