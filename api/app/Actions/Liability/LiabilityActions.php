<?php

namespace App\Actions\Liability;

use App\Actions\CashTransaction\CashTransactionActions;
use App\Actions\LiabilityPayment\LiabilityPaymentActions;
use App\DTOs\CashTransactionCreateDTO;
use App\DTOs\CashTransactionUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\DTOs\LiabilityCreateDTO;
use App\DTOs\LiabilityUpdateDTO;
use App\Helpers\TimezoneHelper;
use App\Models\Liability;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class LiabilityActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'category',
        'creditor',
        'supplier',
        'cashAccount',
    ];

    private const DETAIL_EAGER_LOADS = [
        'company',
        'branch',
        'category',
        'creditor',
        'supplier',
        'cashAccount',
        'payments.cashAccount',
    ];

    public function __construct(
        private readonly CashTransactionActions $cashTransactionActions,
        private readonly LiabilityPaymentActions $liabilityPaymentActions,
    ) {
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,

        ?int $categoryId,
        ?int $creditorId,
        ?int $supplierId,
        ?bool $isPaidOff,
        ?int $includeId,

        ?ExecuteDTO $execute
    ) {
        $query = Liability::select('liabilities.*');

        if ($execute?->pagination) {
            $query->with(self::DETAIL_EAGER_LOADS);
        } else {
            $query->with(self::LIST_EAGER_LOADS);
        }

        $query
            ->whereCompanyId('liabilities', $companyId)
            ->whereBranchId('liabilities', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $categoryId,
            $creditorId,
            $supplierId,
            $isPaidOff,
            $includeId,
        ) {
            $query->where(function ($query) use (
                $withTrashed,
                $search,
                $categoryId,
                $creditorId,
                $supplierId,
                $isPaidOff,
            ) {
                $query->withoutTrashed();
                if ($withTrashed) $query->withTrashed();

                if ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('liabilities.code', 'like', '%'.$search.'%')
                            ->orWhere('liabilities.remarks', 'like', '%'.$search.'%')
                            ->orWhereHas('category', function ($categoryQuery) use ($search) {
                                $categoryQuery->where('liability_categories.code', 'like', '%'.$search.'%')
                                    ->orWhere('liability_categories.name', 'like', '%'.$search.'%');
                            })
                            ->orWhereHas('creditor', function ($creditorQuery) use ($search) {
                                $creditorQuery->where('liability_creditors.code', 'like', '%'.$search.'%')
                                    ->orWhere('liability_creditors.name', 'like', '%'.$search.'%');
                            })
                            ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                                $supplierQuery->where('suppliers.code', 'like', '%'.$search.'%')
                                    ->orWhere('suppliers.name', 'like', '%'.$search.'%');
                            });
                    });
                }

                if (! is_null($categoryId)) {
                    $query->where('liabilities.category_id', $categoryId);
                }

                if (! is_null($creditorId)) {
                    $query->where('liabilities.creditor_id', $creditorId);
                }

                if (! is_null($supplierId)) {
                    $query->where('liabilities.supplier_id', $supplierId);
                }

                if (! is_null($isPaidOff)) {
                    $query->where('liabilities.is_paid_off', $isPaidOff);
                }
            });

            if ($includeId) {
                $query->orWhere('liabilities.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(liabilities.id, '.$includeId.') desc');
        }
        $query->orderBy('liabilities.date', 'desc');
        $query->orderBy('liabilities.code', 'desc');

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
                    $creditorId ?? '[null]',
                    $supplierId ?? '[null]',
                    is_null($isPaidOff) ? '[null]' : ($isPaidOff ? 'true' : 'false'),
                    $includeId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_liability_'.implode('_', $cacheParams);

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

    public function read(Liability $liability): Liability
    {
        return $liability->load(self::DETAIL_EAGER_LOADS);
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
                $count = Liability::whereCompanyId('liabilities', $companyId)->withTrashed()->count() + 1 + $tryCount;
                $code = 'LIA'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        }

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = Liability::whereCompanyId('liabilities', $companyId)
            ->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    public function create(LiabilityCreateDTO $data): Liability
    {
        $timer_start = microtime(true);

        try {
            $liability = new Liability();
            $liability->company_id = $data->companyId;
            $liability->branch_id = $data->branchId;
            $liability->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $liability->date = $this->generateDate($data->date);
            $liability->category_id = $data->categoryId;
            $liability->creditor_id = $data->creditorId;
            $liability->supplier_id = $data->supplierId;
            $liability->cash_account_id = $data->cashAccountId;
            $liability->amount_received = $data->amountReceived;
            $liability->amount_payable = $data->amountPayable;
            $liability->due_days = $data->dueDays;
            $liability->remarks = $data->remarks;
            $liability->save();

            if ($liability->cash_account_id && $liability->amount_received > 0) {
                $this->cashTransactionActions->create(
                    data: CashTransactionCreateDTO::fromLiability($liability)
                );
            }

            foreach ($data->payments as $payment) {
                $this->liabilityPaymentActions->create([
                    'company_id' => $liability->company_id,
                    'branch_id' => $liability->branch_id,
                    'code' => $payment['code'],
                    'date' => $payment['date'],
                    'liability_id' => $liability->id,
                    'cash_account_id' => $payment['cash_account_id'],
                    'amount' => $payment['amount'],
                    'remarks' => $payment['remarks'] ?? null,
                ], false);
            }

            self::updateSummary($liability);

            $this->flushCache();

            return $liability;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(Liability $liability, LiabilityUpdateDTO $data): Liability
    {
        $timer_start = microtime(true);

        try {
            $liability->code = $this->generateUniqueCode($liability->company_id, $data->code, $liability->id);
            $liability->date = $this->generateDate($data->date);
            $liability->category_id = $data->categoryId;
            $liability->creditor_id = $data->creditorId;
            $liability->supplier_id = $data->supplierId;
            $liability->cash_account_id = $data->cashAccountId;
            $liability->amount_received = $data->amountReceived;
            $liability->amount_payable = $data->amountPayable;
            $liability->due_days = $data->dueDays;
            $liability->remarks = $data->remarks;
            $liability->save();

            $cashTransaction = $liability->cashTransaction;
            if ($liability->cash_account_id && $liability->amount_received > 0) {
                if (! $cashTransaction) {
                    $this->cashTransactionActions->create(
                        data: CashTransactionCreateDTO::fromLiability($liability)
                    );
                } else {
                    $this->cashTransactionActions->update(
                        cashTransaction: $cashTransaction,
                        data: CashTransactionUpdateDTO::fromLiability($liability)
                    );
                }
            } elseif ($cashTransaction) {
                $this->cashTransactionActions->delete($cashTransaction);
            }

            foreach ($data->deletePaymentIds as $deleteId) {
                $liabilityPayment = $liability->payments()->findOrFail($deleteId);
                $this->liabilityPaymentActions->delete($liabilityPayment, false);
            }

            foreach ($data->payments as $payment) {
                if (! empty($payment['id'])) {
                    $liabilityPayment = $liability->payments()->findOrFail($payment['id']);
                    $this->liabilityPaymentActions->update($liabilityPayment, [
                        'code' => $payment['code'],
                        'date' => $payment['date'],
                        'cash_account_id' => $payment['cash_account_id'],
                        'amount' => $payment['amount'],
                        'remarks' => $payment['remarks'] ?? null,
                    ], false);
                } else {
                    $this->liabilityPaymentActions->create([
                        'company_id' => $liability->company_id,
                        'branch_id' => $liability->branch_id,
                        'code' => $payment['code'],
                        'date' => $payment['date'],
                        'liability_id' => $liability->id,
                        'cash_account_id' => $payment['cash_account_id'],
                        'amount' => $payment['amount'],
                        'remarks' => $payment['remarks'] ?? null,
                    ], false);
                }
            }

            self::updateSummary($liability);

            $this->flushCache();

            return $liability;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public static function updateSummary(Liability $liability): void
    {
        $liability->refresh();

        $liability->amount_total = (float) $liability->amount_received + (float) $liability->amount_payable;
        $liability->amount_paid_by_cash_account = (float) $liability->payments()->sum('amount');
        // Stock-adjustment settlement link is not implemented yet in this batch.
        $liability->amount_paid_by_stock_adjustment = 0;
        $liability->amount_due = max(
            0,
            (float) $liability->amount_total
            - (float) $liability->amount_paid_by_cash_account
            - (float) $liability->amount_paid_by_stock_adjustment
        );
        $liability->is_paid_off = $liability->amount_due == 0;
        $liability->save();
    }

    public function delete(Liability $liability): bool
    {
        $timer_start = microtime(true);
        $retval = false;

        try {
            $cashTransaction = $liability->cashTransaction;
            if ($cashTransaction) {
                $this->cashTransactionActions->delete($cashTransaction);
            }

            foreach ($liability->payments as $payment) {
                $this->liabilityPaymentActions->delete($payment, false);
            }

            $retval = $liability->delete();

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
