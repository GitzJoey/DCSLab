<?php

namespace App\Actions\PurchaseAdditionalCost;

use App\DTOs\ExecuteDTO;
use App\Models\Company;
use App\Models\PurchaseAdditionalCost;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseAdditionalCostActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,

        ?string $startDate,
        ?string $endDate,
        ?int $purchaseId,
        ?int $categoryId,

        ?ExecuteDTO $execute
    ) {
        $query = PurchaseAdditionalCost::select('purchase_additional_costs.*')
            ->with(['company', 'branch', 'purchase', 'category'])
            ->join('companies', 'companies.id', '=', 'purchase_additional_costs.company_id')
            ->whereCompanyId('purchase_additional_costs', $companyId)
            ->whereBranchId('purchase_additional_costs', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $startDate,
            $endDate,
            $purchaseId,
            $categoryId,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->search($search);
            }

            if ($startDate) {
                $query->where('purchase_additional_costs.date', '>=', $startDate);
            }

            if ($endDate) {
                $query->where('purchase_additional_costs.date', '<=', $endDate);
            }

            if ($purchaseId) {
                $query->where('purchase_additional_costs.purchase_id', $purchaseId);
            }

            if ($categoryId) {
                $query->where('purchase_additional_costs.category_id', $categoryId);
            }
        });

        $query->orderBy('purchase_additional_costs.date', 'desc')
            ->orderBy('purchase_additional_costs.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    $startDate ?? '[null]',
                    $endDate ?? '[null]',
                    $purchaseId ?? '[null]',
                    $categoryId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_purchase_additional_cost_'.implode('_', $cacheParams);

                if ($execute->useCache) {
                    $cacheResult = $this->readFromCache($cacheKey);
                    if ($cacheResult !== Config::get('dcslab.ERROR_RETURN_VALUE')) {
                        return $cacheResult;
                    }
                }

                if ($execute->pagination) {
                    $result = $query->paginate(
                        perPage: $execute->pagination->perPage,
                        columns: ['*'],
                        pageName: 'page',
                        page: $execute->pagination->page,
                    );
                } else {
                    if ($execute->get?->limit) {
                        $query->limit($execute->get->limit);
                    }
                    $result = $query->get();
                }

                $recordsCount = $result->count();

                if ($execute->useCache) {
                    $this->saveToCache($cacheKey, $result);
                }

                return $result;
            } catch (Exception $e) {
                $this->loggerDebug(__METHOD__, $e);
                throw $e;
            } finally {
                $execution_time = microtime(true) - $timer_start;
                $this->loggerPerformance(__METHOD__, $execution_time, $recordsCount);
            }
        }

        return $query;
    }

    public function read(PurchaseAdditionalCost $purchaseAdditionalCost): PurchaseAdditionalCost
    {
        return $purchaseAdditionalCost->load([
            'company',
            'branch',
            'purchase',
            'category',
        ]);
    }

    public function create(array $data): PurchaseAdditionalCost
    {
        $timer_start = microtime(true);

        try {
            $purchaseAdditionalCost = new PurchaseAdditionalCost();
            $purchaseAdditionalCost->company_id = $data['company_id'];
            $purchaseAdditionalCost->branch_id = $data['branch_id'];
            $purchaseAdditionalCost->purchase_id = $data['purchase_id'];
            $purchaseAdditionalCost->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $purchaseAdditionalCost->date = $data['date'];
            $purchaseAdditionalCost->category_id = $data['category_id'];
            $purchaseAdditionalCost->amount = $data['amount'];
            $purchaseAdditionalCost->remarks = $data['remarks'];
            $purchaseAdditionalCost->save();

            $this->flushCache();

            return $purchaseAdditionalCost;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(PurchaseAdditionalCost $purchaseAdditionalCost, array $data): PurchaseAdditionalCost
    {
        $timer_start = microtime(true);

        try {
            $purchaseAdditionalCost->company_id = $data['company_id'];
            $purchaseAdditionalCost->branch_id = $data['branch_id'];
            $purchaseAdditionalCost->purchase_id = $data['purchase_id'];
            $purchaseAdditionalCost->code = $this->generateUniqueCode($data['company_id'], $data['code'], $purchaseAdditionalCost->id);
            $purchaseAdditionalCost->date = $data['date'];
            $purchaseAdditionalCost->category_id = $data['category_id'];
            $purchaseAdditionalCost->amount = $data['amount'];
            $purchaseAdditionalCost->remarks = $data['remarks'];
            $purchaseAdditionalCost->save();

            $this->flushCache();

            return $purchaseAdditionalCost->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseAdditionalCost $purchaseAdditionalCost): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $purchaseAdditionalCost->delete();

            $this->flushCache();

            return $retval;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function generateUniqueCode(int $companyId, string $code, ?int $exceptId): string
    {
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            $company = Company::find($companyId);

            $tryCount = 0;
            do {
                $count = $company->purchaseAdditionalCosts()->withTrashed()->count() + 1 + $tryCount;
                $code = 'WH'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        } else {
            return $code;
        }
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = PurchaseAdditionalCost::whereCompanyId('purchase_additional_costs', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
