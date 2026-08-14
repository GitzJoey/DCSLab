<?php

namespace App\Actions\SalesInvoicePayment;

use App\Actions\CashTransaction\CashTransactionActions;
use App\Actions\JournalEntry\JournalEntryActions;
use App\Actions\SalesInvoice\SalesInvoiceActions;
use App\Actions\SalesOrder\SalesOrderActions;
use App\Actions\SalesReturn\SalesReturnActions;
use App\DTOs\CashTransactionCreateDTO;
use App\DTOs\CashTransactionUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\DTOs\JournalEntryCreateDTO;
use App\DTOs\JournalEntryItemDTO;
use App\DTOs\JournalEntryUpdateDTO;
use App\DTOs\SalesInvoicePaymentCreateDTO;
use App\DTOs\SalesInvoicePaymentUpdateDTO;
use App\Enums\JournalEntryTypeEnum;
use App\Enums\PaymentTypeEnum;
use App\Helpers\TimezoneHelper;
use App\Models\SalesInvoicePayment;
use App\Models\SalesOrderPayment;
use App\Models\SalesReturn;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class SalesInvoicePaymentActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'salesInvoice.customer',
        'cashAccount',
        'salesOrderPayment',
        'salesReturn',
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
        ?int $salesInvoiceId,
        ?string $paymentType,
        ?int $cashAccountId,
        ?int $salesOrderPaymentId,
        ?int $salesReturnId,

        ?ExecuteDTO $execute
    ) {
        $query = SalesInvoicePayment::with(self::LIST_EAGER_LOADS)
            ->select('sales_invoice_payments.*')
            ->whereCompanyId('sales_invoice_payments', $companyId)
            ->whereBranchId('sales_invoice_payments', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $startDate,
            $endDate,
            $salesInvoiceId,
            $paymentType,
            $cashAccountId,
            $salesOrderPaymentId,
            $salesReturnId,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) {
                $query->withTrashed();
            }

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('sales_invoice_payments.code', 'like', '%'.$search.'%')
                        ->orWhere('sales_invoice_payments.remarks', 'like', '%'.$search.'%');
                });
            }

            if ($startDate) {
                $query->where('sales_invoice_payments.date', '>=', TimezoneHelper::convertToUTC($startDate));
            }

            if ($endDate) {
                $query->where('sales_invoice_payments.date', '<=', TimezoneHelper::convertToUTC($endDate));
            }

            if ($salesInvoiceId) {
                $query->where('sales_invoice_payments.sales_invoice_id', $salesInvoiceId);
            }

            if ($paymentType) {
                $query->where('sales_invoice_payments.payment_type', $paymentType);
            }

            if ($cashAccountId) {
                $query->where('sales_invoice_payments.cash_account_id', $cashAccountId);
            }

            if ($salesOrderPaymentId) {
                $query->where('sales_invoice_payments.sales_order_payment_id', $salesOrderPaymentId);
            }

            if ($salesReturnId) {
                $query->where('sales_invoice_payments.sales_return_id', $salesReturnId);
            }
        });

        $query->orderBy('sales_invoice_payments.date', 'desc');
        $query->orderBy('sales_invoice_payments.code', 'desc');

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
                    $salesInvoiceId ?? '[null]',
                    $paymentType ?? '[null]',
                    $cashAccountId ?? '[null]',
                    $salesOrderPaymentId ?? '[null]',
                    $salesReturnId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_sales_invoice_payment_'.implode('_', $cacheParams);

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

    public function read(SalesInvoicePayment $salesInvoicePayment): SalesInvoicePayment
    {
        return $salesInvoicePayment->load(self::LIST_EAGER_LOADS);
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
                $count = SalesInvoicePayment::whereCompanyId('sales_invoice_payments', $companyId)->withTrashed()->count() + 1 + $tryCount;
                $code = 'SIP'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        }

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = SalesInvoicePayment::whereCompanyId('sales_invoice_payments', $companyId)
            ->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    public function create(SalesInvoicePaymentCreateDTO $data, bool $updateParentSummary = true): SalesInvoicePayment
    {
        $timer_start = microtime(true);

        try {
            $salesInvoicePayment = new SalesInvoicePayment();
            $salesInvoicePayment->company_id = $data->companyId;
            $salesInvoicePayment->branch_id = $data->branchId;
            $salesInvoicePayment->sales_invoice_id = $data->salesInvoiceId;
            $salesInvoicePayment->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $salesInvoicePayment->date = $this->generateDate($data->date);
            $salesInvoicePayment->payment_type = $data->paymentType;
            $salesInvoicePayment->cash_account_id = $data->paymentType == PaymentTypeEnum::CASH->value ? $data->cashAccountId : null;
            $salesInvoicePayment->sales_order_payment_id = $data->paymentType == PaymentTypeEnum::DOWN_PAYMENT->value ? $data->salesOrderPaymentId : null;
            $salesInvoicePayment->sales_return_id = $data->paymentType == PaymentTypeEnum::RETURN->value ? $data->salesReturnId : null;
            $salesInvoicePayment->amount = $data->amount;
            $salesInvoicePayment->remarks = $data->remarks;
            $salesInvoicePayment->save();

            $this->applySideEffects($salesInvoicePayment);

            $this->refreshSalesOrderPaymentAllocation($salesInvoicePayment->sales_order_payment_id);
            $this->refreshSalesReturnSummary($salesInvoicePayment->sales_return_id);

            if ($updateParentSummary) {
                SalesInvoiceActions::updateSummary($salesInvoicePayment->salesInvoice);
                $salesInvoicePayment->refresh();
            }

            $this->flushCache();

            return $salesInvoicePayment;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(SalesInvoicePayment $salesInvoicePayment, SalesInvoicePaymentUpdateDTO $data, bool $updateParentSummary = true): SalesInvoicePayment
    {
        $timer_start = microtime(true);

        try {
            $previousSalesOrderPaymentId = $salesInvoicePayment->sales_order_payment_id;
            $previousSalesReturnId = $salesInvoicePayment->sales_return_id;

            $salesInvoicePayment->code = $this->generateUniqueCode($salesInvoicePayment->company_id, $data->code, $salesInvoicePayment->id);
            $salesInvoicePayment->date = $this->generateDate($data->date);
            $salesInvoicePayment->payment_type = $data->paymentType;
            $salesInvoicePayment->cash_account_id = $data->paymentType == PaymentTypeEnum::CASH->value ? $data->cashAccountId : null;
            $salesInvoicePayment->sales_order_payment_id = $data->paymentType == PaymentTypeEnum::DOWN_PAYMENT->value ? $data->salesOrderPaymentId : null;
            $salesInvoicePayment->sales_return_id = $data->paymentType == PaymentTypeEnum::RETURN->value ? $data->salesReturnId : null;
            $salesInvoicePayment->amount = $data->amount;
            $salesInvoicePayment->remarks = $data->remarks;
            $salesInvoicePayment->save();

            $this->applySideEffects($salesInvoicePayment);

            if ($previousSalesOrderPaymentId && $previousSalesOrderPaymentId !== $salesInvoicePayment->sales_order_payment_id) {
                $this->refreshSalesOrderPaymentAllocation($previousSalesOrderPaymentId);
            }
            $this->refreshSalesOrderPaymentAllocation($salesInvoicePayment->sales_order_payment_id);

            if ($previousSalesReturnId && $previousSalesReturnId !== $salesInvoicePayment->sales_return_id) {
                $this->refreshSalesReturnSummary($previousSalesReturnId);
            }
            $this->refreshSalesReturnSummary($salesInvoicePayment->sales_return_id);

            if ($updateParentSummary) {
                SalesInvoiceActions::updateSummary($salesInvoicePayment->salesInvoice);
                $salesInvoicePayment->refresh();
            }

            $this->flushCache();

            return $salesInvoicePayment;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(SalesInvoicePayment $salesInvoicePayment, bool $updateParentSummary = true): bool
    {
        $timer_start = microtime(true);
        $retval = false;

        try {
            $salesInvoice = $salesInvoicePayment->salesInvoice;
            $salesOrderPaymentId = $salesInvoicePayment->sales_order_payment_id;
            $salesReturnId = $salesInvoicePayment->sales_return_id;

            $cashTransaction = $salesInvoicePayment->cashTransaction;
            if ($cashTransaction) {
                $this->cashTransactionActions->delete($cashTransaction);
            }

            $journalEntry = $salesInvoicePayment->journalEntry;
            if ($journalEntry) {
                $this->journalEntryActions->delete($journalEntry);
            }

            $retval = $salesInvoicePayment->delete();

            $this->refreshSalesOrderPaymentAllocation($salesOrderPaymentId);
            $this->refreshSalesReturnSummary($salesReturnId);

            if ($updateParentSummary) {
                SalesInvoiceActions::updateSummary($salesInvoice);
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
     * Cash transaction (cash type only, money IN) + journal entry per payment type:
     * - cash: Dr cash account COA / Cr customer AR (S6).
     * - down_payment: Dr customer down payment liability / Cr customer AR (S7).
     * - return: no journal, no cash (roll-ups only).
     */
    private function applySideEffects(SalesInvoicePayment $salesInvoicePayment): void
    {
        $isCash = $salesInvoicePayment->payment_type == PaymentTypeEnum::CASH;

        $cashTransaction = $salesInvoicePayment->cashTransaction;
        if ($isCash && $salesInvoicePayment->cash_account_id && (float) $salesInvoicePayment->amount > 0) {
            if (! $cashTransaction) {
                $this->cashTransactionActions->create(
                    data: CashTransactionCreateDTO::fromSalesInvoicePayment($salesInvoicePayment)
                );
            } else {
                $this->cashTransactionActions->update(
                    cashTransaction: $cashTransaction,
                    data: CashTransactionUpdateDTO::fromSalesInvoicePayment($salesInvoicePayment)
                );
            }
        } elseif ($cashTransaction) {
            $this->cashTransactionActions->delete($cashTransaction);
        }

        $journalItems = $this->buildJournalItems($salesInvoicePayment);

        $journalEntry = $salesInvoicePayment->journalEntry;
        if (count($journalItems) == 0) {
            if ($journalEntry) {
                $this->journalEntryActions->delete($journalEntry);
            }

            return;
        }

        if (! $journalEntry) {
            $journalEntryDTO = new JournalEntryCreateDTO(
                companyId: $salesInvoicePayment->company_id,
                branchId: $salesInvoicePayment->branch_id,
                code: config('dcslab.KEYWORDS.AUTO'),
                date: $salesInvoicePayment->date,
                journalType: JournalEntryTypeEnum::TRANSACTION->value,
                sourceType: SalesInvoicePayment::class,
                sourceId: $salesInvoicePayment->id,
                referenceNo: $salesInvoicePayment->code,
                remarks: $salesInvoicePayment->remarks,
                items: $journalItems,
            );
            $this->journalEntryActions->create($journalEntryDTO);
        } else {
            $journalEntryDTO = new JournalEntryUpdateDTO(
                branchId: $salesInvoicePayment->branch_id,
                code: $journalEntry->code,
                date: $salesInvoicePayment->date,
                journalType: JournalEntryTypeEnum::TRANSACTION->value,
                referenceNo: $salesInvoicePayment->code,
                remarks: $salesInvoicePayment->remarks,
                items: $journalItems,
            );
            $this->journalEntryActions->update($journalEntry, $journalEntryDTO);
        }
    }

    private function buildJournalItems(SalesInvoicePayment $salesInvoicePayment): array
    {
        $amount = (float) $salesInvoicePayment->amount;

        if ($amount == 0.0 || $salesInvoicePayment->payment_type == PaymentTypeEnum::RETURN) {
            return [];
        }

        $customerAccountReceivableChartOfAccountId = $salesInvoicePayment->salesInvoice?->customer?->chartOfAccount?->id
            ?? $salesInvoicePayment->company->assetCurrentAccountReceivableChartOfAccount?->id;

        $debitChartOfAccountId = $salesInvoicePayment->payment_type == PaymentTypeEnum::CASH
            ? $salesInvoicePayment->cashAccount?->chartOfAccount?->id
            : $salesInvoicePayment->company->liabilityCustomerDownPaymentChartOfAccount?->id;

        $items = [];

        $items[] = new JournalEntryItemDTO(
            chartOfAccountId: $debitChartOfAccountId,
            sequence: count($items) + 1,
            debit: $amount,
            credit: 0,
            remarks: $salesInvoicePayment->remarks,
        );

        $items[] = new JournalEntryItemDTO(
            chartOfAccountId: $customerAccountReceivableChartOfAccountId,
            sequence: count($items) + 1,
            debit: 0,
            credit: $amount,
            remarks: $salesInvoicePayment->remarks,
        );

        return $items;
    }

    private function refreshSalesOrderPaymentAllocation(?int $salesOrderPaymentId): void
    {
        if (! $salesOrderPaymentId) {
            return;
        }

        $salesOrderPayment = SalesOrderPayment::query()->find($salesOrderPaymentId);
        if (! $salesOrderPayment) {
            return;
        }

        $salesOrderPayment->amount_allocated = (float) SalesInvoicePayment::query()
            ->where('sales_order_payment_id', $salesOrderPayment->id)
            ->where('payment_type', PaymentTypeEnum::DOWN_PAYMENT->value)
            ->sum('amount');
        $salesOrderPayment->save();

        $salesOrder = $salesOrderPayment->salesOrder;
        if ($salesOrder) {
            SalesOrderActions::updateSummary($salesOrder);
        }
    }

    private function refreshSalesReturnSummary(?int $salesReturnId): void
    {
        if (! $salesReturnId) {
            return;
        }

        $salesReturn = SalesReturn::query()->find($salesReturnId);
        if (! $salesReturn) {
            return;
        }

        SalesReturnActions::updateSummary($salesReturn);
    }
}
