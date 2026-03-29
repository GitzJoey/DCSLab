<?php

namespace App\Actions\PurchasePayment;

use App\DTOs\ExecuteDTO;
use App\Models\Company;
use App\Models\PurchasePayment;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchasePaymentActions
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
        ?int $cashAccountId,

        ?ExecuteDTO $execute
    ) {
        $query = PurchasePayment::select('purchase_payments.*')
            ->with(['company', 'branch', 'purchase', 'cashAccount'])
            ->join('companies', 'companies.id', '=', 'purchase_payments.company_id')
            ->whereCompanyId('purchase_payments', $companyId)
            ->whereBranchId('purchase_payments', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $startDate,
            $endDate,
            $purchaseId,
            $cashAccountId,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->search($search);
            }

            if ($startDate) {
                $query->where('purchase_payments.date', '>=', $startDate);
            }

            if ($endDate) {
                $query->where('purchase_payments.date', '<=', $endDate);
            }

            if ($purchaseId) {
                $query->where('purchase_payments.purchase_id', $purchaseId);
            }

            if ($cashAccountId) {
                $query->where('purchase_payments.cash_account_id', $cashAccountId);
            }
        });

        $query->orderBy('purchase_payments.date', 'desc')
            ->orderBy('purchase_payments.id', 'asc');

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
                    $cashAccountId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_purchase_payment_'.implode('_', $cacheParams);

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

    public function read(PurchasePayment $purchasePayment): PurchasePayment
    {
        return $purchasePayment->load([
            'company',
            'branch',
            'purchase',
            'cashAccount',
        ]);
    }

    public function create(array $data): PurchasePayment
    {
        $timer_start = microtime(true);

        try {
            $purchasePayment = new PurchasePayment();
            $purchasePayment->company_id = $data['company_id'];
            $purchasePayment->branch_id = $data['branch_id'];
            $purchasePayment->purchase_id = $data['purchase_id'];
            $purchasePayment->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $purchasePayment->date = $data['date'];
            $purchasePayment->cash_account_id = $data['cash_account_id'];
            $purchasePayment->amount = $data['amount'];
            $purchasePayment->remarks = $data['remarks'];
            $purchasePayment->save();

            $this->flushCache();

            return $purchasePayment;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(PurchasePayment $purchasePayment, array $data): PurchasePayment
    {
        $timer_start = microtime(true);

        try {
            $purchasePayment->company_id = $data['company_id'];
            $purchasePayment->branch_id = $data['branch_id'];
            $purchasePayment->purchase_id = $data['purchase_id'];
            $purchasePayment->code = $this->generateUniqueCode($data['company_id'], $data['code'], $purchasePayment->id);
            $purchasePayment->date = $data['date'];
            $purchasePayment->cash_account_id = $data['cash_account_id'];
            $purchasePayment->amount = $data['amount'];
            $purchasePayment->remarks = $data['remarks'];
            $purchasePayment->save();

            $this->flushCache();

            return $purchasePayment->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchasePayment $purchasePayment): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $purchasePayment->delete();

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
                $count = $company->purchasePayments()->withTrashed()->count() + 1 + $tryCount;
                $code = 'PP'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        } else {
            return $code;
        }
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = PurchasePayment::whereCompanyId('purchase_payments', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
