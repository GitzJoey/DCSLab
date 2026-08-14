<?php

namespace App\Actions\PurchaseInvoicePayment;

use App\Actions\CashTransaction\CashTransactionActions;
use App\Actions\JournalEntry\JournalEntryActions;
use App\Actions\PurchaseInvoice\PurchaseInvoiceActions;
use App\Actions\PurchaseOrder\PurchaseOrderActions;
use App\Actions\PurchaseReturn\PurchaseReturnActions;
use App\DTOs\CashTransactionCreateDTO;
use App\DTOs\CashTransactionUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\DTOs\JournalEntryCreateDTO;
use App\DTOs\JournalEntryItemDTO;
use App\DTOs\JournalEntryUpdateDTO;
use App\DTOs\PurchaseInvoicePaymentCreateDTO;
use App\DTOs\PurchaseInvoicePaymentUpdateDTO;
use App\Enums\JournalEntryTypeEnum;
use App\Enums\PaymentTypeEnum;
use App\Helpers\TimezoneHelper;
use App\Models\PurchaseInvoicePayment;
use App\Models\PurchaseOrderPayment;
use App\Models\PurchaseReturn;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseInvoicePaymentActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'purchaseInvoice.supplier',
        'cashAccount',
        'purchaseOrderPayment',
        'purchaseReturn',
    ];

    public function __construct(
        private readonly CashTransactionActions $cashTransactionActions,
        private readonly JournalEntryActions $journalEntryActions,
    ) {
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,

        ?string $startDate,
        ?string $endDate,
        ?int $purchaseInvoiceId,
        ?string $paymentType,
        ?int $cashAccountId,

        ?ExecuteDTO $execute
    ) {
        $query = PurchaseInvoicePayment::select('purchase_invoice_payments.*')
            ->with(self::LIST_EAGER_LOADS)
            ->whereCompanyId('purchase_invoice_payments', $companyId)
            ->whereBranchId('purchase_invoice_payments', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $startDate,
            $endDate,
            $purchaseInvoiceId,
            $paymentType,
            $cashAccountId,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('purchase_invoice_payments.code', 'like', '%'.$search.'%')
                        ->orWhere('purchase_invoice_payments.remarks', 'like', '%'.$search.'%');
                });
            }

            if ($startDate) {
                $query->where('purchase_invoice_payments.date', '>=', TimezoneHelper::convertToUTC($startDate));
            }

            if ($endDate) {
                $query->where('purchase_invoice_payments.date', '<=', TimezoneHelper::convertToUTC($endDate));
            }

            if (! is_null($purchaseInvoiceId)) {
                $query->where('purchase_invoice_payments.purchase_invoice_id', $purchaseInvoiceId);
            }

            if (! is_null($paymentType)) {
                $query->where('purchase_invoice_payments.payment_type', $paymentType);
            }

            if (! is_null($cashAccountId)) {
                $query->where('purchase_invoice_payments.cash_account_id', $cashAccountId);
            }
        });

        $query->orderBy('purchase_invoice_payments.date', 'desc');
        $query->orderBy('purchase_invoice_payments.code', 'desc');

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
                    $purchaseInvoiceId ?? '[null]',
                    $paymentType ?? '[null]',
                    $cashAccountId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_purchase_invoice_payment_'.implode('_', $cacheParams);

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

    public function read(PurchaseInvoicePayment $purchaseInvoicePayment): PurchaseInvoicePayment
    {
        return $purchaseInvoicePayment->load(self::LIST_EAGER_LOADS);
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
                $count = PurchaseInvoicePayment::whereCompanyId('purchase_invoice_payments', $companyId)->withTrashed()->count() + 1 + $tryCount;
                $code = 'PIP'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        }

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = PurchaseInvoicePayment::whereCompanyId('purchase_invoice_payments', $companyId)
            ->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    public function create(PurchaseInvoicePaymentCreateDTO $data, bool $updateParentSummary = true): PurchaseInvoicePayment
    {
        $timer_start = microtime(true);

        try {
            $paymentType = PaymentTypeEnum::from($data->paymentType);

            $purchaseInvoicePayment = new PurchaseInvoicePayment();
            $purchaseInvoicePayment->company_id = $data->companyId;
            $purchaseInvoicePayment->branch_id = $data->branchId;
            $purchaseInvoicePayment->purchase_invoice_id = $data->purchaseInvoiceId;
            $purchaseInvoicePayment->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $purchaseInvoicePayment->date = $this->generateDate($data->date);
            $purchaseInvoicePayment->payment_type = $paymentType;
            $purchaseInvoicePayment->cash_account_id = $paymentType === PaymentTypeEnum::CASH ? $data->cashAccountId : null;
            $purchaseInvoicePayment->purchase_order_payment_id = $paymentType === PaymentTypeEnum::DOWN_PAYMENT ? $data->purchaseOrderPaymentId : null;
            $purchaseInvoicePayment->purchase_return_id = $paymentType === PaymentTypeEnum::RETURN ? $data->purchaseReturnId : null;
            $purchaseInvoicePayment->amount = $data->amount;
            $purchaseInvoicePayment->remarks = $data->remarks;
            $purchaseInvoicePayment->save();

            if ($paymentType === PaymentTypeEnum::CASH) {
                $this->cashTransactionActions->create(
                    data: CashTransactionCreateDTO::fromPurchaseInvoicePayment($purchaseInvoicePayment)
                );
            }

            $this->syncTransactionJournal($purchaseInvoicePayment);

            if ($purchaseInvoicePayment->purchase_order_payment_id) {
                $this->recomputePurchaseOrderPaymentAllocated($purchaseInvoicePayment->purchaseOrderPayment);
            }

            if ($purchaseInvoicePayment->purchase_return_id) {
                PurchaseReturnActions::updateSummary($purchaseInvoicePayment->purchaseReturn);
            }

            if ($updateParentSummary) {
                PurchaseInvoiceActions::updateSummary($purchaseInvoicePayment->purchaseInvoice);
                $purchaseInvoicePayment->refresh();
            }

            $this->flushCache();

            return $purchaseInvoicePayment;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(PurchaseInvoicePayment $purchaseInvoicePayment, PurchaseInvoicePaymentUpdateDTO $data, bool $updateParentSummary = true): PurchaseInvoicePayment
    {
        $timer_start = microtime(true);

        try {
            $previousPurchaseOrderPaymentId = $purchaseInvoicePayment->purchase_order_payment_id;
            $previousPurchaseReturnId = $purchaseInvoicePayment->purchase_return_id;

            $paymentType = PaymentTypeEnum::from($data->paymentType);

            $purchaseInvoicePayment->code = $this->generateUniqueCode($purchaseInvoicePayment->company_id, $data->code, $purchaseInvoicePayment->id);
            $purchaseInvoicePayment->date = $this->generateDate($data->date);
            $purchaseInvoicePayment->payment_type = $paymentType;
            $purchaseInvoicePayment->cash_account_id = $paymentType === PaymentTypeEnum::CASH ? $data->cashAccountId : null;
            $purchaseInvoicePayment->purchase_order_payment_id = $paymentType === PaymentTypeEnum::DOWN_PAYMENT ? $data->purchaseOrderPaymentId : null;
            $purchaseInvoicePayment->purchase_return_id = $paymentType === PaymentTypeEnum::RETURN ? $data->purchaseReturnId : null;
            $purchaseInvoicePayment->amount = $data->amount;
            $purchaseInvoicePayment->remarks = $data->remarks;
            $purchaseInvoicePayment->save();

            $cashTransaction = $purchaseInvoicePayment->cashTransaction;
            if ($paymentType === PaymentTypeEnum::CASH) {
                if (! $cashTransaction) {
                    $this->cashTransactionActions->create(
                        data: CashTransactionCreateDTO::fromPurchaseInvoicePayment($purchaseInvoicePayment)
                    );
                } else {
                    $this->cashTransactionActions->update(
                        cashTransaction: $cashTransaction,
                        data: CashTransactionUpdateDTO::fromPurchaseInvoicePayment($purchaseInvoicePayment)
                    );
                }
            } elseif ($cashTransaction) {
                $this->cashTransactionActions->delete($cashTransaction);
            }

            $this->syncTransactionJournal($purchaseInvoicePayment);

            $affectedPurchaseOrderPaymentIds = array_unique(array_filter([
                $previousPurchaseOrderPaymentId,
                $purchaseInvoicePayment->purchase_order_payment_id,
            ]));
            foreach ($affectedPurchaseOrderPaymentIds as $purchaseOrderPaymentId) {
                $purchaseOrderPayment = PurchaseOrderPayment::find($purchaseOrderPaymentId);
                if ($purchaseOrderPayment) {
                    $this->recomputePurchaseOrderPaymentAllocated($purchaseOrderPayment);
                }
            }

            $affectedPurchaseReturnIds = array_unique(array_filter([
                $previousPurchaseReturnId,
                $purchaseInvoicePayment->purchase_return_id,
            ]));
            foreach ($affectedPurchaseReturnIds as $purchaseReturnId) {
                $purchaseReturn = PurchaseReturn::find($purchaseReturnId);
                if ($purchaseReturn) {
                    PurchaseReturnActions::updateSummary($purchaseReturn);
                }
            }

            if ($updateParentSummary) {
                PurchaseInvoiceActions::updateSummary($purchaseInvoicePayment->purchaseInvoice);
                $purchaseInvoicePayment->refresh();
            }

            $this->flushCache();

            return $purchaseInvoicePayment;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseInvoicePayment $purchaseInvoicePayment, bool $updateParentSummary = true): bool
    {
        $timer_start = microtime(true);
        $retval = false;

        try {
            $purchaseInvoice = $purchaseInvoicePayment->purchaseInvoice;
            $purchaseOrderPayment = $purchaseInvoicePayment->purchaseOrderPayment;
            $purchaseReturn = $purchaseInvoicePayment->purchaseReturn;

            $cashTransaction = $purchaseInvoicePayment->cashTransaction;
            if ($cashTransaction) {
                $this->cashTransactionActions->delete($cashTransaction);
            }

            $journalEntry = $purchaseInvoicePayment->journalEntry;
            if ($journalEntry) {
                $this->journalEntryActions->delete($journalEntry);
            }

            $retval = $purchaseInvoicePayment->delete();

            if ($purchaseOrderPayment) {
                $this->recomputePurchaseOrderPaymentAllocated($purchaseOrderPayment);
            }

            if ($purchaseReturn) {
                PurchaseReturnActions::updateSummary($purchaseReturn);
            }

            if ($updateParentSummary) {
                PurchaseInvoiceActions::updateSummary($purchaseInvoice);
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

    /**
     * Create / update / delete the TRANSACTION journal entry of a payment.
     *
     * cash (P6): Dr supplier AP / Cr cash account.
     * down_payment (P7): Dr supplier AP / Cr supplier down payment account.
     * return: NO journal entry (settlement happens inside the return document).
     */
    private function syncTransactionJournal(PurchaseInvoicePayment $purchaseInvoicePayment): void
    {
        $journalEntry = $purchaseInvoicePayment->journalEntry;
        $journalItems = $this->buildJournalItems($purchaseInvoicePayment);

        if (count($journalItems) === 0) {
            if ($journalEntry) {
                $this->journalEntryActions->delete($journalEntry);
            }

            return;
        }

        if (! $journalEntry) {
            $this->journalEntryActions->create(new JournalEntryCreateDTO(
                companyId: $purchaseInvoicePayment->company_id,
                branchId: $purchaseInvoicePayment->branch_id,
                code: config('dcslab.KEYWORDS.AUTO'),
                date: $purchaseInvoicePayment->date,
                journalType: JournalEntryTypeEnum::TRANSACTION->value,
                sourceType: PurchaseInvoicePayment::class,
                sourceId: $purchaseInvoicePayment->id,
                referenceNo: $purchaseInvoicePayment->code,
                remarks: $purchaseInvoicePayment->remarks,
                items: $journalItems,
            ));
        } else {
            $this->journalEntryActions->update($journalEntry, new JournalEntryUpdateDTO(
                branchId: $purchaseInvoicePayment->branch_id,
                code: $journalEntry->code,
                date: $purchaseInvoicePayment->date,
                journalType: JournalEntryTypeEnum::TRANSACTION->value,
                referenceNo: $purchaseInvoicePayment->code,
                remarks: $purchaseInvoicePayment->remarks,
                items: $journalItems,
            ));
        }
    }

    /**
     * @return JournalEntryItemDTO[]
     */
    private function buildJournalItems(PurchaseInvoicePayment $purchaseInvoicePayment): array
    {
        if ($purchaseInvoicePayment->payment_type === PaymentTypeEnum::RETURN) {
            return [];
        }

        $amount = (float) $purchaseInvoicePayment->amount;
        if ($amount <= 0) {
            return [];
        }

        $creditChartOfAccountId = (function () use ($purchaseInvoicePayment) {
            if ($purchaseInvoicePayment->payment_type === PaymentTypeEnum::CASH) {
                return $purchaseInvoicePayment->cashAccount?->chartOfAccount?->id;
            }

            return $purchaseInvoicePayment->company->assetCurrentSupplierDownPaymentChartOfAccount?->id;
        })();

        $supplierPayableChartOfAccountId = $purchaseInvoicePayment->purchaseInvoice?->supplier?->chartOfAccount?->id
            ?? $purchaseInvoicePayment->company->liabilityAccountPayableChartOfAccount?->id;

        $items = [];

        $items[] = new JournalEntryItemDTO(
            chartOfAccountId: $supplierPayableChartOfAccountId,
            sequence: count($items) + 1,
            debit: $amount,
            credit: 0,
            remarks: $purchaseInvoicePayment->remarks,
        );

        $items[] = new JournalEntryItemDTO(
            chartOfAccountId: $creditChartOfAccountId,
            sequence: count($items) + 1,
            debit: 0,
            credit: $amount,
            remarks: $purchaseInvoicePayment->remarks,
        );

        return $items;
    }

    /**
     * Recompute the allocated amount of a purchase order payment from the
     * alive invoice payments referencing it, then refresh the PO summary.
     */
    private function recomputePurchaseOrderPaymentAllocated(PurchaseOrderPayment $purchaseOrderPayment): void
    {
        $purchaseOrderPayment->amount_allocated = (float) PurchaseInvoicePayment::query()
            ->where('purchase_order_payment_id', $purchaseOrderPayment->id)
            ->where('payment_type', PaymentTypeEnum::DOWN_PAYMENT->value)
            ->sum('amount');
        $purchaseOrderPayment->save();

        PurchaseOrderActions::updateSummary($purchaseOrderPayment->purchaseOrder);
    }
}
