<?php

namespace App\Rules;

use App\Services\BranchService;
use App\Services\CompanyService;
use App\Services\FinanceCashService;

use Illuminate\Container\Container;
use Illuminate\Contracts\Validation\Rule;

class uniqueCode implements Rule
{
    private $id;
    private $table;

    private $companyService;
    private $branchService;
    private $financeCashService;
    

    public function __construct($id, $table)
    {
        $this->id = $id;
        $this->table = $table;

        switch($this->table) {
            case 'company': 
                $this->companyService = Container::getInstance()->make(CompanyService::class);
                break;
            case 'branches':
                $this->branchService = Container::getInstance()->make(BranchService::class);
                break;
            case 'finance_cash': 
                $this->financeCashService = Container::getInstance()->make(FinanceCashService::class);
                break;
            default:
                break;
        }
    }


    public function passes($attribute, $code)
    {
        $count = 0;
        switch($this->table) {
            case 'company': 
                $count = $this->companyService->checkDuplicatedCode($this->id, $code);
                break;
            case 'branches': 
                $count = $this->branchService->checkDuplicatedCode($this->id, $code);
                break;
            case 'finance_cash': 
                $count = $this->financeCashService->checkDuplicatedCode($this->id, $code);
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
