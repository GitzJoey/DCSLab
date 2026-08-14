<?php

namespace App\Actions\Receivable;

use App\Actions\CashTransaction\CashTransactionActions;
use App\Actions\JournalEntry\JournalEntryActions;
use App\Actions\ReceivablePayment\ReceivablePaymentActions;
use App\DTOs\CashTransactionCreateDTO;
use App\DTOs\CashTransactionUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\DTOs\JournalEntryCreateDTO;
use App\DTOs\JournalEntryItemDTO;
use App\DTOs\JournalEntryUpdateDTO;
use App\DTOs\ReceivableCreateDTO;
use App\DTOs\ReceivablePaymentCreateDTO;
use App\DTOs\ReceivablePaymentUpdateDTO;
use App\DTOs\ReceivableUpdateDTO;
use App\Helpers\TimezoneHelper;
use App\Models\Receivable;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class ReceivableActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'category',
        'customer',
        'cashAccount',
    ];

    private const DETAIL_EAGER_LOADS = [
        'company',
        'branch',
        'category',
        'customer',
        'cashAccount',
        'payments.cashAccount',
    ];

    public function __construct(
        private readonly CashTransactionActions $cashTransactionActions,
        private readonly ReceivablePaymentActions $receivablePaymentActions,
        private readonly JournalEntryActions $journalEntryActions,
    ) {
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,

        ?int $categoryId,
        ?int $customerId,
        ?bool $isPaidOff,
        ?int $includeId,

        ?ExecuteDTO $execute
    ) {
        $query = Receivable::select('receivables.*');

        if ($execute?->pagination) {
            $query->with(self::DETAIL_EAGER_LOADS);
        } else {
            $query->with(self::LIST_EAGER_LOADS);
        }

        $query
            ->whereCompanyId('receivables', $companyId)
            ->whereBranchId('receivables', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $categoryId,
            $customerId,
            $isPaidOff,
            $includeId,
        ) {
            $query->where(function ($query) use (
                $withTrashed,
                $search,
                $categoryId,
                $customerId,
                $isPaidOff,
            ) {
                if ($withTrashed) $query->withTrashed();

                if ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('receivables.code', 'like', '%'.$search.'%')
                            ->orWhere('receivables.remarks', 'like', '%'.$search.'%')
                            ->orWhereHas('category', function ($categoryQuery) use ($search) {
                                $categoryQuery->where('receivable_categories.code', 'like', '%'.$search.'%')
                                    ->orWhere('receivable_categories.name', 'like', '%'.$search.'%');
                            })
                            ->orWhereHas('customer', function ($customerQuery) use ($search) {
                                $customerQuery->where('customers.code', 'like', '%'.$search.'%')
                                    ->orWhere('customers.name', 'like', '%'.$search.'%');
                            });
                    });
                }

                if (! is_null($categoryId)) {
                    $query->where('receivables.category_id', $categoryId);
                }

                if (! is_null($customerId)) {
                    $query->where('receivables.customer_id', $customerId);
                }

                if (! is_null($isPaidOff)) {
                    $query->where('receivables.is_paid_off', $isPaidOff);
                }
            });

            if ($includeId) {
                $query->orWhere('receivables.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(receivables.id, '.$includeId.') desc');
        }
        $query->orderBy('receivables.date', 'desc');
        $query->orderBy('receivables.code', 'desc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    $categoryId ?? '[null]',
                    $customerId ?? '[null]',
                    is_null($isPaidOff) ? '[null]' : ($isPaidOff ? 'true' : 'false'),
                    $includeId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_receivable_'.implode('_', $cacheParams);

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

    public function read(Receivable $receivable): Receivable
    {
        return $receivable->load(self::DETAIL_EAGER_LOADS);
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
                $count = Receivable::whereCompanyId('receivables', $companyId)->withTrashed()->count() + 1 + $tryCount;
                $code = 'REC'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        }

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = Receivable::whereCompanyId('receivables', $companyId)
            ->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    public function create(ReceivableCreateDTO $data): Receivable
    {
        $timer_start = microtime(true);

        try {
            $receivable = new Receivable();
            $receivable->company_id = $data->companyId;
            $receivable->branch_id = $data->branchId;
            $receivable->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $receivable->date = $this->generateDate($data->date);
            $receivable->category_id = $data->categoryId;
            $receivable->customer_id = $data->customerId;
            $receivable->cash_account_id = $data->cashAccountId;
            $receivable->direct_amount_received = $data->directAmountReceived;
            $receivable->opening_amount_due = $data->openingAmountDue;
            $receivable->due_days = $data->dueDays;
            $receivable->remarks = $data->remarks;
            $receivable->save();

            if ($receivable->cash_account_id && $receivable->direct_amount_received > 0) {
                $this->cashTransactionActions->create(
                    data: CashTransactionCreateDTO::fromReceivable($receivable)
                );
            }

            foreach ($data->payments as $payment) {
                $dto = new ReceivablePaymentCreateDTO(
                    companyId: $receivable->company_id,
                    branchId: $receivable->branch_id,
                    code: $payment['code'],
                    date: $payment['date'],
                    receivableId: $receivable->id,
                    cashAccountId: $payment['cash_account_id'],
                    amount: $payment['amount'],
                    remarks: $payment['remarks'],
                );
                $this->receivablePaymentActions->create($dto, false);
            }

            $journalEntryDTO = new JournalEntryCreateDTO(
                companyId: $receivable->company_id,
                branchId: $receivable->branch_id,
                code: config('dcslab.KEYWORDS.AUTO'),
                date: $receivable->date,
                sourceType: Receivable::class,
                sourceId: $receivable->id,
                referenceNo: $receivable->code,
                remarks: $receivable->remarks,
                items: (function () use ($receivable) {
                    $items = [];

                    if ((float) $receivable->direct_amount_received > 0) {
                        $items[] = new JournalEntryItemDTO(
                            chartOfAccountId: $receivable->cashAccount?->chartOfAccount?->id,
                            sequence: count($items) + 1,
                            debit: (float) $receivable->direct_amount_received,
                            credit: 0,
                            remarks: $receivable->remarks,
                        );
                    }

                    if ((float) $receivable->opening_amount_due > 0) {
                        $items[] = new JournalEntryItemDTO(
                            chartOfAccountId: $receivable->customer?->chartOfAccount?->id,
                            sequence: count($items) + 1,
                            debit: (float) $receivable->opening_amount_due,
                            credit: 0,
                            remarks: $receivable->remarks,
                        );
                    }

                    $items[] = new JournalEntryItemDTO(
                        chartOfAccountId: $receivable->company->equityCapitalOpeningCapitalChartOfAccount?->id,
                        sequence: count($items) + 1,
                        debit: 0,
                        credit: (float) $receivable->amount_total,
                        remarks: $receivable->remarks,
                    );

                    return $items;
                })(),
            );
            $this->journalEntryActions->create($journalEntryDTO);

            self::updateSummary($receivable);

            $this->flushCache();

            return $receivable;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(Receivable $receivable, ReceivableUpdateDTO $data): Receivable
    {
        $timer_start = microtime(true);

        try {
            $receivable->code = $this->generateUniqueCode($receivable->company_id, $data->code, $receivable->id);
            $receivable->date = $this->generateDate($data->date);
            $receivable->category_id = $data->categoryId;
            $receivable->customer_id = $data->customerId;
            $receivable->cash_account_id = $data->cashAccountId;
            $receivable->direct_amount_received = $data->directAmountReceived;
            $receivable->opening_amount_due = $data->openingAmountDue;
            $receivable->due_days = $data->dueDays;
            $receivable->remarks = $data->remarks;
            $receivable->save();

            $cashTransaction = $receivable->cashTransaction;
            if ($receivable->cash_account_id && $receivable->direct_amount_received > 0) {
                if (! $cashTransaction) {
                    $this->cashTransactionActions->create(
                        data: CashTransactionCreateDTO::fromReceivable($receivable)
                    );
                } else {
                    $this->cashTransactionActions->update(
                        cashTransaction: $cashTransaction,
                        data: CashTransactionUpdateDTO::fromReceivable($receivable)
                    );
                }
            } elseif ($cashTransaction) {
                $this->cashTransactionActions->delete($cashTransaction);
            }

            foreach ($data->deletePaymentIds as $deleteId) {
                $receivablePayment = $receivable->payments()->findOrFail($deleteId);
                $this->receivablePaymentActions->delete($receivablePayment, false);
            }

            foreach ($data->payments as $payment) {
                if (! empty($payment['id'])) {
                    $receivablePayment = $receivable->payments()->findOrFail($payment['id']);
                    $dto = new ReceivablePaymentUpdateDTO(
                        code: $payment['code'],
                        date: $payment['date'],
                        cashAccountId: $payment['cash_account_id'],
                        amount: $payment['amount'],
                        remarks: $payment['remarks'],
                    );
                    $this->receivablePaymentActions->update($receivablePayment, $dto, false);
                } else {
                    $dto = new ReceivablePaymentCreateDTO(
                        companyId: $receivable->company_id,
                        branchId: $receivable->branch_id,
                        code: $payment['code'],
                        date: $payment['date'],
                        receivableId: $receivable->id,
                        cashAccountId: $payment['cash_account_id'],
                        amount: $payment['amount'],
                        remarks: $payment['remarks'],
                    );
                    $this->receivablePaymentActions->create($dto, false);
                }
            }

            $journalEntry = $receivable->journalEntry;
            if (! $journalEntry) {
                $journalEntryDTO = new JournalEntryCreateDTO(
                    companyId: $receivable->company_id,
                    branchId: $receivable->branch_id,
                    code: config('dcslab.KEYWORDS.AUTO'),
                    date: $receivable->date,
                    sourceType: Receivable::class,
                    sourceId: $receivable->id,
                    referenceNo: $receivable->code,
                    remarks: $receivable->remarks,
                    items: (function () use ($receivable) {
                        $items = [];

                        if ((float) $receivable->direct_amount_received > 0) {
                            $items[] = new JournalEntryItemDTO(
                                chartOfAccountId: $receivable->cashAccount?->chartOfAccount?->id,
                                sequence: count($items) + 1,
                                debit: (float) $receivable->direct_amount_received,
                                credit: 0,
                                remarks: $receivable->remarks,
                            );
                        }

                        if ((float) $receivable->opening_amount_due > 0) {
                            $items[] = new JournalEntryItemDTO(
                                chartOfAccountId: $receivable->customer?->chartOfAccount?->id,
                                sequence: count($items) + 1,
                                debit: (float) $receivable->opening_amount_due,
                                credit: 0,
                                remarks: $receivable->remarks,
                            );
                        }

                        $items[] = new JournalEntryItemDTO(
                            chartOfAccountId: $receivable->company->equityCapitalOpeningCapitalChartOfAccount?->id,
                            sequence: count($items) + 1,
                            debit: 0,
                            credit: (float) $receivable->amount_total,
                            remarks: $receivable->remarks,
                        );

                        return $items;
                    })(),
                );
                $this->journalEntryActions->create($journalEntryDTO);
            } else {
                $journalEntryDTO = new JournalEntryUpdateDTO(
                    branchId: $receivable->branch_id,
                    code: $journalEntry->code,
                    date: $receivable->date,
                    referenceNo: $receivable->code,
                    remarks: $receivable->remarks,
                    items: (function () use ($receivable) {
                        $items = [];

                        if ((float) $receivable->direct_amount_received > 0) {
                            $items[] = new JournalEntryItemDTO(
                                chartOfAccountId: $receivable->cashAccount?->chartOfAccount?->id,
                                sequence: count($items) + 1,
                                debit: (float) $receivable->direct_amount_received,
                                credit: 0,
                                remarks: $receivable->remarks,
                            );
                        }

                        if ((float) $receivable->opening_amount_due > 0) {
                            $items[] = new JournalEntryItemDTO(
                                chartOfAccountId: $receivable->customer?->chartOfAccount?->id,
                                sequence: count($items) + 1,
                                debit: (float) $receivable->opening_amount_due,
                                credit: 0,
                                remarks: $receivable->remarks,
                            );
                        }

                        $items[] = new JournalEntryItemDTO(
                            chartOfAccountId: $receivable->company->equityCapitalOpeningCapitalChartOfAccount?->id,
                            sequence: count($items) + 1,
                            debit: 0,
                            credit: (float) $receivable->amount_total,
                            remarks: $receivable->remarks,
                        );

                        return $items;
                    })(),
                );
                $this->journalEntryActions->update($journalEntry, $journalEntryDTO);
            }

            self::updateSummary($receivable);

            $this->flushCache();

            return $receivable;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public static function updateSummary(Receivable $receivable): void
    {
        $receivable->refresh();

        $receivable->amount_total = (float) $receivable->direct_amount_received + (float) $receivable->opening_amount_due;
        $receivable->amount_paid_by_cash_account = (float) $receivable->payments()->sum('amount');
        // Stock-adjustment settlement link is not implemented yet in this batch.
        $receivable->amount_paid_by_stock_adjustment = 0;
        $receivable->amount_due = max(
            0,
            (float) $receivable->amount_total
            - (float) $receivable->amount_paid_by_cash_account
            - (float) $receivable->amount_paid_by_stock_adjustment
        );
        $receivable->is_paid_off = $receivable->amount_due == 0;
        $receivable->save();
    }

    public function delete(Receivable $receivable): bool
    {
        $timer_start = microtime(true);
        $retval = false;

        try {
            $cashTransaction = $receivable->cashTransaction;
            if ($cashTransaction) {
                $this->cashTransactionActions->delete($cashTransaction);
            }

            foreach ($receivable->payments as $payment) {
                $this->receivablePaymentActions->delete($payment, false);
            }

            $journalEntry = $receivable->journalEntry;
            if ($journalEntry) $this->journalEntryActions->delete($journalEntry);

            $retval = $receivable->delete();

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
