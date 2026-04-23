<?php

namespace App\Actions\LiabilityPayment;

use App\Actions\CashTransaction\CashTransactionActions;
use App\Actions\Liability\LiabilityActions;
use App\DTOs\CashTransactionCreateDTO;
use App\DTOs\CashTransactionUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\Helpers\TimezoneHelper;
use App\Models\LiabilityPayment;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class LiabilityPaymentActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'liability.category',
        'liability.creditor',
        'liability.supplier',
        'cashAccount',
    ];

    public function __construct(
        private readonly CashTransactionActions $cashTransactionActions,
    ) {
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,

        ?int $liabilityId,
        ?int $includeId,

        ?ExecuteDTO $execute
    ) {
        $query = LiabilityPayment::with(self::LIST_EAGER_LOADS)
            ->select('liability_payments.*')
            ->whereCompanyId('liability_payments', $companyId)
            ->whereBranchId('liability_payments', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $liabilityId,
            $includeId,
        ) {
            $query->where(function ($query) use (
                $withTrashed,
                $search,
                $liabilityId,
            ) {
                $query->withoutTrashed();
                if ($withTrashed) $query->withTrashed();

                if ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('liability_payments.code', 'like', '%'.$search.'%')
                            ->orWhere('liability_payments.remarks', 'like', '%'.$search.'%');
                    });
                }

                if (! is_null($liabilityId)) {
                    $query->where('liability_payments.liability_id', $liabilityId);
                }
            });

            if ($includeId) {
                $query->orWhere('liability_payments.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(liability_payments.id, '.$includeId.') desc');
        }
        $query->orderBy('liability_payments.date', 'desc');
        $query->orderBy('liability_payments.code', 'desc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    $liabilityId ?? '[null]',
                    $includeId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_liability_payment_'.implode('_', $cacheParams);

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

    public function read(LiabilityPayment $liabilityPayment): LiabilityPayment
    {
        return $liabilityPayment->load(self::LIST_EAGER_LOADS);
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
                $count = LiabilityPayment::whereCompanyId('liability_payments', $companyId)->withTrashed()->count() + 1 + $tryCount;
                $code = 'LIP'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        }

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = LiabilityPayment::whereCompanyId('liability_payments', $companyId)
            ->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    public function create(
        array $data,
        bool $updateParentSummary = true,
    ): LiabilityPayment {
        $timer_start = microtime(true);

        try {
            $liabilityPayment = new LiabilityPayment();
            $liabilityPayment->company_id = $data['company_id'];
            $liabilityPayment->branch_id = $data['branch_id'];
            $liabilityPayment->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $liabilityPayment->date = $this->generateDate($data['date']);
            $liabilityPayment->liability_id = $data['liability_id'];
            $liabilityPayment->cash_account_id = $data['cash_account_id'];
            $liabilityPayment->amount = $data['amount'];
            $liabilityPayment->remarks = $data['remarks'];
            $liabilityPayment->save();

            $this->cashTransactionActions->create(
                data: CashTransactionCreateDTO::fromLiabilityPayment($liabilityPayment)
            );

            if ($updateParentSummary) {
                LiabilityActions::updateSummary($liabilityPayment->liability);
                $liabilityPayment->refresh();
            }

            $this->flushCache();

            return $liabilityPayment;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(
        LiabilityPayment $liabilityPayment,
        array $data,
        bool $updateParentSummary = true,
    ): LiabilityPayment {
        $timer_start = microtime(true);

        try {
            $liabilityPayment->code = $this->generateUniqueCode($liabilityPayment->company_id, $data['code'], $liabilityPayment->id);
            $liabilityPayment->date = $this->generateDate($data['date']);
            $liabilityPayment->cash_account_id = $data['cash_account_id'];
            $liabilityPayment->amount = $data['amount'];
            $liabilityPayment->remarks = $data['remarks'];
            $liabilityPayment->save();

            $cashTransaction = $liabilityPayment->cashTransaction;
            if (! $cashTransaction) {
                $this->cashTransactionActions->create(
                    data: CashTransactionCreateDTO::fromLiabilityPayment($liabilityPayment)
                );
            } else {
                $this->cashTransactionActions->update(
                    cashTransaction: $cashTransaction,
                    data: CashTransactionUpdateDTO::fromLiabilityPayment($liabilityPayment)
                );
            }

            if ($updateParentSummary) {
                LiabilityActions::updateSummary($liabilityPayment->liability);
                $liabilityPayment->refresh();
            }

            $this->flushCache();

            return $liabilityPayment;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(
        LiabilityPayment $liabilityPayment,
        bool $updateParentSummary = true,
    ): bool {
        $timer_start = microtime(true);
        $retval = false;

        try {
            $cashTransaction = $liabilityPayment->cashTransaction;
            if ($cashTransaction) {
                $this->cashTransactionActions->delete($cashTransaction);
            }

            $retval = $liabilityPayment->delete();

            if ($updateParentSummary) {
                LiabilityActions::updateSummary($liabilityPayment->liability);
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
