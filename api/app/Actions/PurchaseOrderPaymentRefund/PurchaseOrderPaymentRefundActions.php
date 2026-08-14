<?php

namespace App\Actions\PurchaseOrderPaymentRefund;

use App\Actions\CashTransaction\CashTransactionActions;
use App\Actions\JournalEntry\JournalEntryActions;
use App\Actions\PurchaseOrder\PurchaseOrderActions;
use App\DTOs\CashTransactionCreateDTO;
use App\DTOs\CashTransactionUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\DTOs\JournalEntryCreateDTO;
use App\DTOs\JournalEntryItemDTO;
use App\DTOs\JournalEntryUpdateDTO;
use App\DTOs\PurchaseOrderPaymentRefundCreateDTO;
use App\DTOs\PurchaseOrderPaymentRefundUpdateDTO;
use App\Enums\JournalEntryTypeEnum;
use App\Helpers\TimezoneHelper;
use App\Models\PurchaseOrderPaymentRefund;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseOrderPaymentRefundActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'purchaseOrder.supplier',
        'cashAccount',
    ];

    public function __construct(
        private CashTransactionActions $cashTransactionActions,
        private JournalEntryActions $journalEntryActions,
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
        $query = PurchaseOrderPaymentRefund::select('purchase_order_payment_refunds.*')
            ->with(self::LIST_EAGER_LOADS)
            ->join('companies', 'companies.id', '=', 'purchase_order_payment_refunds.company_id')
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_payment_refunds.purchase_order_id')
            ->whereCompanyId('purchase_order_payment_refunds', $companyId)
            ->whereBranchId('purchase_order_payment_refunds', $branchId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $startDate, $endDate, $purchaseOrderId, $supplierId, $cashAccountId) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('purchase_order_payment_refunds.code', 'like', '%'.$search.'%')
                        ->orWhere('purchase_order_payment_refunds.remarks', 'like', '%'.$search.'%');
                });
            }

            if ($startDate) {
                $query->where('purchase_order_payment_refunds.date', '>=', TimezoneHelper::convertToUTC($startDate));
            }

            if ($endDate) {
                $query->where('purchase_order_payment_refunds.date', '<=', TimezoneHelper::convertToUTC($endDate));
            }

            if ($purchaseOrderId) {
                $query->where('purchase_order_payment_refunds.purchase_order_id', $purchaseOrderId);
            }

            if ($supplierId) {
                $query->where('purchase_orders.supplier_id', $supplierId);
            }

            if ($cashAccountId) {
                $query->where('purchase_order_payment_refunds.cash_account_id', $cashAccountId);
            }
        });

        $query->orderBy('purchase_order_payment_refunds.date', 'desc')
            ->orderBy('purchase_order_payment_refunds.id', 'asc');

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

                $cacheKey = 'read_any_purchase_order_payment_refund_'.implode('_', $cacheParams);

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

    public function read(PurchaseOrderPaymentRefund $purchaseOrderPaymentRefund): PurchaseOrderPaymentRefund
    {
        return $purchaseOrderPaymentRefund->load(self::LIST_EAGER_LOADS);
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
                $count = PurchaseOrderPaymentRefund::whereCompanyId('purchase_order_payment_refunds', $companyId)->withTrashed()->count() + 1 + $tryCount;
                $code = 'POPR'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        } else {
            return $code;
        }
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = PurchaseOrderPaymentRefund::whereCompanyId('purchase_order_payment_refunds', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    public function create(
        PurchaseOrderPaymentRefundCreateDTO $data,
        bool $updateParentSummary,
    ): PurchaseOrderPaymentRefund {
        $timer_start = microtime(true);

        try {
            $purchaseOrderPaymentRefund = new PurchaseOrderPaymentRefund();
            $purchaseOrderPaymentRefund->company_id = $data->companyId;
            $purchaseOrderPaymentRefund->branch_id = $data->branchId;
            $purchaseOrderPaymentRefund->purchase_order_id = $data->purchaseOrderId;
            $purchaseOrderPaymentRefund->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $purchaseOrderPaymentRefund->date = $this->generateDate($data->date);
            $purchaseOrderPaymentRefund->cash_account_id = $data->cashAccountId;
            $purchaseOrderPaymentRefund->amount = $data->amount;
            $purchaseOrderPaymentRefund->remarks = $data->remarks;
            $purchaseOrderPaymentRefund->save();

            if ((float) $purchaseOrderPaymentRefund->amount > 0) {
                $this->cashTransactionActions->create(
                    data: CashTransactionCreateDTO::fromPurchaseOrderPaymentRefund($purchaseOrderPaymentRefund)
                );

                $this->journalEntryActions->create(
                    data: new JournalEntryCreateDTO(
                        companyId: $purchaseOrderPaymentRefund->company_id,
                        branchId: $purchaseOrderPaymentRefund->branch_id,
                        code: config('dcslab.KEYWORDS.AUTO'),
                        date: $purchaseOrderPaymentRefund->date,
                        journalType: JournalEntryTypeEnum::TRANSACTION->value,
                        sourceType: PurchaseOrderPaymentRefund::class,
                        sourceId: $purchaseOrderPaymentRefund->id,
                        referenceNo: $purchaseOrderPaymentRefund->code,
                        remarks: $purchaseOrderPaymentRefund->remarks,
                        items: $this->buildJournalEntryItems($purchaseOrderPaymentRefund),
                    )
                );
            }

            if ($updateParentSummary) {
                $purchaseOrder = $purchaseOrderPaymentRefund->purchaseOrder;
                PurchaseOrderActions::updateSummary($purchaseOrder);
                $purchaseOrderPaymentRefund->refresh();
            }

            $this->flushCache();

            return $purchaseOrderPaymentRefund;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(
        PurchaseOrderPaymentRefund $purchaseOrderPaymentRefund,
        PurchaseOrderPaymentRefundUpdateDTO $data,
        bool $updateParentSummary,
    ): PurchaseOrderPaymentRefund {
        $timer_start = microtime(true);

        try {
            $purchaseOrderPaymentRefund->code = $this->generateUniqueCode($purchaseOrderPaymentRefund->company_id, $data->code, $purchaseOrderPaymentRefund->id);
            $purchaseOrderPaymentRefund->date = $this->generateDate($data->date);
            $purchaseOrderPaymentRefund->cash_account_id = $data->cashAccountId;
            $purchaseOrderPaymentRefund->amount = $data->amount;
            $purchaseOrderPaymentRefund->remarks = $data->remarks;
            $purchaseOrderPaymentRefund->save();

            $cashTransaction = $purchaseOrderPaymentRefund->cashTransaction;
            if ((float) $purchaseOrderPaymentRefund->amount > 0) {
                if (! $cashTransaction) {
                    $this->cashTransactionActions->create(
                        data: CashTransactionCreateDTO::fromPurchaseOrderPaymentRefund($purchaseOrderPaymentRefund)
                    );
                } else {
                    $this->cashTransactionActions->update(
                        cashTransaction: $cashTransaction,
                        data: CashTransactionUpdateDTO::fromPurchaseOrderPaymentRefund($purchaseOrderPaymentRefund)
                    );
                }
            } elseif ($cashTransaction) {
                $this->cashTransactionActions->delete($cashTransaction);
            }

            $journalEntry = $purchaseOrderPaymentRefund->journalEntry;
            if ((float) $purchaseOrderPaymentRefund->amount > 0) {
                if (! $journalEntry) {
                    $this->journalEntryActions->create(
                        data: new JournalEntryCreateDTO(
                            companyId: $purchaseOrderPaymentRefund->company_id,
                            branchId: $purchaseOrderPaymentRefund->branch_id,
                            code: config('dcslab.KEYWORDS.AUTO'),
                            date: $purchaseOrderPaymentRefund->date,
                            journalType: JournalEntryTypeEnum::TRANSACTION->value,
                            sourceType: PurchaseOrderPaymentRefund::class,
                            sourceId: $purchaseOrderPaymentRefund->id,
                            referenceNo: $purchaseOrderPaymentRefund->code,
                            remarks: $purchaseOrderPaymentRefund->remarks,
                            items: $this->buildJournalEntryItems($purchaseOrderPaymentRefund),
                        )
                    );
                } else {
                    $this->journalEntryActions->update(
                        journalEntry: $journalEntry,
                        data: new JournalEntryUpdateDTO(
                            branchId: $purchaseOrderPaymentRefund->branch_id,
                            code: $journalEntry->code,
                            date: $purchaseOrderPaymentRefund->date,
                            journalType: JournalEntryTypeEnum::TRANSACTION->value,
                            referenceNo: $purchaseOrderPaymentRefund->code,
                            remarks: $purchaseOrderPaymentRefund->remarks,
                            items: $this->buildJournalEntryItems($purchaseOrderPaymentRefund),
                        )
                    );
                }
            } elseif ($journalEntry) {
                $this->journalEntryActions->delete($journalEntry);
            }

            if ($updateParentSummary) {
                $purchaseOrder = $purchaseOrderPaymentRefund->purchaseOrder;
                PurchaseOrderActions::updateSummary($purchaseOrder);
                $purchaseOrderPaymentRefund->refresh();
            }

            $this->flushCache();

            return $purchaseOrderPaymentRefund;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseOrderPaymentRefund $purchaseOrderPaymentRefund): bool
    {
        $timer_start = microtime(true);
        $retval = false;

        try {
            $cashTransaction = $purchaseOrderPaymentRefund->cashTransaction;
            if ($cashTransaction) $this->cashTransactionActions->delete($cashTransaction);

            $journalEntry = $purchaseOrderPaymentRefund->journalEntry;
            if ($journalEntry) $this->journalEntryActions->delete($journalEntry);

            $retval = $purchaseOrderPaymentRefund->delete();

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

    /**
     * §5 P2 — Dr cash account / Cr supplier down payment asset.
     *
     * @return JournalEntryItemDTO[]
     */
    private function buildJournalEntryItems(PurchaseOrderPaymentRefund $purchaseOrderPaymentRefund): array
    {
        $items = [];

        $items[] = new JournalEntryItemDTO(
            chartOfAccountId: $purchaseOrderPaymentRefund->cashAccount?->chartOfAccount?->id,
            sequence: count($items) + 1,
            debit: (float) $purchaseOrderPaymentRefund->amount,
            credit: 0,
            remarks: $purchaseOrderPaymentRefund->remarks,
        );

        $items[] = new JournalEntryItemDTO(
            chartOfAccountId: $purchaseOrderPaymentRefund->company->assetCurrentSupplierDownPaymentChartOfAccount?->id,
            sequence: count($items) + 1,
            debit: 0,
            credit: (float) $purchaseOrderPaymentRefund->amount,
            remarks: $purchaseOrderPaymentRefund->remarks,
        );

        return $items;
    }
}
