<?php

namespace App\Actions\PrepaidExpense;

use App\Actions\CashTransaction\CashTransactionActions;
use App\Actions\PrepaidExpenseImage\PrepaidExpenseImageActions;
use App\Actions\PrepaidExpensePayment\PrepaidExpensePaymentActions;
use App\DTOs\CashTransactionCreateDTO;
use App\DTOs\CashTransactionUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\DTOs\PrepaidExpenseCreateDTO;
use App\DTOs\PrepaidExpenseImageDTO;
use App\DTOs\PrepaidExpensePaymentCreateDTO;
use App\DTOs\PrepaidExpensePaymentUpdateDTO;
use App\DTOs\PrepaidExpenseUpdateDTO;
use App\Helpers\TimezoneHelper;
use App\Models\PrepaidExpense;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PrepaidExpenseActions
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
        private readonly PrepaidExpenseImageActions $prepaidExpenseImageActions,
        private readonly PrepaidExpensePaymentActions $prepaidExpensePaymentActions,
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
        $query = PrepaidExpense::select('prepaid_expenses.*');

        if ($execute?->pagination) {
            $query->with(self::DETAIL_EAGER_LOADS);
        } else {
            $query->with(self::LIST_EAGER_LOADS);
        }

        $query
            ->whereCompanyId('prepaid_expenses', $companyId)
            ->whereBranchId('prepaid_expenses', $branchId)
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
                        $query->where('prepaid_expenses.code', 'like', '%'.$search.'%')
                            ->orWhere('prepaid_expenses.remarks', 'like', '%'.$search.'%');
                    });
                }

                if (! is_null($categoryId)) {
                    $query->where('prepaid_expenses.expense_category_id', $categoryId);
                }

                if (! is_null($isAmountPayablePaidOff)) {
                    $query->where('prepaid_expenses.is_amount_payable_paid_off', $isAmountPayablePaidOff);
                }
            });

            if ($includeId) {
                $query->orWhere('prepaid_expenses.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(prepaid_expenses.id, '.$includeId.') desc');
        }
        $query->orderBy('prepaid_expenses.date', 'desc');
        $query->orderBy('prepaid_expenses.code', 'desc');

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

                $cacheKey = 'read_any_prepaid_expense_'.implode('_', $cacheParams);

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

    public function read(PrepaidExpense $prepaidExpense): PrepaidExpense
    {
        return $prepaidExpense->load(self::DETAIL_EAGER_LOADS);
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
                $count = PrepaidExpense::whereCompanyId('prepaid_expenses', $companyId)->withTrashed()->count() + 1 + $tryCount;
                $code = 'PRE'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        }

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = PrepaidExpense::whereCompanyId('prepaid_expenses', $companyId)
            ->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    public function create(PrepaidExpenseCreateDTO $data): PrepaidExpense
    {
        $timer_start = microtime(true);

        try {
            $prepaidExpense = new PrepaidExpense();
            $prepaidExpense->company_id = $data->companyId;
            $prepaidExpense->branch_id = $data->branchId;
            $prepaidExpense->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $prepaidExpense->date = $this->generateDate($data->date);
            $prepaidExpense->expense_category_id = $data->expenseCategoryId;
            $prepaidExpense->estimated_useful_life = $data->estimatedUsefulLife;
            $prepaidExpense->paid_immediately_cash_account_id = $data->paidImmediatelyCashAccountId;
            $prepaidExpense->amount_paid_immediately = $data->amountPaidImmediately;
            $prepaidExpense->amount_payable = $data->amountPayable;
            $prepaidExpense->due_days = $data->dueDays;
            $prepaidExpense->remarks = $data->remarks;
            $prepaidExpense->save();

            if ($prepaidExpense->paid_immediately_cash_account_id && $prepaidExpense->amount_paid_immediately > 0) {
                $this->cashTransactionActions->create(
                    data: CashTransactionCreateDTO::fromPrepaidExpense($prepaidExpense)
                );
            }

            foreach ($data->payments as $payment) {
                $dto = new PrepaidExpensePaymentCreateDTO(
                    companyId: $prepaidExpense->company_id,
                    branchId: $prepaidExpense->branch_id,
                    code: $payment['code'],
                    date: $payment['date'],
                    prepaidExpenseId: $prepaidExpense->id,
                    cashAccountId: $payment['cash_account_id'],
                    amount: $payment['amount'],
                    remarks: $payment['remarks'],
                );
                $this->prepaidExpensePaymentActions->create($dto, false);
            }

            foreach ($data->images as $image) {
                $prepaidExpenseImageDTO = new PrepaidExpenseImageDTO(
                    hash: $image['hash'],
                    isMain: (bool) $image['is_main'],
                );

                $this->prepaidExpenseImageActions->attachByHash($prepaidExpense, $prepaidExpenseImageDTO);
            }

            self::updateSummary($prepaidExpense);

            $this->flushCache();

            return $prepaidExpense;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(PrepaidExpense $prepaidExpense, PrepaidExpenseUpdateDTO $data): PrepaidExpense
    {
        $timer_start = microtime(true);

        try {
            $prepaidExpense->code = $this->generateUniqueCode($prepaidExpense->company_id, $data->code, $prepaidExpense->id);
            $prepaidExpense->date = $this->generateDate($data->date);
            $prepaidExpense->expense_category_id = $data->expenseCategoryId;
            $prepaidExpense->estimated_useful_life = $data->estimatedUsefulLife;
            $prepaidExpense->paid_immediately_cash_account_id = $data->paidImmediatelyCashAccountId;
            $prepaidExpense->amount_paid_immediately = $data->amountPaidImmediately;
            $prepaidExpense->amount_payable = $data->amountPayable;
            $prepaidExpense->due_days = $data->dueDays;
            $prepaidExpense->remarks = $data->remarks;
            $prepaidExpense->save();

            $cashTransaction = $prepaidExpense->cashTransaction;
            if ($prepaidExpense->paid_immediately_cash_account_id && $prepaidExpense->amount_paid_immediately > 0) {
                if (! $cashTransaction) {
                    $this->cashTransactionActions->create(
                        data: CashTransactionCreateDTO::fromPrepaidExpense($prepaidExpense)
                    );
                } else {
                    $this->cashTransactionActions->update(
                        cashTransaction: $cashTransaction,
                        data: CashTransactionUpdateDTO::fromPrepaidExpense($prepaidExpense)
                    );
                }
            } elseif ($cashTransaction) {
                $this->cashTransactionActions->delete($cashTransaction);
            }

            foreach ($data->deletePaymentIds as $deleteId) {
                $prepaidExpensePayment = $prepaidExpense->payments()->findOrFail($deleteId);
                $this->prepaidExpensePaymentActions->delete($prepaidExpensePayment, false);
            }

            foreach ($data->deleteImageIds as $deleteId) {
                $this->prepaidExpenseImageActions->detachById($prepaidExpense, $deleteId);
            }

            foreach ($data->payments as $payment) {
                if (! empty($payment['id'])) {
                    $prepaidExpensePayment = $prepaidExpense->payments()->findOrFail($payment['id']);
                    $dto = new PrepaidExpensePaymentUpdateDTO(
                        code: $payment['code'],
                        date: $payment['date'],
                        cashAccountId: $payment['cash_account_id'],
                        amount: $payment['amount'],
                        remarks: $payment['remarks'],
                    );
                    $this->prepaidExpensePaymentActions->update($prepaidExpensePayment, $dto, false);
                } else {
                    $dto = new PrepaidExpensePaymentCreateDTO(
                        companyId: $prepaidExpense->company_id,
                        branchId: $prepaidExpense->branch_id,
                        code: $payment['code'],
                        date: $payment['date'],
                        prepaidExpenseId: $prepaidExpense->id,
                        cashAccountId: $payment['cash_account_id'],
                        amount: $payment['amount'],
                        remarks: $payment['remarks'],
                    );
                    $this->prepaidExpensePaymentActions->create($dto, false);
                }
            }

            foreach ($data->images as $image) {
                $prepaidExpenseImageDTO = new PrepaidExpenseImageDTO(
                    hash: $image['hash'],
                    isMain: (bool) $image['is_main'],
                );

                $this->prepaidExpenseImageActions->attachByHash($prepaidExpense, $prepaidExpenseImageDTO);
            }

            self::updateSummary($prepaidExpense);

            $this->flushCache();

            return $prepaidExpense;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public static function updateSummary(PrepaidExpense $prepaidExpense): void
    {
        $prepaidExpense->refresh();

        $prepaidExpense->amount_payable_paid = (float) $prepaidExpense->payments()->sum('amount');
        $prepaidExpense->amount_payable_due = max(0, $prepaidExpense->amount_payable - $prepaidExpense->amount_payable_paid);
        $prepaidExpense->is_amount_payable_paid_off = $prepaidExpense->amount_payable_due == 0;
        $prepaidExpense->amount_total = (float) ($prepaidExpense->amount_paid_immediately + $prepaidExpense->amount_payable);
        $prepaidExpense->save();
    }

    public function delete(PrepaidExpense $prepaidExpense): bool
    {
        $timer_start = microtime(true);
        $retval = false;

        try {
            foreach ($prepaidExpense->payments as $payment) {
                $this->prepaidExpensePaymentActions->delete($payment, false);
            }

            $cashTransaction = $prepaidExpense->cashTransaction;
            if ($cashTransaction) {
                $this->cashTransactionActions->delete($cashTransaction);
            }

            $retval = $prepaidExpense->delete();

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
