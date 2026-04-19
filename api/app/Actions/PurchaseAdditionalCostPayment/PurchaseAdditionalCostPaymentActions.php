<?php

namespace App\Actions\PurchaseAdditionalCostPayment;

use App\Actions\CashTransaction\CashTransactionActions;
use App\Actions\Purchase\PurchaseActions;
use App\Actions\PurchaseAdditionalCost\PurchaseAdditionalCostActions;
use App\DTOs\CashTransactionCreateDTO;
use App\DTOs\CashTransactionUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\Helpers\TimezoneHelper;
use App\Models\PurchaseAdditionalCostPayment;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseAdditionalCostPaymentActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'purchaseAdditionalCost.purchase',
        'cashAccount',
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

        ?int $purchaseAdditionalCostId,
        ?int $includeId,

        ?ExecuteDTO $execute
    ) {
        $query = PurchaseAdditionalCostPayment::with(self::LIST_EAGER_LOADS)
            ->select('purchase_additional_cost_payments.*')
            ->whereCompanyId('purchase_additional_cost_payments', $companyId)
            ->whereBranchId('purchase_additional_cost_payments', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $purchaseAdditionalCostId,
            $includeId,
        ) {
            $query->where(function ($query) use (
                $withTrashed,
                $search,
                $purchaseAdditionalCostId,
            ) {
                $query->withoutTrashed();
                if ($withTrashed) {
                    $query->withTrashed();
                }

                if ($search) {
                    $query->search($search);
                }

                if (! is_null($purchaseAdditionalCostId)) {
                    $query->where('purchase_additional_cost_payments.purchase_additional_cost_id', $purchaseAdditionalCostId);
                }
            });

            if ($includeId) {
                $query->orWhere('purchase_additional_cost_payments.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(purchase_additional_cost_payments.id, '.$includeId.') desc');
        }
        $query->orderBy('purchase_additional_cost_payments.date', 'desc');
        $query->orderBy('purchase_additional_cost_payments.code', 'desc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    $purchaseAdditionalCostId ?? '[null]',
                    $includeId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_purchase_additional_cost_payment_'.implode('_', $cacheParams);

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

    public function read(PurchaseAdditionalCostPayment $purchaseAdditionalCostPayment): PurchaseAdditionalCostPayment
    {
        return $purchaseAdditionalCostPayment->load(self::LIST_EAGER_LOADS);
    }

    public function generateDate(string $date): string
    {
        if ($date == config('dcslab.KEYWORDS.AUTO')) {
            $nowLocal = now(TimezoneHelper::getUserTimezone())->toDateTimeString();

            return TimezoneHelper::convertToUTC($nowLocal);
        }

        return TimezoneHelper::convertToUTC($date);
    }

    public function generateUniqueCode(int $companyId, string $code, ?int $exceptId): string
    {
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            $tryCount = 0;

            do {
                $count = PurchaseAdditionalCostPayment::whereCompanyId('purchase_additional_cost_payments', $companyId)->withTrashed()->count() + 1 + $tryCount;
                $code = 'PACP'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        }

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = PurchaseAdditionalCostPayment::whereCompanyId('purchase_additional_cost_payments', $companyId)
            ->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    public function create(array $data): PurchaseAdditionalCostPayment
    {
        $timer_start = microtime(true);

        try {
            $purchaseAdditionalCostPayment = new PurchaseAdditionalCostPayment();
            $purchaseAdditionalCostPayment->company_id = $data['company_id'];
            $purchaseAdditionalCostPayment->branch_id = $data['branch_id'];
            $purchaseAdditionalCostPayment->purchase_additional_cost_id = $data['purchase_additional_cost_id'];
            $purchaseAdditionalCostPayment->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $purchaseAdditionalCostPayment->date = $this->generateDate($data['date']);
            $purchaseAdditionalCostPayment->cash_account_id = $data['cash_account_id'];
            $purchaseAdditionalCostPayment->amount = $data['amount'];
            $purchaseAdditionalCostPayment->remarks = $data['remarks'];
            $purchaseAdditionalCostPayment->save();

            $this->cashTransactionActions->create(
                data: CashTransactionCreateDTO::fromPurchaseAdditionalCostPayment($purchaseAdditionalCostPayment)
            );

            $purchaseAdditionalCost = $purchaseAdditionalCostPayment->purchaseAdditionalCost;
            PurchaseAdditionalCostActions::updateSummary($purchaseAdditionalCost);
            PurchaseActions::updateSummary($purchaseAdditionalCost->purchase);
            $purchaseAdditionalCostPayment->refresh();

            $this->flushCache();

            return $purchaseAdditionalCostPayment;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(PurchaseAdditionalCostPayment $purchaseAdditionalCostPayment, array $data): PurchaseAdditionalCostPayment
    {
        $timer_start = microtime(true);

        try {
            $purchaseAdditionalCostPayment->code = $this->generateUniqueCode($purchaseAdditionalCostPayment->company_id, $data['code'], $purchaseAdditionalCostPayment->id);
            $purchaseAdditionalCostPayment->date = $this->generateDate($data['date']);
            $purchaseAdditionalCostPayment->cash_account_id = $data['cash_account_id'];
            $purchaseAdditionalCostPayment->amount = $data['amount'];
            $purchaseAdditionalCostPayment->remarks = $data['remarks'];
            $purchaseAdditionalCostPayment->save();

            $cashTransaction = $purchaseAdditionalCostPayment->cashTransaction;
            if (! $cashTransaction) {
                $this->cashTransactionActions->create(
                    data: CashTransactionCreateDTO::fromPurchaseAdditionalCostPayment($purchaseAdditionalCostPayment)
                );
            } else {
                $this->cashTransactionActions->update(
                    cashTransaction: $cashTransaction,
                    data: CashTransactionUpdateDTO::fromPurchaseAdditionalCostPayment($purchaseAdditionalCostPayment)
                );
            }

            $purchaseAdditionalCost = $purchaseAdditionalCostPayment->purchaseAdditionalCost;
            PurchaseAdditionalCostActions::updateSummary($purchaseAdditionalCost);
            PurchaseActions::updateSummary($purchaseAdditionalCost->purchase);
            $purchaseAdditionalCostPayment->refresh();

            $this->flushCache();

            return $purchaseAdditionalCostPayment;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseAdditionalCostPayment $purchaseAdditionalCostPayment): bool
    {
        $timer_start = microtime(true);
        $retval = false;

        try {
            $cashTransaction = $purchaseAdditionalCostPayment->cashTransaction;
            if ($cashTransaction) {
                $this->cashTransactionActions->delete($cashTransaction);
            }

            $purchaseAdditionalCost = $purchaseAdditionalCostPayment->purchaseAdditionalCost;
            $retval = $purchaseAdditionalCostPayment->delete();

            if ($purchaseAdditionalCost) {
                PurchaseAdditionalCostActions::updateSummary($purchaseAdditionalCost);
                PurchaseActions::updateSummary($purchaseAdditionalCost->purchase);
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
