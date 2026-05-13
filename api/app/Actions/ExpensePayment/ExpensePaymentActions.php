<?php

namespace App\Actions\ExpensePayment;

use App\Actions\CashTransaction\CashTransactionActions;
use App\Actions\Expense\ExpenseActions;
use App\Actions\JournalEntry\JournalEntryActions;
use App\DTOs\CashTransactionCreateDTO;
use App\DTOs\CashTransactionUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExpensePaymentCreateDTO;
use App\DTOs\ExpensePaymentUpdateDTO;
use App\DTOs\JournalEntryCreateDTO;
use App\DTOs\JournalEntryItemDTO;
use App\DTOs\JournalEntryUpdateDTO;
use App\Helpers\TimezoneHelper;
use App\Models\ExpensePayment;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class ExpensePaymentActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'expense.category',
        'cashAccount',
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

        ?int $expenseId,
        ?int $includeId,

        ?ExecuteDTO $execute
    ) {
        $query = ExpensePayment::with(self::LIST_EAGER_LOADS)
            ->select('expense_payments.*')
            ->whereCompanyId('expense_payments', $companyId)
            ->whereBranchId('expense_payments', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $expenseId,
            $includeId,
        ) {
            $query->where(function ($query) use (
                $withTrashed,
                $search,
                $expenseId,
            ) {
                $query->withoutTrashed();
                if ($withTrashed) $query->withTrashed();

                if ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('expense_payments.code', 'like', '%'.$search.'%')
                            ->orWhere('expense_payments.remarks', 'like', '%'.$search.'%');
                    });
                }

                if (! is_null($expenseId)) {
                    $query->where('expense_payments.expense_id', $expenseId);
                }
            });

            if ($includeId) {
                $query->orWhere('expense_payments.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(expense_payments.id, '.$includeId.') desc');
        }
        $query->orderBy('expense_payments.date', 'desc');
        $query->orderBy('expense_payments.code', 'desc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    $expenseId ?? '[null]',
                    $includeId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_expense_payment_'.implode('_', $cacheParams);

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

    public function read(ExpensePayment $expensePayment): ExpensePayment
    {
        return $expensePayment->load(self::LIST_EAGER_LOADS);
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
                $count = ExpensePayment::whereCompanyId('expense_payments', $companyId)->withTrashed()->count() + 1 + $tryCount;
                $code = 'EXPP'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        }

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = ExpensePayment::whereCompanyId('expense_payments', $companyId)
            ->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    public function create(ExpensePaymentCreateDTO $data, bool $updateParentSummary = true): ExpensePayment
    {
        $timer_start = microtime(true);

        try {
            $expensePayment = new ExpensePayment();
            $expensePayment->company_id = $data->companyId;
            $expensePayment->branch_id = $data->branchId;
            $expensePayment->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $expensePayment->date = $this->generateDate($data->date);
            $expensePayment->expense_id = $data->expenseId;
            $expensePayment->cash_account_id = $data->cashAccountId;
            $expensePayment->amount = $data->amount;
            $expensePayment->remarks = $data->remarks;
            $expensePayment->save();

            $this->cashTransactionActions->create(
                data: CashTransactionCreateDTO::fromExpensePayment($expensePayment)
            );

            $journalEntryDTO = new JournalEntryCreateDTO(
                companyId: $expensePayment->company_id,
                branchId: $expensePayment->branch_id,
                code: config('dcslab.KEYWORDS.AUTO'),
                date: $expensePayment->date,
                sourceType: ExpensePayment::class,
                sourceId: $expensePayment->id,
                referenceNo: $expensePayment->code,
                remarks: $expensePayment->remarks,
                items: (function () use ($expensePayment) {
                    $items = [];

                    $items[] = new JournalEntryItemDTO(
                        sequence: count($items) + 1,
                        chartOfAccountId: $expensePayment->company->liabilityAccountPayableChartOfAccount?->id,
                        debit: (float) $expensePayment->amount,
                        credit: 0,
                        remarks: $expensePayment->remarks,
                    );

                    $items[] = new JournalEntryItemDTO(
                        sequence: count($items) + 1,
                        chartOfAccountId: $expensePayment->cashAccount?->chartOfAccount?->id,
                        debit: 0,
                        credit: (float) $expensePayment->amount,
                        remarks: $expensePayment->remarks,
                    );

                    return $items;
                })(),
            );
            $this->journalEntryActions->create($journalEntryDTO);

            if ($updateParentSummary) {
                ExpenseActions::updateSummary($expensePayment->expense);
                $expensePayment->refresh();
            }

            $this->flushCache();

            return $expensePayment;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(ExpensePayment $expensePayment, ExpensePaymentUpdateDTO $data, bool $updateParentSummary = true): ExpensePayment
    {
        $timer_start = microtime(true);

        try {
            $expensePayment->code = $this->generateUniqueCode($expensePayment->company_id, $data->code, $expensePayment->id);
            $expensePayment->date = $this->generateDate($data->date);
            $expensePayment->cash_account_id = $data->cashAccountId;
            $expensePayment->amount = $data->amount;
            $expensePayment->remarks = $data->remarks;
            $expensePayment->save();

            $cashTransaction = $expensePayment->cashTransaction;
            if (! $cashTransaction) {
                $this->cashTransactionActions->create(
                    data: CashTransactionCreateDTO::fromExpensePayment($expensePayment)
                );
            } else {
                $this->cashTransactionActions->update(
                    cashTransaction: $cashTransaction,
                    data: CashTransactionUpdateDTO::fromExpensePayment($expensePayment)
                );
            }

            $journalEntry = $expensePayment->journalEntry;
            if (! $journalEntry) {
                $journalEntryDTO = new JournalEntryCreateDTO(
                    companyId: $expensePayment->company_id,
                    branchId: $expensePayment->branch_id,
                    code: config('dcslab.KEYWORDS.AUTO'),
                    date: $expensePayment->date,
                    sourceType: ExpensePayment::class,
                    sourceId: $expensePayment->id,
                    referenceNo: $expensePayment->code,
                    remarks: $expensePayment->remarks,
                    items: (function () use ($expensePayment) {
                        $items = [];

                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $expensePayment->company->liabilityAccountPayableChartOfAccount?->id,
                            debit: (float) $expensePayment->amount,
                            credit: 0,
                            remarks: $expensePayment->remarks,
                        );

                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $expensePayment->cashAccount?->chartOfAccount?->id,
                            debit: 0,
                            credit: (float) $expensePayment->amount,
                            remarks: $expensePayment->remarks,
                        );

                        return $items;
                    })(),
                );
                $this->journalEntryActions->create($journalEntryDTO);
            } else {
                $journalEntryDTO = new JournalEntryUpdateDTO(
                    branchId: $expensePayment->branch_id,
                    code: $journalEntry->code,
                    date: $expensePayment->date,
                    referenceNo: $expensePayment->code,
                    remarks: $expensePayment->remarks,
                    items: (function () use ($expensePayment) {
                        $items = [];

                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $expensePayment->company->liabilityAccountPayableChartOfAccount?->id,
                            debit: (float) $expensePayment->amount,
                            credit: 0,
                            remarks: $expensePayment->remarks,
                        );

                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $expensePayment->cashAccount?->chartOfAccount?->id,
                            debit: 0,
                            credit: (float) $expensePayment->amount,
                            remarks: $expensePayment->remarks,
                        );

                        return $items;
                    })(),
                );
                $this->journalEntryActions->update($journalEntry, $journalEntryDTO);
            }

            if ($updateParentSummary) {
                ExpenseActions::updateSummary($expensePayment->expense);
                $expensePayment->refresh();
            }

            $this->flushCache();

            return $expensePayment;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(
        ExpensePayment $expensePayment,
        bool $updateParentSummary = true,
    ): bool {
        $timer_start = microtime(true);
        $retval = false;

        try {
            $cashTransaction = $expensePayment->cashTransaction;
            if ($cashTransaction) {
                $this->cashTransactionActions->delete($cashTransaction);
            }

            $journalEntry = $expensePayment->journalEntry;
            if ($journalEntry) $this->journalEntryActions->delete($journalEntry);

            $retval = $expensePayment->delete();

            if ($updateParentSummary) {
                ExpenseActions::updateSummary($expensePayment->expense);
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
