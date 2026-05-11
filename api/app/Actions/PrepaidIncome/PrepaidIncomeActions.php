<?php

namespace App\Actions\PrepaidIncome;

use App\Actions\CashTransaction\CashTransactionActions;
use App\Actions\PrepaidIncomeImage\PrepaidIncomeImageActions;
use App\Actions\PrepaidIncomePayment\PrepaidIncomePaymentActions;
use App\DTOs\CashTransactionCreateDTO;
use App\DTOs\CashTransactionUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\DTOs\PrepaidIncomeCreateDTO;
use App\DTOs\PrepaidIncomeImageDTO;
use App\DTOs\PrepaidIncomePaymentCreateDTO;
use App\DTOs\PrepaidIncomePaymentUpdateDTO;
use App\DTOs\PrepaidIncomeUpdateDTO;
use App\Helpers\TimezoneHelper;
use App\Models\PrepaidIncome;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PrepaidIncomeActions
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
        private readonly PrepaidIncomeImageActions $prepaidIncomeImageActions,
        private readonly PrepaidIncomePaymentActions $prepaidIncomePaymentActions,
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
        $query = PrepaidIncome::select('prepaid_incomes.*');

        if ($execute?->pagination) {
            $query->with(self::DETAIL_EAGER_LOADS);
        } else {
            $query->with(self::LIST_EAGER_LOADS);
        }

        $query
            ->whereCompanyId('prepaid_incomes', $companyId)
            ->whereBranchId('prepaid_incomes', $branchId)
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
                        $query->where('prepaid_incomes.code', 'like', '%'.$search.'%')
                            ->orWhere('prepaid_incomes.remarks', 'like', '%'.$search.'%');
                    });
                }

                if (! is_null($categoryId)) {
                    $query->where('prepaid_incomes.income_category_id', $categoryId);
                }

                if (! is_null($isAmountReceivablePaidOff)) {
                    $query->where('prepaid_incomes.is_amount_receivable_paid_off', $isAmountReceivablePaidOff);
                }
            });

            if ($includeId) {
                $query->orWhere('prepaid_incomes.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(prepaid_incomes.id, '.$includeId.') desc');
        }
        $query->orderBy('prepaid_incomes.date', 'desc');
        $query->orderBy('prepaid_incomes.code', 'desc');

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

                $cacheKey = 'read_any_prepaid_income_'.implode('_', $cacheParams);

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

    public function read(PrepaidIncome $prepaidIncome): PrepaidIncome
    {
        return $prepaidIncome->load(self::DETAIL_EAGER_LOADS);
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
                $count = PrepaidIncome::whereCompanyId('prepaid_incomes', $companyId)->withTrashed()->count() + 1 + $tryCount;
                $code = 'PREI'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        }

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = PrepaidIncome::whereCompanyId('prepaid_incomes', $companyId)
            ->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    public function create(PrepaidIncomeCreateDTO $data): PrepaidIncome
    {
        $timer_start = microtime(true);

        try {
            $prepaidIncome = new PrepaidIncome();
            $prepaidIncome->company_id = $data->companyId;
            $prepaidIncome->branch_id = $data->branchId;
            $prepaidIncome->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $prepaidIncome->date = $this->generateDate($data->date);
            $prepaidIncome->income_category_id = $data->incomeCategoryId;
            $prepaidIncome->estimated_useful_life = $data->estimatedUsefulLife;
            $prepaidIncome->paid_immediately_cash_account_id = $data->paidImmediatelyCashAccountId;
            $prepaidIncome->amount_paid_immediately = $data->amountPaidImmediately;
            $prepaidIncome->amount_receivable = $data->amountReceivable;
            $prepaidIncome->due_days = $data->dueDays;
            $prepaidIncome->remarks = $data->remarks;
            $prepaidIncome->save();

            if ($prepaidIncome->paid_immediately_cash_account_id && $prepaidIncome->amount_paid_immediately > 0) {
                $this->cashTransactionActions->create(
                    data: CashTransactionCreateDTO::fromPrepaidIncome($prepaidIncome)
                );
            }

            foreach ($data->payments as $payment) {
                $dto = new PrepaidIncomePaymentCreateDTO(
                    companyId: $prepaidIncome->company_id,
                    branchId: $prepaidIncome->branch_id,
                    code: $payment['code'],
                    date: $payment['date'],
                    prepaidIncomeId: $prepaidIncome->id,
                    cashAccountId: $payment['cash_account_id'],
                    amount: $payment['amount'],
                    remarks: $payment['remarks'],
                );
                $this->prepaidIncomePaymentActions->create($dto, false);
            }

            foreach ($data->images as $image) {
                $prepaidIncomeImageDTO = new PrepaidIncomeImageDTO(
                    hash: $image['hash'],
                    isMain: (bool) $image['is_main'],
                );

                $this->prepaidIncomeImageActions->attachByHash($prepaidIncome, $prepaidIncomeImageDTO);
            }

            self::updateSummary($prepaidIncome);

            $this->flushCache();

            return $prepaidIncome;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(PrepaidIncome $prepaidIncome, PrepaidIncomeUpdateDTO $data): PrepaidIncome
    {
        $timer_start = microtime(true);

        try {
            $prepaidIncome->code = $this->generateUniqueCode($prepaidIncome->company_id, $data->code, $prepaidIncome->id);
            $prepaidIncome->date = $this->generateDate($data->date);
            $prepaidIncome->income_category_id = $data->incomeCategoryId;
            $prepaidIncome->estimated_useful_life = $data->estimatedUsefulLife;
            $prepaidIncome->paid_immediately_cash_account_id = $data->paidImmediatelyCashAccountId;
            $prepaidIncome->amount_paid_immediately = $data->amountPaidImmediately;
            $prepaidIncome->amount_receivable = $data->amountReceivable;
            $prepaidIncome->due_days = $data->dueDays;
            $prepaidIncome->remarks = $data->remarks;
            $prepaidIncome->save();

            $cashTransaction = $prepaidIncome->cashTransaction;
            if ($prepaidIncome->paid_immediately_cash_account_id && $prepaidIncome->amount_paid_immediately > 0) {
                if (! $cashTransaction) {
                    $this->cashTransactionActions->create(
                        data: CashTransactionCreateDTO::fromPrepaidIncome($prepaidIncome)
                    );
                } else {
                    $this->cashTransactionActions->update(
                        cashTransaction: $cashTransaction,
                        data: CashTransactionUpdateDTO::fromPrepaidIncome($prepaidIncome)
                    );
                }
            } elseif ($cashTransaction) {
                $this->cashTransactionActions->delete($cashTransaction);
            }

            foreach ($data->deletePaymentIds as $deleteId) {
                $prepaidIncomePayment = $prepaidIncome->payments()->findOrFail($deleteId);
                $this->prepaidIncomePaymentActions->delete($prepaidIncomePayment, false);
            }

            foreach ($data->deleteImageIds as $deleteId) {
                $this->prepaidIncomeImageActions->detachById($prepaidIncome, $deleteId);
            }

            foreach ($data->payments as $payment) {
                if (! empty($payment['id'])) {
                    $prepaidIncomePayment = $prepaidIncome->payments()->findOrFail($payment['id']);
                    $dto = new PrepaidIncomePaymentUpdateDTO(
                        code: $payment['code'],
                        date: $payment['date'],
                        cashAccountId: $payment['cash_account_id'],
                        amount: $payment['amount'],
                        remarks: $payment['remarks'],
                    );
                    $this->prepaidIncomePaymentActions->update($prepaidIncomePayment, $dto, false);
                } else {
                    $dto = new PrepaidIncomePaymentCreateDTO(
                        companyId: $prepaidIncome->company_id,
                        branchId: $prepaidIncome->branch_id,
                        code: $payment['code'],
                        date: $payment['date'],
                        prepaidIncomeId: $prepaidIncome->id,
                        cashAccountId: $payment['cash_account_id'],
                        amount: $payment['amount'],
                        remarks: $payment['remarks'],
                    );
                    $this->prepaidIncomePaymentActions->create($dto, false);
                }
            }

            foreach ($data->images as $image) {
                $prepaidIncomeImageDTO = new PrepaidIncomeImageDTO(
                    hash: $image['hash'],
                    isMain: (bool) $image['is_main'],
                );

                $this->prepaidIncomeImageActions->attachByHash($prepaidIncome, $prepaidIncomeImageDTO);
            }

            self::updateSummary($prepaidIncome);

            $this->flushCache();

            return $prepaidIncome;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public static function updateSummary(PrepaidIncome $prepaidIncome): void
    {
        $prepaidIncome->refresh();

        $prepaidIncome->amount_receivable_paid = (float) $prepaidIncome->payments()->sum('amount');
        $prepaidIncome->amount_receivable_due = max(0, $prepaidIncome->amount_receivable - $prepaidIncome->amount_receivable_paid);
        $prepaidIncome->is_amount_receivable_paid_off = $prepaidIncome->amount_receivable_due == 0;
        $prepaidIncome->amount_total = (float) ($prepaidIncome->amount_paid_immediately + $prepaidIncome->amount_receivable);
        $prepaidIncome->save();
    }

    public function delete(PrepaidIncome $prepaidIncome): bool
    {
        $timer_start = microtime(true);
        $retval = false;

        try {
            foreach ($prepaidIncome->payments as $payment) {
                $this->prepaidIncomePaymentActions->delete($payment, false);
            }

            $cashTransaction = $prepaidIncome->cashTransaction;
            if ($cashTransaction) {
                $this->cashTransactionActions->delete($cashTransaction);
            }

            $retval = $prepaidIncome->delete();

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
