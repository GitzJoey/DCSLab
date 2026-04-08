<?php

namespace App\Actions\PurchaseOrderDownPayment;

use App\Actions\CashTransaction\CashTransactionActions;
use App\DTOs\CashTransactionCreateDTO;
use App\DTOs\CashTransactionUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\DTOs\PurchaseOrderDownPaymentCreateDTO;
use App\DTOs\PurchaseOrderDownPaymentUpdateDTO;
use App\Helpers\TimezoneHelper;
use App\Models\PurchaseOrderDownPayment;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseOrderDownPaymentActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct(
        private CashTransactionActions $cashTransactionActions,
    ) {
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,
        ?string $startDate,
        ?string $endDate,
        ?int $purchaseOrderId,
        ?int $supplierId,
        ?int $cashAccountId,
        ?ExecuteDTO $execute
    ) {
        $query = PurchaseOrderDownPayment::select('purchase_order_down_payments.*')
            ->with([
                'company',
                'branch',
                'purchaseOrder.supplier',
                'cashAccount',
            ])
            ->join('companies', 'companies.id', '=', 'purchase_order_down_payments.company_id')
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_down_payments.purchase_order_id')
            ->whereCompanyId('purchase_order_down_payments', $companyId)
            ->whereBranchId('purchase_order_down_payments', $branchId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $startDate, $endDate, $purchaseOrderId, $supplierId, $cashAccountId) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->search($search);
            }

            if ($startDate) {
                $query->where('purchase_order_down_payments.date', '>=', TimezoneHelper::convertToUTC($startDate));
            }

            if ($endDate) {
                $query->where('purchase_order_down_payments.date', '<=', TimezoneHelper::convertToUTC($endDate));
            }

            if ($purchaseOrderId) {
                $query->where('purchase_order_down_payments.purchase_order_id', $purchaseOrderId);
            }

            if ($supplierId) {
                $query->where('purchase_orders.supplier_id', $supplierId);
            }

            if ($cashAccountId) {
                $query->where('purchase_order_down_payments.cash_account_id', $cashAccountId);
            }
        });

        $query->orderBy('purchase_order_down_payments.date', 'desc')
            ->orderBy('purchase_order_down_payments.id', 'asc');

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
                    $purchaseOrderId ?? '[null]',
                    $supplierId ?? '[null]',
                    $cashAccountId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_purchase_order_down_payment_'.implode('_', $cacheParams);

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

    public function read(PurchaseOrderDownPayment $purchaseOrderDownPayment): PurchaseOrderDownPayment
    {
        return $purchaseOrderDownPayment->load([
            'company',
            'branch',
            'purchaseOrder.supplier',
            'cashAccount',
        ]);
    }

    public function create(PurchaseOrderDownPaymentCreateDTO $data): PurchaseOrderDownPayment
    {
        $timer_start = microtime(true);

        try {
            $purchaseOrderDownPayment = new PurchaseOrderDownPayment();
            $purchaseOrderDownPayment->company_id = $data->companyId;
            $purchaseOrderDownPayment->branch_id = $data->branchId;
            $purchaseOrderDownPayment->purchase_order_id = $data->purchaseOrderId;
            $purchaseOrderDownPayment->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $purchaseOrderDownPayment->date = $this->generateDate($data->date);
            $purchaseOrderDownPayment->cash_account_id = $data->cashAccountId;
            $purchaseOrderDownPayment->amount = $data->amount;
            $purchaseOrderDownPayment->remarks = $data->remarks;
            $purchaseOrderDownPayment->save();

            $this->cashTransactionActions->create(
                data: CashTransactionCreateDTO::fromPurchaseOrderDownPayment($purchaseOrderDownPayment)
            );

            $this->flushCache();

            return $purchaseOrderDownPayment;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(PurchaseOrderDownPayment $purchaseOrderDownPayment, PurchaseOrderDownPaymentUpdateDTO $data): PurchaseOrderDownPayment
    {
        $timer_start = microtime(true);

        try {
            $purchaseOrderDownPayment->code = $this->generateUniqueCode($purchaseOrderDownPayment->company_id, $data->code, $purchaseOrderDownPayment->id);
            $purchaseOrderDownPayment->date = $this->generateDate($data->date);
            $purchaseOrderDownPayment->cash_account_id = $data->cashAccountId;
            $purchaseOrderDownPayment->amount = $data->amount;
            $purchaseOrderDownPayment->remarks = $data->remarks;
            $purchaseOrderDownPayment->save();

            $cashTransaction = $purchaseOrderDownPayment->cashTransaction;
            if (! $cashTransaction) {
                $this->cashTransactionActions->create(
                    data: CashTransactionCreateDTO::fromPurchaseOrderDownPayment($purchaseOrderDownPayment)
                );
            } else {
                $this->cashTransactionActions->update(
                    cashTransaction: $cashTransaction,
                    data: CashTransactionUpdateDTO::fromPurchaseOrderDownPayment($purchaseOrderDownPayment)
                );
            }

            $this->flushCache();

            return $purchaseOrderDownPayment;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseOrderDownPayment $purchaseOrderDownPayment): bool
    {
        $timer_start = microtime(true);
        $retval = false;

        try {
            $cashTransaction = $purchaseOrderDownPayment->cashTransaction;
            if ($cashTransaction) $this->cashTransactionActions->delete($cashTransaction);

            $retval = $purchaseOrderDownPayment->delete();

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
                $count = PurchaseOrderDownPayment::whereCompanyId('purchase_order_down_payments', $companyId)->withTrashed()->count() + 1 + $tryCount;
                $code = 'PODP'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        } else {
            return $code;
        }
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = PurchaseOrderDownPayment::whereCompanyId('purchase_order_down_payments', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }
}
