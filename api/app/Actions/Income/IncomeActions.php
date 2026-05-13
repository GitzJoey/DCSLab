<?php

namespace App\Actions\Income;

use App\Actions\CashTransaction\CashTransactionActions;
use App\Actions\IncomeImage\IncomeImageActions;
use App\Actions\IncomePayment\IncomePaymentActions;
use App\Actions\JournalEntry\JournalEntryActions;
use App\DTOs\CashTransactionCreateDTO;
use App\DTOs\CashTransactionUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\DTOs\IncomeCreateDTO;
use App\DTOs\IncomeImageDTO;
use App\DTOs\IncomePaymentCreateDTO;
use App\DTOs\IncomePaymentUpdateDTO;
use App\DTOs\IncomeUpdateDTO;
use App\DTOs\JournalEntryCreateDTO;
use App\DTOs\JournalEntryItemDTO;
use App\DTOs\JournalEntryUpdateDTO;
use App\Helpers\TimezoneHelper;
use App\Models\Income;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class IncomeActions
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
        private readonly IncomeImageActions $incomeImageActions,
        private readonly IncomePaymentActions $incomePaymentActions,
        private readonly JournalEntryActions $journalEntryActions,
    ) {
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,

        ?int $categoryId,
        ?bool $isAmountReceivablePaidOff,
        ?int $includeId,

        ?ExecuteDTO $execute
    ) {
        $query = Income::select('incomes.*');

        if ($execute?->pagination) {
            $query->with(self::DETAIL_EAGER_LOADS);
        } else {
            $query->with(self::LIST_EAGER_LOADS);
        }

        $query
            ->whereCompanyId('incomes', $companyId)
            ->whereBranchId('incomes', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $categoryId,
            $isAmountReceivablePaidOff,
            $includeId,
        ) {
            $query->where(function ($query) use (
                $withTrashed,
                $search,
                $categoryId,
                $isAmountReceivablePaidOff,
            ) {
                $query->withoutTrashed();
                if ($withTrashed) $query->withTrashed();

                if ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('incomes.code', 'like', '%'.$search.'%')
                            ->orWhere('incomes.remarks', 'like', '%'.$search.'%');
                    });
                }

                if (! is_null($categoryId)) {
                    $query->where('incomes.income_category_id', $categoryId);
                }

                if (! is_null($isAmountReceivablePaidOff)) {
                    $query->where('incomes.is_amount_receivable_paid_off', $isAmountReceivablePaidOff);
                }
            });

            if ($includeId) {
                $query->orWhere('incomes.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(incomes.id, '.$includeId.') desc');
        }
        $query->orderBy('incomes.date', 'desc');
        $query->orderBy('incomes.code', 'desc');

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
                    is_null($isAmountReceivablePaidOff) ? '[null]' : ($isAmountReceivablePaidOff ? 'true' : 'false'),
                    $includeId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_income_'.implode('_', $cacheParams);

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

    public function read(Income $income): Income
    {
        return $income->load(self::DETAIL_EAGER_LOADS);
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
                $count = Income::whereCompanyId('incomes', $companyId)->withTrashed()->count() + 1 + $tryCount;
                $code = 'INC'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        }

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = Income::whereCompanyId('incomes', $companyId)
            ->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    public function create(IncomeCreateDTO $data): Income
    {
        $timer_start = microtime(true);

        try {
            $income = new Income();
            $income->company_id = $data->companyId;
            $income->branch_id = $data->branchId;
            $income->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $income->date = $this->generateDate($data->date);
            $income->income_category_id = $data->incomeCategoryId;
            $income->paid_immediately_cash_account_id = $data->paidImmediatelyCashAccountId;
            $income->amount_paid_immediately = $data->amountPaidImmediately;
            $income->amount_receivable = $data->amountReceivable;
            $income->due_days = $data->dueDays;
            $income->remarks = $data->remarks;
            $income->save();

            if ($income->paid_immediately_cash_account_id && $income->amount_paid_immediately > 0) {
                $this->cashTransactionActions->create(
                    data: CashTransactionCreateDTO::fromIncome($income)
                );
            }

            foreach ($data->payments as $payment) {
                $dto = new IncomePaymentCreateDTO(
                    companyId: $income->company_id,
                    branchId: $income->branch_id,
                    code: $payment['code'],
                    date: $payment['date'],
                    incomeId: $income->id,
                    cashAccountId: $payment['cash_account_id'],
                    amount: $payment['amount'],
                    remarks: $payment['remarks'],
                );
                $this->incomePaymentActions->create($dto, false);
            }

            foreach ($data->images as $image) {
                $incomeImageDTO = new IncomeImageDTO(
                    hash: $image['hash'],
                    isMain: (bool) $image['is_main'],
                );

                $this->incomeImageActions->attachByHash($income, $incomeImageDTO);
            }

            $journalEntryDTO = new JournalEntryCreateDTO(
                companyId: $income->company_id,
                branchId: $income->branch_id,
                code: config('dcslab.KEYWORDS.AUTO'),
                date: $income->date,
                sourceType: Income::class,
                sourceId: $income->id,
                referenceNo: $income->code,
                remarks: $income->remarks,
                items: (function () use ($income) {
                    $items = [];

                    if ((float) $income->amount_paid_immediately > 0) {
                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $income->paidImmediatelyCashAccount?->chartOfAccount?->id,
                            debit: (float) $income->amount_paid_immediately,
                            credit: 0,
                            remarks: $income->remarks,
                        );
                    }

                    if ((float) $income->amount_receivable > 0) {
                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $income->company->assetCurrentAccountReceivableChartOfAccount,
                            debit: (float) $income->amount_receivable,
                            credit: 0,
                            remarks: $income->remarks,
                        );
                    }

                    $items[] = new JournalEntryItemDTO(
                        sequence: count($items) + 1,
                        chartOfAccountId: $income->category?->chartOfAccount?->id,
                        debit: 0,
                        credit: (float) $income->amount_total,
                        remarks: $income->remarks,
                    );

                    return $items;
                })(),
            );
            $this->journalEntryActions->create($journalEntryDTO);

            self::updateSummary($income);

            $this->flushCache();

            return $income;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(Income $income, IncomeUpdateDTO $data): Income
    {
        $timer_start = microtime(true);

        try {
            $income->code = $this->generateUniqueCode($income->company_id, $data->code, $income->id);
            $income->date = $this->generateDate($data->date);
            $income->income_category_id = $data->incomeCategoryId;
            $income->paid_immediately_cash_account_id = $data->paidImmediatelyCashAccountId;
            $income->amount_paid_immediately = $data->amountPaidImmediately;
            $income->amount_receivable = $data->amountReceivable;
            $income->due_days = $data->dueDays;
            $income->remarks = $data->remarks;
            $income->save();

            $cashTransaction = $income->cashTransaction;
            if ($income->paid_immediately_cash_account_id && $income->amount_paid_immediately > 0) {
                if (! $cashTransaction) {
                    $this->cashTransactionActions->create(
                        data: CashTransactionCreateDTO::fromIncome($income)
                    );
                } else {
                    $this->cashTransactionActions->update(
                        cashTransaction: $cashTransaction,
                        data: CashTransactionUpdateDTO::fromIncome($income)
                    );
                }
            } elseif ($cashTransaction) {
                $this->cashTransactionActions->delete($cashTransaction);
            }

            foreach ($data->deletePaymentIds as $deleteId) {
                $incomePayment = $income->payments()->findOrFail($deleteId);
                $this->incomePaymentActions->delete($incomePayment, false);
            }

            foreach ($data->deleteImageIds as $deleteId) {
                $this->incomeImageActions->detachById($income, $deleteId);
            }

            foreach ($data->payments as $payment) {
                if (! empty($payment['id'])) {
                    $incomePayment = $income->payments()->findOrFail($payment['id']);
                    $dto = new IncomePaymentUpdateDTO(
                        code: $payment['code'],
                        date: $payment['date'],
                        cashAccountId: $payment['cash_account_id'],
                        amount: $payment['amount'],
                        remarks: $payment['remarks'],
                    );
                    $this->incomePaymentActions->update($incomePayment, $dto, false);
                } else {
                    $dto = new IncomePaymentCreateDTO(
                        companyId: $income->company_id,
                        branchId: $income->branch_id,
                        code: $payment['code'],
                        date: $payment['date'],
                        incomeId: $income->id,
                        cashAccountId: $payment['cash_account_id'],
                        amount: $payment['amount'],
                        remarks: $payment['remarks'],
                    );
                    $this->incomePaymentActions->create($dto, false);
                }
            }

            foreach ($data->images as $image) {
                $incomeImageDTO = new IncomeImageDTO(
                    hash: $image['hash'],
                    isMain: (bool) $image['is_main'],
                );

                $this->incomeImageActions->attachByHash($income, $incomeImageDTO);
            }

            $journalEntry = $income->journalEntry;
            if (! $journalEntry) {
                $journalEntryDTO = new JournalEntryCreateDTO(
                    companyId: $income->company_id,
                    branchId: $income->branch_id,
                    code: config('dcslab.KEYWORDS.AUTO'),
                    date: $income->date,
                    sourceType: Income::class,
                    sourceId: $income->id,
                    referenceNo: $income->code,
                    remarks: $income->remarks,
                    items: (function () use ($income) {
                        $items = [];

                        if ((float) $income->amount_paid_immediately > 0) {
                            $items[] = new JournalEntryItemDTO(
                                sequence: count($items) + 1,
                                chartOfAccountId: $income->paidImmediatelyCashAccount?->chartOfAccount?->id,
                                debit: (float) $income->amount_paid_immediately,
                                credit: 0,
                                remarks: $income->remarks,
                            );
                        }

                        if ((float) $income->amount_receivable > 0) {
                            $items[] = new JournalEntryItemDTO(
                                sequence: count($items) + 1,
                                chartOfAccountId: $income->company->assetCurrentAccountReceivableChartOfAccount?->id,
                                debit: (float) $income->amount_receivable,
                                credit: 0,
                                remarks: $income->remarks,
                            );
                        }

                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $income->category?->chartOfAccount?->id,
                            debit: 0,
                            credit: (float) $income->amount_total,
                            remarks: $income->remarks,
                        );

                        return $items;
                    })(),
                );
                $this->journalEntryActions->create($journalEntryDTO);
            } else {
                $journalEntryDTO = new JournalEntryUpdateDTO(
                    branchId: $income->branch_id,
                    code: $journalEntry->code,
                    date: $income->date,
                    referenceNo: $income->code,
                    remarks: $income->remarks,
                    items: (function () use ($income) {
                        $items = [];

                        if ((float) $income->amount_paid_immediately > 0) {
                            $items[] = new JournalEntryItemDTO(
                                sequence: count($items) + 1,
                                chartOfAccountId: $income->paidImmediatelyCashAccount?->chartOfAccount?->id,
                                debit: (float) $income->amount_paid_immediately,
                                credit: 0,
                                remarks: $income->remarks,
                            );
                        }

                        if ((float) $income->amount_receivable > 0) {
                            $items[] = new JournalEntryItemDTO(
                                sequence: count($items) + 1,
                                chartOfAccountId: $income->company->assetCurrentAccountReceivableChartOfAccount?->id,
                                debit: (float) $income->amount_receivable,
                                credit: 0,
                                remarks: $income->remarks,
                            );
                        }

                        $items[] = new JournalEntryItemDTO(
                            sequence: count($items) + 1,
                            chartOfAccountId: $income->category?->chartOfAccount?->id,
                            debit: 0,
                            credit: (float) $income->amount_total,
                            remarks: $income->remarks,
                        );

                        return $items;
                    })(),
                );
                $this->journalEntryActions->update($journalEntry, $journalEntryDTO);
            }

            self::updateSummary($income);

            $this->flushCache();

            return $income;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public static function updateSummary(Income $income): void
    {
        $income->refresh();

        $income->amount_receivable_paid = (float) $income->payments()->sum('amount');
        $income->amount_receivable_due = max(0, $income->amount_receivable - $income->amount_receivable_paid);
        $income->is_amount_receivable_paid_off = $income->amount_receivable_due == 0;
        $income->amount_total = (float) ($income->amount_paid_immediately + $income->amount_receivable);
        $income->save();
    }

    public function delete(Income $income): bool
    {
        $timer_start = microtime(true);
        $retval = false;

        try {
            foreach ($income->payments as $payment) {
                $this->incomePaymentActions->delete($payment, false);
            }

            $cashTransaction = $income->cashTransaction;
            if ($cashTransaction) {
                $this->cashTransactionActions->delete($cashTransaction);
            }

            $journalEntry = $income->journalEntry;
            if ($journalEntry) $this->journalEntryActions->delete($journalEntry);

            $retval = $income->delete();

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
