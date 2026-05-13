<?php

namespace App\Actions\Expense;

use App\Actions\CashTransaction\CashTransactionActions;
use App\Actions\ExpenseImage\ExpenseImageActions;
use App\Actions\ExpensePayment\ExpensePaymentActions;
use App\Actions\JournalEntry\JournalEntryActions;
use App\DTOs\CashTransactionCreateDTO;
use App\DTOs\CashTransactionUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExpenseCreateDTO;
use App\DTOs\ExpenseImageDTO;
use App\DTOs\ExpensePaymentCreateDTO;
use App\DTOs\ExpensePaymentUpdateDTO;
use App\DTOs\ExpenseUpdateDTO;
use App\DTOs\JournalEntryCreateDTO;
use App\DTOs\JournalEntryItemDTO;
use App\DTOs\JournalEntryUpdateDTO;
use App\Enums\JournalEntryTypeEnum;
use App\Helpers\TimezoneHelper;
use App\Models\Expense;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Config;

class ExpenseActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'category',
        'paidImmediatelyCashAccount',
        'mainImage',
    ];

    private const DETAIL_EAGER_LOADS = [
        'company',
        'branch',
        'category',
        'paidImmediatelyCashAccount',
        'images',
        'mainImage',
        'payments.cashAccount',
    ];

    public function __construct(
        private readonly CashTransactionActions $cashTransactionActions,
        private readonly ExpenseImageActions $expenseImageActions,
        private readonly ExpensePaymentActions $expensePaymentActions,
        private readonly JournalEntryActions $journalEntryActions,
    ) {
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,

        ?int $categoryId,
        ?bool $isAmountPayablePaidOff,
        ?int $includeId,

        ?ExecuteDTO $execute
    ) {
        $query = Expense::select('expenses.*');

        if ($execute?->pagination) {
            $query->with(self::DETAIL_EAGER_LOADS);
        } else {
            $query->with(self::LIST_EAGER_LOADS);
        }

        $query
            ->whereCompanyId('expenses', $companyId)
            ->whereBranchId('expenses', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $categoryId,
            $isAmountPayablePaidOff,
            $includeId,
        ) {
            $query->where(function ($query) use (
                $withTrashed,
                $search,
                $categoryId,
                $isAmountPayablePaidOff,
            ) {
                $query->withoutTrashed();
                if ($withTrashed) $query->withTrashed();

                if ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('expenses.code', 'like', '%'.$search.'%')
                            ->orWhere('expenses.remarks', 'like', '%'.$search.'%');
                    });
                }

                if (! is_null($categoryId)) {
                    $query->where('expenses.expense_category_id', $categoryId);
                }

                if (! is_null($isAmountPayablePaidOff)) {
                    $query->where('expenses.is_amount_payable_paid_off', $isAmountPayablePaidOff);
                }
            });

            if ($includeId) {
                $query->orWhere('expenses.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(expenses.id, '.$includeId.') desc');
        }
        $query->orderBy('expenses.date', 'desc');
        $query->orderBy('expenses.code', 'desc');

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
                    is_null($isAmountPayablePaidOff) ? '[null]' : ($isAmountPayablePaidOff ? 'true' : 'false'),
                    $includeId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_expense_'.implode('_', $cacheParams);

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

    public function read(Expense $expense): Expense
    {
        return $expense->load(self::DETAIL_EAGER_LOADS);
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
                $count = Expense::whereCompanyId('expenses', $companyId)->withTrashed()->count() + 1 + $tryCount;
                $code = 'EXP'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        }

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = Expense::whereCompanyId('expenses', $companyId)
            ->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    public function create(ExpenseCreateDTO $data): Expense
    {
        $timer_start = microtime(true);

        try {
            $expense = new Expense();
            $expense->company_id = $data->companyId;
            $expense->branch_id = $data->branchId;
            $expense->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $expense->date = $this->generateDate($data->date);
            $expense->expense_category_id = $data->expenseCategoryId;
            $expense->paid_immediately_cash_account_id = $data->paidImmediatelyCashAccountId;
            $expense->amount_paid_immediately = $data->amountPaidImmediately;
            $expense->amount_payable = $data->amountPayable;
            $expense->due_days = $data->dueDays;
            $expense->remarks = $data->remarks;
            $expense->save();

            if ($expense->paid_immediately_cash_account_id && $expense->amount_paid_immediately > 0) {
                $this->cashTransactionActions->create(
                    data: CashTransactionCreateDTO::fromExpense($expense)
                );
            }

            foreach ($data->payments as $payment) {
                $dto = new ExpensePaymentCreateDTO(
                    companyId: $expense->company_id,
                    branchId: $expense->branch_id,
                    code: $payment['code'],
                    date: $payment['date'],
                    expenseId: $expense->id,
                    cashAccountId: $payment['cash_account_id'],
                    amount: $payment['amount'],
                    remarks: $payment['remarks'],
                );
                $this->expensePaymentActions->create($dto, false);
            }

            foreach ($data->images as $image) {
                $expenseImageDTO = new ExpenseImageDTO(
                    hash: $image['hash'],
                    isMain: (bool) $image['is_main'],
                );

                $this->expenseImageActions->attachByHash($expense, $expenseImageDTO);
            }

            self::updateSummary($expense);

            $journalEntryDTO = new JournalEntryCreateDTO(
                companyId: $expense->company_id,
                branchId: $expense->branch_id,
                code: config('dcslab.KEYWORDS.AUTO'),
                date: $expense->date,
                journalType: JournalEntryTypeEnum::TRANSACTION->value,
                sourceType: Expense::class,
                sourceId: $expense->id,
                referenceNo: $expense->code,
                remarks: $expense->remarks,
                items: (function () use ($expense) {
                    $items = [];

                    $items[] = new JournalEntryItemDTO(
                        sequence: count($items) + 1,
                        chartOfAccountId: $expense->category?->chartOfAccount?->id,
                        debit: (float) $expense->amount_total,
                        credit: 0,
                        remarks: $expense->remarks,
                    );

                    if ((float) $expense->amount_paid_immediately > 0) {
                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $expense->paidImmediatelyCashAccount?->chartOfAccount?->id,
                            debit: 0,
                            credit: (float) $expense->amount_paid_immediately,
                            remarks: $expense->remarks,
                        );
                    }

                    if ((float) $expense->amount_payable > 0) {
                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $expense->company->liabilityAccountPayableChartOfAccount?->id,
                            debit: 0,
                            credit: (float) $expense->amount_payable,
                            remarks: $expense->remarks,
                        );
                    }

                    return $items;
                })(),
            );
            $this->journalEntryActions->create($journalEntryDTO);

            $journalEntryDTO = new JournalEntryCreateDTO(
                companyId: $expense->company_id,
                branchId: $expense->branch_id,
                code: config('dcslab.KEYWORDS.AUTO'),
                date: $expense->date,
                journalType: JournalEntryTypeEnum::CURRENT_MONTH_EARNINGS->value,
                sourceType: Expense::class,
                sourceId: $expense->id,
                referenceNo: $expense->code,
                remarks: $expense->remarks,
                items: (function () use ($expense) {
                    $items = [];

                    $items[] = new JournalEntryItemDTO(
                        sequence: count($items) + 1,
                        chartOfAccountId: $expense->company->equityCurrentMonthEarningsChartOfAccount?->id,
                        debit: (float) $expense->amount_total,
                        credit: 0,
                        remarks: $expense->remarks,
                    );

                    $items[] = new JournalEntryItemDTO(
                        sequence: count($items) + 1,
                        chartOfAccountId: $expense->company->systemSuspenseChartOfAccount?->id,
                        debit: 0,
                        credit: (float) $expense->amount_total,
                        remarks: $expense->remarks,
                    );

                    return $items;
                })(),
            );
            $this->journalEntryActions->create($journalEntryDTO);

            $journalEntryDTO = new JournalEntryCreateDTO(
                companyId: $expense->company_id,
                branchId: $expense->branch_id,
                code: config('dcslab.KEYWORDS.AUTO'),
                date: (function () use ($expense) {
                    return ($expense->date instanceof Carbon
                        ? $expense->date->copy()
                        : Carbon::parse((string) $expense->date))
                        ->endOfMonth()
                        ->format('Y-m-d H:i:s');
                })(),
                journalType: JournalEntryTypeEnum::MONTH_END_CLOSING->value,
                sourceType: Expense::class,
                sourceId: $expense->id,
                referenceNo: $expense->code,
                remarks: $expense->remarks,
                items: (function () use ($expense) {
                    $items = [];

                    $items[] = new JournalEntryItemDTO(
                        sequence: count($items) + 1,
                        chartOfAccountId: $expense->company->systemSuspenseChartOfAccount?->id,
                        debit: (float) $expense->amount_total,
                        credit: 0,
                        remarks: $expense->remarks,
                    );

                    $items[] = new JournalEntryItemDTO(
                        sequence: count($items) + 1,
                        chartOfAccountId: $expense->category?->chartOfAccount?->id,
                        debit: 0,
                        credit: (float) $expense->amount_total,
                        remarks: $expense->remarks,
                    );

                    return $items;
                })(),
            );
            $this->journalEntryActions->create($journalEntryDTO);

            $journalEntryDTO = new JournalEntryCreateDTO(
                companyId: $expense->company_id,
                branchId: $expense->branch_id,
                code: config('dcslab.KEYWORDS.AUTO'),
                date: (function () use ($expense) {
                    return ($expense->date instanceof Carbon
                        ? $expense->date->copy()
                        : Carbon::parse((string) $expense->date))
                        ->endOfMonth()
                        ->format('Y-m-d H:i:s');
                })(),
                journalType: JournalEntryTypeEnum::MONTH_TO_YEAR_CLOSING->value,
                sourceType: Expense::class,
                sourceId: $expense->id,
                referenceNo: $expense->code,
                remarks: $expense->remarks,
                items: (function () use ($expense) {
                    $items = [];

                    $items[] = new JournalEntryItemDTO(
                        sequence: count($items) + 1,
                        chartOfAccountId: $expense->company->equityCurrentYearEarningsChartOfAccount?->id,
                        debit: (float) $expense->amount_total,
                        credit: 0,
                        remarks: $expense->remarks,
                    );

                    $items[] = new JournalEntryItemDTO(
                        sequence: count($items) + 1,
                        chartOfAccountId: $expense->company->equityCurrentMonthEarningsChartOfAccount?->id,
                        debit: 0,
                        credit: (float) $expense->amount_total,
                        remarks: $expense->remarks,
                    );

                    return $items;
                })(),
            );
            $this->journalEntryActions->create($journalEntryDTO);

            $journalEntryDTO = new JournalEntryCreateDTO(
                companyId: $expense->company_id,
                branchId: $expense->branch_id,
                code: config('dcslab.KEYWORDS.AUTO'),
                date: (function () use ($expense) {
                    return ($expense->date instanceof Carbon
                        ? $expense->date->copy()
                        : Carbon::parse((string) $expense->date))
                        ->endOfYear()
                        ->format('Y-m-d H:i:s');
                })(),
                journalType: JournalEntryTypeEnum::YEAR_TO_RETAINED_EARNINGS_CLOSING->value,
                sourceType: Expense::class,
                sourceId: $expense->id,
                referenceNo: $expense->code,
                remarks: $expense->remarks,
                items: (function () use ($expense) {
                    $items = [];

                    $items[] = new JournalEntryItemDTO(
                        sequence: count($items) + 1,
                        chartOfAccountId: $expense->company->equityRetainedEarningsChartOfAccount?->id,
                        debit: (float) $expense->amount_total,
                        credit: 0,
                        remarks: $expense->remarks,
                    );

                    $items[] = new JournalEntryItemDTO(
                        sequence: count($items) + 1,
                        chartOfAccountId: $expense->company->equityCurrentYearEarningsChartOfAccount?->id,
                        debit: 0,
                        credit: (float) $expense->amount_total,
                        remarks: $expense->remarks,
                    );

                    return $items;
                })(),
            );
            $this->journalEntryActions->create($journalEntryDTO);

            $this->flushCache();

            return $expense;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(Expense $expense, ExpenseUpdateDTO $data): Expense
    {
        $timer_start = microtime(true);

        try {
            $expense->code = $this->generateUniqueCode($expense->company_id, $data->code, $expense->id);
            $expense->date = $this->generateDate($data->date);
            $expense->expense_category_id = $data->expenseCategoryId;
            $expense->paid_immediately_cash_account_id = $data->paidImmediatelyCashAccountId;
            $expense->amount_paid_immediately = $data->amountPaidImmediately;
            $expense->amount_payable = $data->amountPayable;
            $expense->due_days = $data->dueDays;
            $expense->remarks = $data->remarks;
            $expense->save();

            $cashTransaction = $expense->cashTransaction;
            if ($expense->paid_immediately_cash_account_id && $expense->amount_paid_immediately > 0) {
                if (! $cashTransaction) {
                    $this->cashTransactionActions->create(
                        data: CashTransactionCreateDTO::fromExpense($expense)
                    );
                } else {
                    $this->cashTransactionActions->update(
                        cashTransaction: $cashTransaction,
                        data: CashTransactionUpdateDTO::fromExpense($expense)
                    );
                }
            } elseif ($cashTransaction) {
                $this->cashTransactionActions->delete($cashTransaction);
            }

            foreach ($data->deletePaymentIds as $deleteId) {
                $expensePayment = $expense->payments()->findOrFail($deleteId);
                $this->expensePaymentActions->delete($expensePayment, false);
            }

            foreach ($data->deleteImageIds as $deleteId) {
                $this->expenseImageActions->detachById($expense, $deleteId);
            }

            foreach ($data->payments as $payment) {
                if (! empty($payment['id'])) {
                    $expensePayment = $expense->payments()->findOrFail($payment['id']);
                    $dto = new ExpensePaymentUpdateDTO(
                        code: $payment['code'],
                        date: $payment['date'],
                        cashAccountId: $payment['cash_account_id'],
                        amount: $payment['amount'],
                        remarks: $payment['remarks'],
                    );
                    $this->expensePaymentActions->update($expensePayment, $dto, false);
                } else {
                    $dto = new ExpensePaymentCreateDTO(
                        companyId: $expense->company_id,
                        branchId: $expense->branch_id,
                        code: $payment['code'],
                        date: $payment['date'],
                        expenseId: $expense->id,
                        cashAccountId: $payment['cash_account_id'],
                        amount: $payment['amount'],
                        remarks: $payment['remarks'],
                    );
                    $this->expensePaymentActions->create($dto, false);
                }
            }

            foreach ($data->images as $image) {
                $expenseImageDTO = new ExpenseImageDTO(
                    hash: $image['hash'],
                    isMain: (bool) $image['is_main'],
                );

                $this->expenseImageActions->attachByHash($expense, $expenseImageDTO);
            }

            self::updateSummary($expense);

            $journalEntry = $expense->journalEntry;
            if (! $journalEntry) {
                $journalEntryDTO = new JournalEntryCreateDTO(
                    companyId: $expense->company_id,
                    branchId: $expense->branch_id,
                    code: config('dcslab.KEYWORDS.AUTO'),
                    date: $expense->date,
                    journalType: JournalEntryTypeEnum::TRANSACTION->value,
                    sourceType: Expense::class,
                    sourceId: $expense->id,
                    referenceNo: $expense->code,
                    remarks: $expense->remarks,
                    items: (function () use ($expense) {
                        $items = [];

                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $expense->category?->chartOfAccount?->id,
                            debit: (float) $expense->amount_total,
                            credit: 0,
                            remarks: $expense->remarks,
                        );

                        if ((float) $expense->amount_paid_immediately > 0) {
                            $items[] = new JournalEntryItemDTO(
                                sequence: count($items) + 1,
                                chartOfAccountId: $expense->paidImmediatelyCashAccount?->chartOfAccount?->id,
                                debit: 0,
                                credit: (float) $expense->amount_paid_immediately,
                                remarks: $expense->remarks,
                            );
                        }

                        if ((float) $expense->amount_payable > 0) {
                            $items[] = new JournalEntryItemDTO(
                                sequence: count($items) + 1,
                                chartOfAccountId: $expense->company->liabilityAccountPayableChartOfAccount?->id,
                                debit: 0,
                                credit: (float) $expense->amount_payable,
                                remarks: $expense->remarks,
                            );
                        }

                        return $items;
                    })(),
                );
                $this->journalEntryActions->create($journalEntryDTO);
            } else {
                $journalEntryDTO = new JournalEntryUpdateDTO(
                    branchId: $expense->branch_id,
                    code: $journalEntry->code,
                    date: $expense->date,
                    journalType: JournalEntryTypeEnum::TRANSACTION->value,
                    referenceNo: $expense->code,
                    remarks: $expense->remarks,
                    items: (function () use ($expense) {
                        $items = [];

                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $expense->category?->chartOfAccount?->id,
                            debit: (float) $expense->amount_total,
                            credit: 0,
                            remarks: $expense->remarks,
                        );

                        if ((float) $expense->amount_paid_immediately > 0) {
                            $items[] = new JournalEntryItemDTO(
                                sequence: count($items) + 1,
                                chartOfAccountId: $expense->paidImmediatelyCashAccount?->chartOfAccount?->id,
                                debit: 0,
                                credit: (float) $expense->amount_paid_immediately,
                                remarks: $expense->remarks,
                            );
                        }

                        if ((float) $expense->amount_payable > 0) {
                            $items[] = new JournalEntryItemDTO(
                                sequence: count($items) + 1,
                                chartOfAccountId: $expense->company->liabilityAccountPayableChartOfAccount?->id,
                                debit: 0,
                                credit: (float) $expense->amount_payable,
                                remarks: $expense->remarks,
                            );
                        }

                        return $items;
                    })(),
                );
                $this->journalEntryActions->update($journalEntry, $journalEntryDTO);
            }

            $currentMonthEarningsJournalEntry = $expense->currentMonthEarningsJournalEntry;
            if (! $currentMonthEarningsJournalEntry) {
                $journalEntryDTO = new JournalEntryCreateDTO(
                    companyId: $expense->company_id,
                    branchId: $expense->branch_id,
                    code: config('dcslab.KEYWORDS.AUTO'),
                    date: $expense->date,
                    journalType: JournalEntryTypeEnum::CURRENT_MONTH_EARNINGS->value,
                    sourceType: Expense::class,
                    sourceId: $expense->id,
                    referenceNo: $expense->code,
                    remarks: $expense->remarks,
                    items: (function () use ($expense) {
                        $items = [];

                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $expense->company->equityCurrentMonthEarningsChartOfAccount?->id,
                            debit: (float) $expense->amount_total,
                            credit: 0,
                            remarks: $expense->remarks,
                        );

                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $expense->company->systemSuspenseChartOfAccount?->id,
                            debit: 0,
                            credit: (float) $expense->amount_total,
                            remarks: $expense->remarks,
                        );

                        return $items;
                    })(),
                );
                $this->journalEntryActions->create($journalEntryDTO);
            } else {
                $journalEntryDTO = new JournalEntryUpdateDTO(
                    branchId: $expense->branch_id,
                    code: $currentMonthEarningsJournalEntry->code,
                    date: $expense->date,
                    journalType: JournalEntryTypeEnum::CURRENT_MONTH_EARNINGS->value,
                    referenceNo: $expense->code,
                    remarks: $expense->remarks,
                    items: (function () use ($expense) {
                        $items = [];

                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $expense->company->equityCurrentMonthEarningsChartOfAccount?->id,
                            debit: (float) $expense->amount_total,
                            credit: 0,
                            remarks: $expense->remarks,
                        );

                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $expense->company->systemSuspenseChartOfAccount?->id,
                            debit: 0,
                            credit: (float) $expense->amount_total,
                            remarks: $expense->remarks,
                        );

                        return $items;
                    })(),
                );
                $this->journalEntryActions->update($currentMonthEarningsJournalEntry, $journalEntryDTO);
            }

            $monthEndClosingJournalEntry = $expense->monthEndClosingJournalEntry;
            if (! $monthEndClosingJournalEntry) {
                $journalEntryDTO = new JournalEntryCreateDTO(
                    companyId: $expense->company_id,
                    branchId: $expense->branch_id,
                    code: config('dcslab.KEYWORDS.AUTO'),
                    date: (function () use ($expense) {
                        return ($expense->date instanceof Carbon
                            ? $expense->date->copy()
                            : Carbon::parse((string) $expense->date))
                            ->endOfMonth()
                            ->format('Y-m-d H:i:s');
                    })(),
                    journalType: JournalEntryTypeEnum::MONTH_END_CLOSING->value,
                    sourceType: Expense::class,
                    sourceId: $expense->id,
                    referenceNo: $expense->code,
                    remarks: $expense->remarks,
                    items: (function () use ($expense) {
                        $items = [];

                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $expense->company->systemSuspenseChartOfAccount?->id,
                            debit: (float) $expense->amount_total,
                            credit: 0,
                            remarks: $expense->remarks,
                        );

                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $expense->category?->chartOfAccount?->id,
                            debit: 0,
                            credit: (float) $expense->amount_total,
                            remarks: $expense->remarks,
                        );

                        return $items;
                    })(),
                );
                $this->journalEntryActions->create($journalEntryDTO);
            } else {
                $journalEntryDTO = new JournalEntryUpdateDTO(
                    branchId: $expense->branch_id,
                    code: $monthEndClosingJournalEntry->code,
                    date: (function () use ($expense) {
                        return ($expense->date instanceof Carbon
                            ? $expense->date->copy()
                            : Carbon::parse((string) $expense->date))
                            ->endOfMonth()
                            ->format('Y-m-d H:i:s');
                    })(),
                    journalType: JournalEntryTypeEnum::MONTH_END_CLOSING->value,
                    referenceNo: $expense->code,
                    remarks: $expense->remarks,
                    items: (function () use ($expense) {
                        $items = [];

                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $expense->company->systemSuspenseChartOfAccount?->id,
                            debit: (float) $expense->amount_total,
                            credit: 0,
                            remarks: $expense->remarks,
                        );

                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $expense->category?->chartOfAccount?->id,
                            debit: 0,
                            credit: (float) $expense->amount_total,
                            remarks: $expense->remarks,
                        );

                        return $items;
                    })(),
                );
                $this->journalEntryActions->update($monthEndClosingJournalEntry, $journalEntryDTO);
            }

            $monthToYearClosingJournalEntry = $expense->monthToYearClosingJournalEntry;
            if (! $monthToYearClosingJournalEntry) {
                $journalEntryDTO = new JournalEntryCreateDTO(
                    companyId: $expense->company_id,
                    branchId: $expense->branch_id,
                    code: config('dcslab.KEYWORDS.AUTO'),
                    date: (function () use ($expense) {
                        return ($expense->date instanceof Carbon
                            ? $expense->date->copy()
                            : Carbon::parse((string) $expense->date))
                            ->endOfMonth()
                            ->format('Y-m-d H:i:s');
                    })(),
                    journalType: JournalEntryTypeEnum::MONTH_TO_YEAR_CLOSING->value,
                    sourceType: Expense::class,
                    sourceId: $expense->id,
                    referenceNo: $expense->code,
                    remarks: $expense->remarks,
                    items: (function () use ($expense) {
                        $items = [];

                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $expense->company->equityCurrentYearEarningsChartOfAccount?->id,
                            debit: (float) $expense->amount_total,
                            credit: 0,
                            remarks: $expense->remarks,
                        );

                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $expense->company->equityCurrentMonthEarningsChartOfAccount?->id,
                            debit: 0,
                            credit: (float) $expense->amount_total,
                            remarks: $expense->remarks,
                        );

                        return $items;
                    })(),
                );
                $this->journalEntryActions->create($journalEntryDTO);
            } else {
                $journalEntryDTO = new JournalEntryUpdateDTO(
                    branchId: $expense->branch_id,
                    code: $monthToYearClosingJournalEntry->code,
                    date: (function () use ($expense) {
                        return ($expense->date instanceof Carbon
                            ? $expense->date->copy()
                            : Carbon::parse((string) $expense->date))
                            ->endOfMonth()
                            ->format('Y-m-d H:i:s');
                    })(),
                    journalType: JournalEntryTypeEnum::MONTH_TO_YEAR_CLOSING->value,
                    referenceNo: $expense->code,
                    remarks: $expense->remarks,
                    items: (function () use ($expense) {
                        $items = [];

                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $expense->company->equityCurrentYearEarningsChartOfAccount?->id,
                            debit: (float) $expense->amount_total,
                            credit: 0,
                            remarks: $expense->remarks,
                        );

                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $expense->company->equityCurrentMonthEarningsChartOfAccount?->id,
                            debit: 0,
                            credit: (float) $expense->amount_total,
                            remarks: $expense->remarks,
                        );

                        return $items;
                    })(),
                );
                $this->journalEntryActions->update($monthToYearClosingJournalEntry, $journalEntryDTO);
            }

            $yearToRetainedEarningsClosingJournalEntry = $expense->yearToRetainedEarningsClosingJournalEntry;
            if (! $yearToRetainedEarningsClosingJournalEntry) {
                $journalEntryDTO = new JournalEntryCreateDTO(
                    companyId: $expense->company_id,
                    branchId: $expense->branch_id,
                    code: config('dcslab.KEYWORDS.AUTO'),
                    date: (function () use ($expense) {
                        return ($expense->date instanceof Carbon
                            ? $expense->date->copy()
                            : Carbon::parse((string) $expense->date))
                            ->endOfYear()
                            ->format('Y-m-d H:i:s');
                    })(),
                    journalType: JournalEntryTypeEnum::YEAR_TO_RETAINED_EARNINGS_CLOSING->value,
                    sourceType: Expense::class,
                    sourceId: $expense->id,
                    referenceNo: $expense->code,
                    remarks: $expense->remarks,
                    items: (function () use ($expense) {
                        $items = [];

                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $expense->company->equityRetainedEarningsChartOfAccount?->id,
                            debit: (float) $expense->amount_total,
                            credit: 0,
                            remarks: $expense->remarks,
                        );

                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $expense->company->equityCurrentYearEarningsChartOfAccount?->id,
                            debit: 0,
                            credit: (float) $expense->amount_total,
                            remarks: $expense->remarks,
                        );

                        return $items;
                    })(),
                );
                $this->journalEntryActions->create($journalEntryDTO);
            } else {
                $journalEntryDTO = new JournalEntryUpdateDTO(
                    branchId: $expense->branch_id,
                    code: $yearToRetainedEarningsClosingJournalEntry->code,
                    date: (function () use ($expense) {
                        return ($expense->date instanceof Carbon
                            ? $expense->date->copy()
                            : Carbon::parse((string) $expense->date))
                            ->endOfYear()
                            ->format('Y-m-d H:i:s');
                    })(),
                    journalType: JournalEntryTypeEnum::YEAR_TO_RETAINED_EARNINGS_CLOSING->value,
                    referenceNo: $expense->code,
                    remarks: $expense->remarks,
                    items: (function () use ($expense) {
                        $items = [];

                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $expense->company->equityRetainedEarningsChartOfAccount?->id,
                            debit: (float) $expense->amount_total,
                            credit: 0,
                            remarks: $expense->remarks,
                        );

                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $expense->company->equityCurrentYearEarningsChartOfAccount?->id,
                            debit: 0,
                            credit: (float) $expense->amount_total,
                            remarks: $expense->remarks,
                        );

                        return $items;
                    })(),
                );
                $this->journalEntryActions->update($yearToRetainedEarningsClosingJournalEntry, $journalEntryDTO);
            }

            $this->flushCache();

            return $expense;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public static function updateSummary(Expense $expense): void
    {
        $expense->refresh();

        $expense->amount_payable_paid = (float) $expense->payments()->sum('amount');
        $expense->amount_payable_due = max(0, $expense->amount_payable - $expense->amount_payable_paid);
        $expense->is_amount_payable_paid_off = $expense->amount_payable_due == 0;
        $expense->amount_total = (float) ($expense->amount_paid_immediately + $expense->amount_payable);
        $expense->save();
    }

    public function delete(Expense $expense): bool
    {
        $timer_start = microtime(true);
        $retval = false;

        try {
            foreach ($expense->payments as $payment) {
                $this->expensePaymentActions->delete($payment, false);
            }

            $cashTransaction = $expense->cashTransaction;
            if ($cashTransaction) {
                $this->cashTransactionActions->delete($cashTransaction);
            }

            $journalEntry = $expense->journalEntry;
            if ($journalEntry) $this->journalEntryActions->delete($journalEntry);

            $currentMonthEarningsJournalEntry = $expense->currentMonthEarningsJournalEntry;
            if ($currentMonthEarningsJournalEntry) $this->journalEntryActions->delete($currentMonthEarningsJournalEntry);

            $monthEndClosingJournalEntry = $expense->monthEndClosingJournalEntry;
            if ($monthEndClosingJournalEntry) $this->journalEntryActions->delete($monthEndClosingJournalEntry);

            $monthToYearClosingJournalEntry = $expense->monthToYearClosingJournalEntry;
            if ($monthToYearClosingJournalEntry) $this->journalEntryActions->delete($monthToYearClosingJournalEntry);

            $yearToRetainedEarningsClosingJournalEntry = $expense->yearToRetainedEarningsJournalEntry;
            if ($yearToRetainedEarningsClosingJournalEntry) $this->journalEntryActions->delete($yearToRetainedEarningsClosingJournalEntry);

            $retval = $expense->delete();

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
