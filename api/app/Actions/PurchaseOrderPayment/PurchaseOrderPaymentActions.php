<?php

namespace App\Actions\PurchaseOrderPayment;

use App\Actions\CashTransaction\CashTransactionActions;
use App\Actions\JournalEntry\JournalEntryActions;
use App\Actions\PurchaseOrder\PurchaseOrderActions;
use App\DTOs\CashTransactionCreateDTO;
use App\DTOs\CashTransactionUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\DTOs\JournalEntryCreateDTO;
use App\DTOs\JournalEntryItemDTO;
use App\DTOs\JournalEntryUpdateDTO;
use App\DTOs\PurchaseOrderPaymentCreateDTO;
use App\DTOs\PurchaseOrderPaymentUpdateDTO;
use App\Enums\AllocationStatusEnum;
use App\Enums\JournalEntryTypeEnum;
use App\Helpers\TimezoneHelper;
use App\Models\PurchaseOrderPayment;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseOrderPaymentActions
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
        ?string $allocationStatus,

        ?ExecuteDTO $execute
    ) {
        $query = PurchaseOrderPayment::select('purchase_order_payments.*')
            ->with(self::LIST_EAGER_LOADS)
            ->join('companies', 'companies.id', '=', 'purchase_order_payments.company_id')
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_payments.purchase_order_id')
            ->whereCompanyId('purchase_order_payments', $companyId)
            ->whereBranchId('purchase_order_payments', $branchId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $startDate, $endDate, $purchaseOrderId, $supplierId, $cashAccountId, $allocationStatus) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('purchase_order_payments.code', 'like', '%'.$search.'%')
                        ->orWhere('purchase_order_payments.remarks', 'like', '%'.$search.'%');
                });
            }

            if ($startDate) {
                $query->where('purchase_order_payments.date', '>=', TimezoneHelper::convertToUTC($startDate));
            }

            if ($endDate) {
                $query->where('purchase_order_payments.date', '<=', TimezoneHelper::convertToUTC($endDate));
            }

            if ($purchaseOrderId) {
                $query->where('purchase_order_payments.purchase_order_id', $purchaseOrderId);
            }

            if ($supplierId) {
                $query->where('purchase_orders.supplier_id', $supplierId);
            }

            if ($cashAccountId) {
                $query->where('purchase_order_payments.cash_account_id', $cashAccountId);
            }

            if ($allocationStatus === AllocationStatusEnum::NOT_FULLY_ALLOCATED->value) {
                $query->whereColumn('purchase_order_payments.amount_allocated', '<', 'purchase_order_payments.amount');
            }

            if ($allocationStatus === AllocationStatusEnum::FULLY_ALLOCATED->value) {
                $query->whereColumn('purchase_order_payments.amount_allocated', '>=', 'purchase_order_payments.amount')
                    ->where('purchase_order_payments.amount', '>', 0);
            }
        });

        $query->orderBy('purchase_order_payments.date', 'desc')
            ->orderBy('purchase_order_payments.id', 'asc');

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
                    $allocationStatus ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_purchase_order_payment_'.implode('_', $cacheParams);

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

    public function getAllocationStatuses(): array
    {
        return [
            [
                'name' => 'views.purchase_order.filters.payment_allocation_status_not_fully_allocated',
                'code' => AllocationStatusEnum::NOT_FULLY_ALLOCATED->value,
            ],
            [
                'name' => 'views.purchase_order.filters.payment_allocation_status_fully_allocated',
                'code' => AllocationStatusEnum::FULLY_ALLOCATED->value,
            ],
        ];
    }

    public function read(PurchaseOrderPayment $purchaseOrderPayment): PurchaseOrderPayment
    {
        return $purchaseOrderPayment->load(self::LIST_EAGER_LOADS);
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
                $count = PurchaseOrderPayment::whereCompanyId('purchase_order_payments', $companyId)->withTrashed()->count() + 1 + $tryCount;
                $code = 'POP'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        } else {
            return $code;
        }
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = PurchaseOrderPayment::whereCompanyId('purchase_order_payments', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    public function create(
        PurchaseOrderPaymentCreateDTO $data,
        bool $updateParentSummary,
    ): PurchaseOrderPayment {
        $timer_start = microtime(true);

        try {
            $purchaseOrderPayment = new PurchaseOrderPayment();
            $purchaseOrderPayment->company_id = $data->companyId;
            $purchaseOrderPayment->branch_id = $data->branchId;
            $purchaseOrderPayment->purchase_order_id = $data->purchaseOrderId;
            $purchaseOrderPayment->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $purchaseOrderPayment->date = $this->generateDate($data->date);
            $purchaseOrderPayment->cash_account_id = $data->cashAccountId;
            $purchaseOrderPayment->amount = $data->amount;
            $purchaseOrderPayment->remarks = $data->remarks;
            $purchaseOrderPayment->save();

            if ((float) $purchaseOrderPayment->amount > 0) {
                $this->cashTransactionActions->create(
                    data: CashTransactionCreateDTO::fromPurchaseOrderPayment($purchaseOrderPayment)
                );

                $this->journalEntryActions->create(
                    data: new JournalEntryCreateDTO(
                        companyId: $purchaseOrderPayment->company_id,
                        branchId: $purchaseOrderPayment->branch_id,
                        code: config('dcslab.KEYWORDS.AUTO'),
                        date: $purchaseOrderPayment->date,
                        journalType: JournalEntryTypeEnum::TRANSACTION->value,
                        sourceType: PurchaseOrderPayment::class,
                        sourceId: $purchaseOrderPayment->id,
                        referenceNo: $purchaseOrderPayment->code,
                        remarks: $purchaseOrderPayment->remarks,
                        items: $this->buildJournalEntryItems($purchaseOrderPayment),
                    )
                );
            }

            if ($updateParentSummary) {
                $purchaseOrder = $purchaseOrderPayment->purchaseOrder;
                PurchaseOrderActions::updateSummary($purchaseOrder);
                $purchaseOrderPayment->refresh();
            }

            $this->flushCache();

            return $purchaseOrderPayment;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(
        PurchaseOrderPayment $purchaseOrderPayment,
        PurchaseOrderPaymentUpdateDTO $data,
        bool $updateParentSummary,
    ): PurchaseOrderPayment {
        $timer_start = microtime(true);

        try {
            if ($data->amount < (float) $purchaseOrderPayment->amount_allocated) {
                throw new Exception('Purchase order payment amount cannot be less than its allocated amount.');
            }

            $purchaseOrderPayment->code = $this->generateUniqueCode($purchaseOrderPayment->company_id, $data->code, $purchaseOrderPayment->id);
            $purchaseOrderPayment->date = $this->generateDate($data->date);
            $purchaseOrderPayment->cash_account_id = $data->cashAccountId;
            $purchaseOrderPayment->amount = $data->amount;
            $purchaseOrderPayment->remarks = $data->remarks;
            $purchaseOrderPayment->save();

            $cashTransaction = $purchaseOrderPayment->cashTransaction;
            if ((float) $purchaseOrderPayment->amount > 0) {
                if (! $cashTransaction) {
                    $this->cashTransactionActions->create(
                        data: CashTransactionCreateDTO::fromPurchaseOrderPayment($purchaseOrderPayment)
                    );
                } else {
                    $this->cashTransactionActions->update(
                        cashTransaction: $cashTransaction,
                        data: CashTransactionUpdateDTO::fromPurchaseOrderPayment($purchaseOrderPayment)
                    );
                }
            } elseif ($cashTransaction) {
                $this->cashTransactionActions->delete($cashTransaction);
            }

            $journalEntry = $purchaseOrderPayment->journalEntry;
            if ((float) $purchaseOrderPayment->amount > 0) {
                if (! $journalEntry) {
                    $this->journalEntryActions->create(
                        data: new JournalEntryCreateDTO(
                            companyId: $purchaseOrderPayment->company_id,
                            branchId: $purchaseOrderPayment->branch_id,
                            code: config('dcslab.KEYWORDS.AUTO'),
                            date: $purchaseOrderPayment->date,
                            journalType: JournalEntryTypeEnum::TRANSACTION->value,
                            sourceType: PurchaseOrderPayment::class,
                            sourceId: $purchaseOrderPayment->id,
                            referenceNo: $purchaseOrderPayment->code,
                            remarks: $purchaseOrderPayment->remarks,
                            items: $this->buildJournalEntryItems($purchaseOrderPayment),
                        )
                    );
                } else {
                    $this->journalEntryActions->update(
                        journalEntry: $journalEntry,
                        data: new JournalEntryUpdateDTO(
                            branchId: $purchaseOrderPayment->branch_id,
                            code: $journalEntry->code,
                            date: $purchaseOrderPayment->date,
                            journalType: JournalEntryTypeEnum::TRANSACTION->value,
                            referenceNo: $purchaseOrderPayment->code,
                            remarks: $purchaseOrderPayment->remarks,
                            items: $this->buildJournalEntryItems($purchaseOrderPayment),
                        )
                    );
                }
            } elseif ($journalEntry) {
                $this->journalEntryActions->delete($journalEntry);
            }

            if ($updateParentSummary) {
                $purchaseOrder = $purchaseOrderPayment->purchaseOrder;
                PurchaseOrderActions::updateSummary($purchaseOrder);
                $purchaseOrderPayment->refresh();
            }

            $this->flushCache();

            return $purchaseOrderPayment;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseOrderPayment $purchaseOrderPayment): bool
    {
        $timer_start = microtime(true);
        $retval = false;

        try {
            if ((float) $purchaseOrderPayment->amount_allocated > 0 || $purchaseOrderPayment->invoicePayments()->exists()) {
                throw new Exception('Purchase order payment cannot be deleted because it is already allocated to an invoice.');
            }

            $cashTransaction = $purchaseOrderPayment->cashTransaction;
            if ($cashTransaction) $this->cashTransactionActions->delete($cashTransaction);

            $journalEntry = $purchaseOrderPayment->journalEntry;
            if ($journalEntry) $this->journalEntryActions->delete($journalEntry);

            $retval = $purchaseOrderPayment->delete();

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
     * §5 P1 — Dr supplier down payment asset / Cr cash account.
     *
     * @return JournalEntryItemDTO[]
     */
    private function buildJournalEntryItems(PurchaseOrderPayment $purchaseOrderPayment): array
    {
        $items = [];

        $items[] = new JournalEntryItemDTO(
            chartOfAccountId: $purchaseOrderPayment->company->assetCurrentSupplierDownPaymentChartOfAccount?->id,
            sequence: count($items) + 1,
            debit: (float) $purchaseOrderPayment->amount,
            credit: 0,
            remarks: $purchaseOrderPayment->remarks,
        );

        $items[] = new JournalEntryItemDTO(
            chartOfAccountId: $purchaseOrderPayment->cashAccount?->chartOfAccount?->id,
            sequence: count($items) + 1,
            debit: 0,
            credit: (float) $purchaseOrderPayment->amount,
            remarks: $purchaseOrderPayment->remarks,
        );

        return $items;
    }
}
