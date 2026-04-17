<?php

namespace App\Actions\PurchaseOrderDownPaymentRefund;

use App\Actions\CashTransaction\CashTransactionActions;
use App\Actions\PurchaseOrder\PurchaseOrderActions;
use App\DTOs\CashTransactionCreateDTO;
use App\DTOs\CashTransactionUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\DTOs\PurchaseOrderDownPaymentRefundCreateDTO;
use App\DTOs\PurchaseOrderDownPaymentRefundUpdateDTO;
use App\Helpers\TimezoneHelper;
use App\Models\PurchaseOrderDownPaymentRefund;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseOrderDownPaymentRefundActions
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
        $query = PurchaseOrderDownPaymentRefund::select('purchase_order_down_payment_refunds.*')
            ->with([
                'company',
                'branch',
                'purchaseOrder.supplier',
                'cashAccount',
            ])
            ->join('companies', 'companies.id', '=', 'purchase_order_down_payment_refunds.company_id')
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_down_payment_refunds.purchase_order_id')
            ->whereCompanyId('purchase_order_down_payment_refunds', $companyId)
            ->whereBranchId('purchase_order_down_payment_refunds', $branchId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $startDate, $endDate, $purchaseOrderId, $supplierId, $cashAccountId) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->search($search);
            }

            if ($startDate) {
                $query->where('purchase_order_down_payment_refunds.date', '>=', TimezoneHelper::convertToUTC($startDate));
            }

            if ($endDate) {
                $query->where('purchase_order_down_payment_refunds.date', '<=', TimezoneHelper::convertToUTC($endDate));
            }

            if ($purchaseOrderId) {
                $query->where('purchase_order_down_payment_refunds.purchase_order_id', $purchaseOrderId);
            }

            if ($supplierId) {
                $query->where('purchase_orders.supplier_id', $supplierId);
            }

            if ($cashAccountId) {
                $query->where('purchase_order_down_payment_refunds.cash_account_id', $cashAccountId);
            }
        });

        $query->orderBy('purchase_order_down_payment_refunds.date', 'desc')
            ->orderBy('purchase_order_down_payment_refunds.id', 'asc');

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

                $cacheKey = 'read_any_purchase_order_down_payment_refund_'.implode('_', $cacheParams);

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

    public function read(PurchaseOrderDownPaymentRefund $purchaseOrderDownPaymentRefund): PurchaseOrderDownPaymentRefund
    {
        return $purchaseOrderDownPaymentRefund->load([
            'company',
            'branch',
            'purchaseOrder.supplier',
            'cashAccount',
        ]);
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
                $count = PurchaseOrderDownPaymentRefund::whereCompanyId('purchase_order_down_payment_refunds', $companyId)->withTrashed()->count() + 1 + $tryCount;
                $code = 'PODPR'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        } else {
            return $code;
        }
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = PurchaseOrderDownPaymentRefund::whereCompanyId('purchase_order_down_payment_refunds', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    public function create(
        PurchaseOrderDownPaymentRefundCreateDTO $data,
        bool $updateParentSummary,
    ): PurchaseOrderDownPaymentRefund {
        $timer_start = microtime(true);

        try {
            $purchaseOrderDownPaymentRefund = new PurchaseOrderDownPaymentRefund();
            $purchaseOrderDownPaymentRefund->company_id = $data->companyId;
            $purchaseOrderDownPaymentRefund->branch_id = $data->branchId;
            $purchaseOrderDownPaymentRefund->purchase_order_id = $data->purchaseOrderId;
            $purchaseOrderDownPaymentRefund->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $purchaseOrderDownPaymentRefund->date = $this->generateDate($data->date);
            $purchaseOrderDownPaymentRefund->cash_account_id = $data->cashAccountId;
            $purchaseOrderDownPaymentRefund->amount = $data->amount;
            $purchaseOrderDownPaymentRefund->remarks = $data->remarks;
            $purchaseOrderDownPaymentRefund->save();

            $this->cashTransactionActions->create(
                data: CashTransactionCreateDTO::fromPurchaseOrderDownPaymentRefund($purchaseOrderDownPaymentRefund)
            );

            if ($updateParentSummary) {
                $purchaseOrder = $purchaseOrderDownPaymentRefund->purchaseOrder;
                PurchaseOrderActions::updateSummary($purchaseOrder);
                $purchaseOrderDownPaymentRefund->refresh();
            }

            $this->flushCache();

            return $purchaseOrderDownPaymentRefund;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(
        PurchaseOrderDownPaymentRefund $purchaseOrderDownPaymentRefund,
        PurchaseOrderDownPaymentRefundUpdateDTO $data,
        bool $updateParentSummary,
    ): PurchaseOrderDownPaymentRefund {
        $timer_start = microtime(true);

        try {
            $purchaseOrderDownPaymentRefund->code = $this->generateUniqueCode($purchaseOrderDownPaymentRefund->company_id, $data->code, $purchaseOrderDownPaymentRefund->id);
            $purchaseOrderDownPaymentRefund->date = $this->generateDate($data->date);
            $purchaseOrderDownPaymentRefund->cash_account_id = $data->cashAccountId;
            $purchaseOrderDownPaymentRefund->amount = $data->amount;
            $purchaseOrderDownPaymentRefund->remarks = $data->remarks;
            $purchaseOrderDownPaymentRefund->save();

            $cashTransaction = $purchaseOrderDownPaymentRefund->cashTransaction;
            if (! $cashTransaction) {
                $this->cashTransactionActions->create(
                    data: CashTransactionCreateDTO::fromPurchaseOrderDownPaymentRefund($purchaseOrderDownPaymentRefund)
                );
            } else {
                $this->cashTransactionActions->update(
                    cashTransaction: $cashTransaction,
                    data: CashTransactionUpdateDTO::fromPurchaseOrderDownPaymentRefund($purchaseOrderDownPaymentRefund)
                );
            }

            if ($updateParentSummary) {
                $purchaseOrder = $purchaseOrderDownPaymentRefund->purchaseOrder;
                PurchaseOrderActions::updateSummary($purchaseOrder);
                $purchaseOrderDownPaymentRefund->refresh();
            }

            $this->flushCache();

            return $purchaseOrderDownPaymentRefund;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseOrderDownPaymentRefund $purchaseOrderDownPaymentRefund): bool
    {
        $timer_start = microtime(true);
        $retval = false;

        try {
            $cashTransaction = $purchaseOrderDownPaymentRefund->cashTransaction;
            if ($cashTransaction) $this->cashTransactionActions->delete($cashTransaction);

            $retval = $purchaseOrderDownPaymentRefund->delete();

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
