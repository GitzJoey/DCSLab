<?php

namespace App\Actions\PurchaseOrderDownPaymentAllocation;

use App\Actions\PurchaseOrder\PurchaseOrderActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\PurchaseOrderDownPaymentAllocationCreateDTO;
use App\DTOs\PurchaseOrderDownPaymentAllocationUpdateDTO;
use App\Helpers\TimezoneHelper;
use App\Models\PurchaseOrderDownPaymentAllocation;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseOrderDownPaymentAllocationActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'purchaseOrderDownPayment.purchaseOrder.supplier',
    ];

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,
        ?string $startDate,
        ?string $endDate,
        ?int $purchaseOrderDownPaymentId,
        ?int $purchaseId,
        ?ExecuteDTO $execute
    ) {
        $query = PurchaseOrderDownPaymentAllocation::select('purchase_order_down_payment_allocations.*')
            ->with(self::LIST_EAGER_LOADS)
            ->join('companies', 'companies.id', '=', 'purchase_order_down_payment_allocations.company_id')
            ->join(
                'purchase_order_down_payments',
                'purchase_order_down_payments.id',
                '=',
                'purchase_order_down_payment_allocations.purchase_order_down_payment_id'
            )
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_down_payments.purchase_order_id')
            ->whereCompanyId('purchase_order_down_payment_allocations', $companyId)
            ->whereBranchId('purchase_order_down_payment_allocations', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $startDate,
            $endDate,
            $purchaseOrderDownPaymentId,
            $purchaseId,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('purchase_order_down_payment_allocations.remarks', 'like', '%'.$search.'%');
                });
            }

            if ($startDate) {
                $query->where('purchase_order_down_payment_allocations.date', '>=', TimezoneHelper::convertToUTC($startDate));
            }

            if ($endDate) {
                $query->where('purchase_order_down_payment_allocations.date', '<=', TimezoneHelper::convertToUTC($endDate));
            }

            if ($purchaseOrderDownPaymentId) {
                $query->where('purchase_order_down_payment_allocations.purchase_order_down_payment_id', $purchaseOrderDownPaymentId);
            }

            if ($purchaseId) {
                $query->where('purchase_order_down_payment_allocations.purchase_id', $purchaseId);
            }
        });

        $query->orderBy('purchase_order_down_payment_allocations.date', 'desc')
            ->orderBy('purchase_order_down_payment_allocations.id', 'asc');

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
                    $purchaseOrderDownPaymentId ?? '[null]',
                    $purchaseId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_purchase_order_down_payment_allocation_'.implode('_', $cacheParams);

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

    public function read(PurchaseOrderDownPaymentAllocation $purchaseOrderDownPaymentAllocation): PurchaseOrderDownPaymentAllocation
    {
        return $purchaseOrderDownPaymentAllocation->load(self::LIST_EAGER_LOADS);
    }

    public function generateDate(string $date): string
    {
        if ($date == config('dcslab.KEYWORDS.AUTO')) {
            $nowLocal = now(TimezoneHelper::getUserTimezone())->toDateTimeString();

            return TimezoneHelper::convertToUTC($nowLocal);
        }

        return TimezoneHelper::convertToUTC($date);
    }

    public function create(PurchaseOrderDownPaymentAllocationCreateDTO $data): PurchaseOrderDownPaymentAllocation
    {
        $timer_start = microtime(true);

        try {
            $purchaseOrderDownPaymentAllocation = new PurchaseOrderDownPaymentAllocation();
            $purchaseOrderDownPaymentAllocation->company_id = $data->companyId;
            $purchaseOrderDownPaymentAllocation->branch_id = $data->branchId;
            $purchaseOrderDownPaymentAllocation->purchase_order_down_payment_id = $data->purchaseOrderDownPaymentId;
            $purchaseOrderDownPaymentAllocation->purchase_id = $data->purchaseId;
            $purchaseOrderDownPaymentAllocation->date = $this->generateDate($data->date);
            $purchaseOrderDownPaymentAllocation->amount = $data->amount;
            $purchaseOrderDownPaymentAllocation->remarks = $data->remarks;
            $purchaseOrderDownPaymentAllocation->save();

            $poDownPayment = $purchaseOrderDownPaymentAllocation->purchaseOrderDownPayment;
            if ($poDownPayment) {
                $poDownPayment->amount_allocated = (float) $poDownPayment->allocations()->sum('amount');
                $poDownPayment->save();
                PurchaseOrderActions::updateSummary($poDownPayment->purchaseOrder);
                $purchaseOrderDownPaymentAllocation->refresh();
            }

            $this->flushCache();

            return $purchaseOrderDownPaymentAllocation;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(
        PurchaseOrderDownPaymentAllocation $poDownPaymentAllocation,
        PurchaseOrderDownPaymentAllocationUpdateDTO $data,
    ): PurchaseOrderDownPaymentAllocation {
        $timer_start = microtime(true);

        try {
            $originalPoDownPayment = $poDownPaymentAllocation->purchaseOrderDownPayment;

            $poDownPaymentAllocation->purchase_order_down_payment_id = $data->purchaseOrderDownPaymentId;
            $poDownPaymentAllocation->purchase_id = $data->purchaseId;
            $poDownPaymentAllocation->date = $this->generateDate($data->date);
            $poDownPaymentAllocation->amount = $data->amount;
            $poDownPaymentAllocation->remarks = $data->remarks;
            $poDownPaymentAllocation->save();

            if ($originalPoDownPayment) {
                $originalPoDownPayment->amount_allocated = (float) $originalPoDownPayment->allocations()->sum('amount');
                $originalPoDownPayment->save();
                PurchaseOrderActions::updateSummary($originalPoDownPayment->purchaseOrder);
            }

            $poDownPaymentAllocation->refresh();
            $newPoDownPayment = $poDownPaymentAllocation->purchaseOrderDownPayment;
            if ($newPoDownPayment && $originalPoDownPayment?->id !== $newPoDownPayment->id) {
                $newPoDownPayment->amount_allocated = (float) $newPoDownPayment->allocations()->sum('amount');
                $newPoDownPayment->save();
                PurchaseOrderActions::updateSummary($newPoDownPayment->purchaseOrder);
            }

            $this->flushCache();

            return $poDownPaymentAllocation;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseOrderDownPaymentAllocation $purchaseOrderDownPaymentAllocation): bool
    {
        $timer_start = microtime(true);

        try {
            $poDownPayment = $purchaseOrderDownPaymentAllocation->purchaseOrderDownPayment;
            $result = $purchaseOrderDownPaymentAllocation->delete();

            if ($poDownPayment) {
                $poDownPayment->amount_allocated = (float) $poDownPayment->allocations()->sum('amount');
                $poDownPayment->save();

                PurchaseOrderActions::updateSummary($poDownPayment->purchaseOrder);
            }

            $this->flushCache();

            return $result;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }
}
