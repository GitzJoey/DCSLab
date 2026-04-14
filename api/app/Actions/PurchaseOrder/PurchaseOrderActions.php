<?php

namespace App\Actions\PurchaseOrder;

use App\DTOs\ExecuteDTO;
use App\DTOs\PurchaseOrderCreateDTO;
use App\DTOs\PurchaseOrderUpdateDTO;
use App\Helpers\TimezoneHelper;
use App\Models\Company;
use App\Models\PurchaseOrder;
use App\Services\PurchaseOrder\PurchaseOrderService;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseOrderActions
{
    use CacheHelper;
    use LoggerHelper;

    private $purchaseOrderService;

    public function __construct(
        PurchaseOrderService $purchaseOrderService,
    ) {
        $this->purchaseOrderService = $purchaseOrderService;
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,

        ?string $startDate,
        ?string $endDate,
        ?int $supplierId,

        ?ExecuteDTO $execute
    ) {
        $query = PurchaseOrder::select('purchase_orders.*')
            ->with(['company', 'branch', 'supplier'])
            ->join('companies', 'companies.id', '=', 'purchase_orders.company_id')
            ->whereCompanyId('purchase_orders', $companyId)
            ->whereBranchId('purchase_orders', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $startDate,
            $endDate,
            $supplierId,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->search($search);
            }

            if ($startDate) {
                $query->where('purchase_orders.date', '>=', TimezoneHelper::convertToUTC($startDate));
            }

            if ($endDate) {
                $query->where('purchase_orders.date', '<=', TimezoneHelper::convertToUTC($endDate));
            }

            if ($supplierId) {
                $query->where('purchase_orders.supplier_id', $supplierId);
            }
        });

        $query->orderBy('purchase_orders.date', 'desc');

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
                    $supplierId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_purchase_order_'.implode('_', $cacheParams);

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

    public function read(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        return $purchaseOrder->load([
            'company',
            'branch',
            'supplier',
            'globalDiscounts',
            'items.productUnit.unit',
            'items.productUnit.product.category',
            'items.productUnit.product.brand',
            'items.productUnit.product.baseProductUnit.unit',
            'items.productUnit.product.images',
            'items.productUnit.product.mainImage',
            'items.vatProfile',
            'items.productUnitPriceDiscounts',
            'items.subtotalDiscounts',
            'downPayments.cashAccount',
        ]);
    }

    public function generateUniqueCode(int $companyId, string $code, ?int $exceptId): string
    {
        if ($code != Config::get('dcslab.KEYWORDS.AUTO')) return $code;

        $company = Company::find($companyId);

        $tryCount = 0;
        do {
            $count = $company->purchaseOrders()->withTrashed()->count() + 1 + $tryCount;
            $code = 'PO'.str_pad($count, 5, '0', STR_PAD_LEFT);
            $tryCount++;
        } while (! $this->isUniqueCode($companyId, $code, $exceptId));

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = PurchaseOrder::where('company_id', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    public function create(PurchaseOrderCreateDTO $data): PurchaseOrder
    {
        $timer_start = microtime(true);

        try {
            $purchaseOrder = new PurchaseOrder();
            $purchaseOrder->company_id = $data->companyId;
            $purchaseOrder->branch_id = $data->branchId;
            $purchaseOrder->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $purchaseOrder->date = $this->purchaseOrderService->generateDate($data->date);
            $purchaseOrder->due_days = $data->dueDays;
            $purchaseOrder->supplier_id = $data->supplierId;
            $purchaseOrder->remarks = $data->remarks;
            $purchaseOrder->rounding = $data->rounding;
            $purchaseOrder->save();

            $this->purchaseOrderService->createGlobalDiscounts(
                purchaseOrder: $purchaseOrder,
                globalDiscounts: $data->globalDiscounts,
            );
            $this->purchaseOrderService->createItems(
                purchaseOrder: $purchaseOrder,
                items: $data->items,
            );
            $this->purchaseOrderService->createDownPayments(
                purchaseOrder: $purchaseOrder,
                downPayments: $data->downPayments,
            );
            $this->purchaseOrderService->updateSummary($purchaseOrder);

            $this->flushCache();

            return $purchaseOrder->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(PurchaseOrder $purchaseOrder, PurchaseOrderUpdateDTO $data): PurchaseOrder
    {
        $timer_start = microtime(true);

        try {
            $purchaseOrder->company_id = $data->companyId;
            $purchaseOrder->branch_id = $data->branchId;
            $purchaseOrder->code = $this->generateUniqueCode($data->companyId, $data->code, $purchaseOrder->id);
            $purchaseOrder->date = $this->purchaseOrderService->generateDate($data->date);
            $purchaseOrder->due_days = $data->dueDays;
            $purchaseOrder->supplier_id = $data->supplierId;
            $purchaseOrder->remarks = $data->remarks;
            $purchaseOrder->rounding = $data->rounding;
            $purchaseOrder->save();

            $this->purchaseOrderService->syncGlobalDiscounts(
                purchaseOrder: $purchaseOrder,
                deleteGlobalDiscountIds: $data->deleteGlobalDiscountIds,
                globalDiscounts: $data->globalDiscounts,
            );
            $this->purchaseOrderService->syncItems(
                purchaseOrder: $purchaseOrder,
                deleteItemIds: $data->deleteItemIds,
                items: $data->items,
            );
            $this->purchaseOrderService->syncDownPayments(
                purchaseOrder: $purchaseOrder,
                deleteDownPaymentIds: $data->deleteDownPaymentIds,
                downPayments: $data->downPayments,
            );
            $this->purchaseOrderService->updateSummary($purchaseOrder);

            $this->flushCache();

            return $purchaseOrder->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseOrder $purchaseOrder): bool
    {
        $timer_start = microtime(true);

        try {
            $this->purchaseOrderService->deleteGlobalDiscounts($purchaseOrder);
            $this->purchaseOrderService->deleteDownPayments($purchaseOrder);
            $this->purchaseOrderService->deleteItems($purchaseOrder);

            $result = $purchaseOrder->delete();

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
