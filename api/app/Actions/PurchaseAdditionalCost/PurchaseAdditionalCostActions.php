<?php

namespace App\Actions\PurchaseAdditionalCost;

use App\Actions\CashTransaction\CashTransactionActions;
use App\Actions\Purchase\PurchaseActions;
use App\DTOs\CashTransactionCreateDTO;
use App\DTOs\CashTransactionUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\DTOs\PurchaseAdditionalCostCreateDTO;
use App\DTOs\PurchaseAdditionalCostUpdateDTO;
use App\Helpers\TimezoneHelper;
use App\Models\PurchaseAdditionalCost;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseAdditionalCostActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'purchase',
        'category',
        'paidImmediatelyCashAccount',
    ];

    private const DETAIL_EAGER_LOADS = [
        'company',
        'branch',
        'purchase',
        'category',
        'paidImmediatelyCashAccount',
        'payments.cashAccount',
    ];

    public function __construct(
        private readonly CashTransactionActions $cashTransactionActions,
    ) {
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,

        ?int $purchaseId,
        ?int $categoryId,
        ?bool $isAmountPayablePaidOff,
        ?int $includeId,

        ?ExecuteDTO $execute
    ) {
        $query = PurchaseAdditionalCost::with(self::LIST_EAGER_LOADS)
            ->select('purchase_additional_costs.*')
            ->whereCompanyId('purchase_additional_costs', $companyId)
            ->whereBranchId('purchase_additional_costs', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $purchaseId,
            $categoryId,
            $isAmountPayablePaidOff,
            $includeId,
        ) {
            $query->where(function ($query) use (
                $withTrashed,
                $search,
                $purchaseId,
                $categoryId,
                $isAmountPayablePaidOff,
            ) {
                $query->withoutTrashed();
                if ($withTrashed) {
                    $query->withTrashed();
                }

                if ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('purchase_additional_costs.code', 'like', '%'.$search.'%')
                            ->orWhere('purchase_additional_costs.remarks', 'like', '%'.$search.'%');
                    });
                }

                if (! is_null($purchaseId)) {
                    $query->where('purchase_additional_costs.purchase_id', $purchaseId);
                }

                if (! is_null($categoryId)) {
                    $query->where('purchase_additional_costs.purchase_additional_cost_category_id', $categoryId);
                }

                if (! is_null($isAmountPayablePaidOff)) {
                    $query->where('purchase_additional_costs.is_amount_payable_paid_off', $isAmountPayablePaidOff);
                }
            });

            if ($includeId) {
                $query->orWhere('purchase_additional_costs.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(purchase_additional_costs.id, '.$includeId.') desc');
        }
        $query->orderBy('purchase_additional_costs.date', 'desc');
        $query->orderBy('purchase_additional_costs.code', 'desc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    $purchaseId ?? '[null]',
                    $categoryId ?? '[null]',
                    is_null($isAmountPayablePaidOff) ? '[null]' : ($isAmountPayablePaidOff ? 'true' : 'false'),
                    $includeId ?? '[null]',
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
                        page: $execute->pagination->page
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
        return $purchaseAdditionalCost->load(self::DETAIL_EAGER_LOADS);
    }

    public function generateDate(string $date): string
    {
        if ($date == config('dcslab.KEYWORDS.AUTO')) {
            $nowLocal = now(TimezoneHelper::getUserTimezone())->toDateTimeString();

            return TimezoneHelper::convertToUTC($nowLocal);
        }

        return TimezoneHelper::convertToUTC($date);
    }

    public static function updateSummary(PurchaseAdditionalCost $purchaseAdditionalCost): void
    {
        $purchaseAdditionalCost->load('payments');

        $purchaseAdditionalCost->amount_payable_paid = (float) $purchaseAdditionalCost->payments->sum('amount');
        $purchaseAdditionalCost->amount_payable_due = max(
            0,
            (float) $purchaseAdditionalCost->amount_payable - (float) $purchaseAdditionalCost->amount_payable_paid
        );
        $purchaseAdditionalCost->is_amount_payable_paid_off = $purchaseAdditionalCost->amount_payable_due == 0;
        $purchaseAdditionalCost->amount_total =
            (float) $purchaseAdditionalCost->amount_paid_immediately +
            (float) $purchaseAdditionalCost->amount_payable;

        $purchaseAdditionalCost->save();
    }

    public function generateUniqueCode(int $companyId, string $code, ?int $exceptId): string
    {
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            $tryCount = 0;

            do {
                $count = PurchaseAdditionalCost::whereCompanyId('purchase_additional_costs', $companyId)->withTrashed()->count() + 1 + $tryCount;
                $code = 'PAC'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        }

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = PurchaseAdditionalCost::whereCompanyId('purchase_additional_costs', $companyId)
            ->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    public function create(PurchaseAdditionalCostCreateDTO $data): PurchaseAdditionalCost
    {
        $timer_start = microtime(true);

        try {
            $purchaseAdditionalCost = new PurchaseAdditionalCost();
            $purchaseAdditionalCost->company_id = $data->companyId;
            $purchaseAdditionalCost->branch_id = $data->branchId;
            $purchaseAdditionalCost->purchase_id = $data->purchaseId;
            $purchaseAdditionalCost->purchase_additional_cost_category_id = $data->purchaseAdditionalCostCategoryId;
            $purchaseAdditionalCost->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $purchaseAdditionalCost->date = $this->generateDate($data->date);
            $purchaseAdditionalCost->due_days = $data->dueDays;
            $purchaseAdditionalCost->paid_immediately_cash_account_id = $data->paidImmediatelyCashAccountId;
            $purchaseAdditionalCost->amount_paid_immediately = $data->amountPaidImmediately;
            $purchaseAdditionalCost->amount_payable = $data->amountPayable;
            $purchaseAdditionalCost->remarks = $data->remarks;
            $purchaseAdditionalCost->save();

            self::updateSummary($purchaseAdditionalCost);

            if (
                (float) $purchaseAdditionalCost->amount_paid_immediately > 0 &&
                ! is_null($purchaseAdditionalCost->paid_immediately_cash_account_id)
            ) {
                $this->cashTransactionActions->create(
                    data: CashTransactionCreateDTO::fromPurchaseAdditionalCost($purchaseAdditionalCost)
                );
            }

            PurchaseActions::updateSummary($purchaseAdditionalCost->purchase);
            $purchaseAdditionalCost->refresh();

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

    public function update(PurchaseAdditionalCost $purchaseAdditionalCost, PurchaseAdditionalCostUpdateDTO $data): PurchaseAdditionalCost
    {
        $timer_start = microtime(true);

        try {
            $purchaseAdditionalCost->purchase_id = $data->purchaseId;
            $purchaseAdditionalCost->purchase_additional_cost_category_id = $data->purchaseAdditionalCostCategoryId;
            $purchaseAdditionalCost->code = $this->generateUniqueCode($purchaseAdditionalCost->company_id, $data->code, $purchaseAdditionalCost->id);
            $purchaseAdditionalCost->date = $this->generateDate($data->date);
            $purchaseAdditionalCost->due_days = $data->dueDays;
            $purchaseAdditionalCost->paid_immediately_cash_account_id = $data->paidImmediatelyCashAccountId;
            $purchaseAdditionalCost->amount_paid_immediately = $data->amountPaidImmediately;
            $purchaseAdditionalCost->amount_payable = $data->amountPayable;
            $purchaseAdditionalCost->remarks = $data->remarks;
            $purchaseAdditionalCost->save();

            self::updateSummary($purchaseAdditionalCost);

            $cashTransaction = $purchaseAdditionalCost->cashTransaction;
            if (
                (float) $purchaseAdditionalCost->amount_paid_immediately > 0 &&
                ! is_null($purchaseAdditionalCost->paid_immediately_cash_account_id)
            ) {
                if (! $cashTransaction) {
                    $this->cashTransactionActions->create(
                        data: CashTransactionCreateDTO::fromPurchaseAdditionalCost($purchaseAdditionalCost)
                    );
                } else {
                    $this->cashTransactionActions->update(
                        cashTransaction: $cashTransaction,
                        data: CashTransactionUpdateDTO::fromPurchaseAdditionalCost($purchaseAdditionalCost)
                    );
                }
            } elseif ($cashTransaction) {
                $this->cashTransactionActions->delete($cashTransaction);
            }

            PurchaseActions::updateSummary($purchaseAdditionalCost->purchase);
            $purchaseAdditionalCost->refresh();

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

    public function delete(PurchaseAdditionalCost $purchaseAdditionalCost): bool
    {
        $timer_start = microtime(true);
        $retval = false;

        try {
            foreach ($purchaseAdditionalCost->payments as $payment) {
                $cashTransaction = $payment->cashTransaction;
                if ($cashTransaction) {
                    $this->cashTransactionActions->delete($cashTransaction);
                }
                $payment->delete();
            }

            $cashTransaction = $purchaseAdditionalCost->cashTransaction;
            if ($cashTransaction) {
                $this->cashTransactionActions->delete($cashTransaction);
            }

            $purchase = $purchaseAdditionalCost->purchase;
            $retval = $purchaseAdditionalCost->delete();

            if ($purchase) {
                PurchaseActions::updateSummary($purchase);
            }

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
}
