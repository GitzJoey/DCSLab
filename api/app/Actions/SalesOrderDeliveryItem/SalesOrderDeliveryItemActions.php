<?php

namespace App\Actions\SalesOrderDeliveryItem;

use App\Actions\SalesOrderDelivery\SalesOrderDeliveryActions;
use App\Actions\SalesOrderDeliveryItemSerial\SalesOrderDeliveryItemSerialActions;
use App\Actions\StockTransaction\StockTransactionActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\SalesOrderDeliveryItemCreateDTO;
use App\DTOs\SalesOrderDeliveryItemSerialCreateDTO;
use App\DTOs\SalesOrderDeliveryItemSerialUpdateDTO;
use App\DTOs\SalesOrderDeliveryItemUpdateDTO;
use App\DTOs\StockTransactionCreateDTO;
use App\DTOs\StockTransactionUpdateDTO;
use App\Models\ProductUnit;
use App\Models\PurchaseInvoiceItem;
use App\Models\PurchaseOrderItem;
use App\Models\SalesOrderDelivery;
use App\Models\SalesOrderDeliveryItem;
use App\Models\SalesOrderItem;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class SalesOrderDeliveryItemActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct(
        private readonly SalesOrderDeliveryItemSerialActions $salesOrderDeliveryItemSerialActions,
        private readonly StockTransactionActions $stockTransactionActions,
    ) {
    }

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'salesOrderDelivery.customer',
        'salesOrderDelivery.salesOrder.customer',
        'productUnit.unit',
        'productUnit.product.category',
        'productUnit.product.brand',
        'productUnit.product.baseProductUnit.unit',
        'productUnit.product.images',
        'productUnit.product.mainImage',
    ];

    private const DETAIL_EAGER_LOADS = [
        'company',
        'branch',
        'salesOrderDelivery.customer',
        'salesOrderDelivery.salesOrder.customer',
        'salesOrderItem',
        'productUnit.unit',
        'productUnit.product.category',
        'productUnit.product.brand',
        'productUnit.product.baseProductUnit.unit',
        'productUnit.product.images',
        'productUnit.product.mainImage',
        'serials',
    ];

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,

        ?string $search,
        ?int $salesOrderDeliveryId,
        ?bool $hasSalesOrderItemProduct,
        ?int $productUnitId,

        ?ExecuteDTO $execute
    ) {
        $query = SalesOrderDeliveryItem::select('sales_order_delivery_items.*');

        if ($execute?->pagination) {
            $query->with(self::DETAIL_EAGER_LOADS);
        } else {
            $query->with(self::LIST_EAGER_LOADS);
        }

        $query->join('companies', 'companies.id', '=', 'sales_order_delivery_items.company_id')
            ->join('sales_order_deliveries', 'sales_order_deliveries.id', '=', 'sales_order_delivery_items.sales_order_delivery_id')
            ->whereCompanyId('sales_order_delivery_items', $companyId)
            ->whereBranchId('sales_order_delivery_items', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $salesOrderDeliveryId,
            $hasSalesOrderItemProduct,
            $productUnitId,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->where('sales_order_delivery_items.remarks', 'like', '%'.$search.'%');
            }

            if ($salesOrderDeliveryId) {
                $query->where('sales_order_delivery_items.sales_order_delivery_id', $salesOrderDeliveryId);
            }

            if (! is_null($hasSalesOrderItemProduct)) {
                $query->where('sales_order_delivery_items.has_sales_order_item_product', $hasSalesOrderItemProduct);
            }

            if ($productUnitId) {
                $query->where('sales_order_delivery_items.product_unit_id', $productUnitId);
            }
        });

        $query->orderBy('sales_order_deliveries.date', 'desc')
            ->orderBy('sales_order_delivery_items.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    $salesOrderDeliveryId ?? '[null]',
                    is_null($hasSalesOrderItemProduct) ? '[null]' : ($hasSalesOrderItemProduct ? 'true' : 'false'),
                    $productUnitId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_sales_order_delivery_item_'.implode('_', $cacheParams);

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

    public function read(SalesOrderDeliveryItem $salesOrderDeliveryItem): SalesOrderDeliveryItem
    {
        return $salesOrderDeliveryItem->load(self::DETAIL_EAGER_LOADS);
    }

    public function create(SalesOrderDeliveryItemCreateDTO $data, bool $updateParent): SalesOrderDeliveryItem
    {
        $timer_start = microtime(true);

        try {
            $salesOrderDelivery = SalesOrderDelivery::query()->findOrFail($data->salesOrderDeliveryId);
            $productId = (int) ProductUnit::query()->whereKey($data->productUnitId)->value('product_id');
            $salesOrderItem = $this->matchSalesOrderItem($salesOrderDelivery, $productId);
            $productUnitQtyBase = $data->qty * $data->productUnitConversionValue;
            $baseUnitCogs = $this->resolveBaseUnitCogs($data->companyId, $productId);

            $salesOrderDeliveryItem = new SalesOrderDeliveryItem();
            $salesOrderDeliveryItem->company_id = $data->companyId;
            $salesOrderDeliveryItem->branch_id = $data->branchId;
            $salesOrderDeliveryItem->sales_order_delivery_id = $data->salesOrderDeliveryId;
            $salesOrderDeliveryItem->sales_order_item_id = $salesOrderItem?->id;
            $salesOrderDeliveryItem->has_sales_order_item_product = ! is_null($salesOrderItem);
            $salesOrderDeliveryItem->qty = $data->qty;
            $salesOrderDeliveryItem->product_unit_id = $data->productUnitId;
            $salesOrderDeliveryItem->product_id = $productId;
            $salesOrderDeliveryItem->product_unit_conversion_value = $data->productUnitConversionValue;
            $salesOrderDeliveryItem->product_unit_qty_base = $productUnitQtyBase;
            $salesOrderDeliveryItem->base_unit_cogs = $baseUnitCogs;
            $salesOrderDeliveryItem->total_cogs = $baseUnitCogs * $productUnitQtyBase;
            $salesOrderDeliveryItem->remarks = $data->remarks;
            $salesOrderDeliveryItem->save();

            $this->stockTransactionActions->create(
                data: StockTransactionCreateDTO::fromSalesOrderDeliveryItem($salesOrderDeliveryItem)
            );

            foreach ($data->serials as $serial) {
                $dto = new SalesOrderDeliveryItemSerialCreateDTO(
                    companyId: $salesOrderDeliveryItem->company_id,
                    branchId: $salesOrderDeliveryItem->branch_id,
                    salesOrderDeliveryId: $salesOrderDeliveryItem->sales_order_delivery_id,
                    salesOrderDeliveryItemId: $salesOrderDeliveryItem->id,
                    serial: $serial['serial'],
                );

                $this->salesOrderDeliveryItemSerialActions->create($dto);
            }

            if ($updateParent) {
                SalesOrderDeliveryActions::updateSummary($salesOrderDeliveryItem->salesOrderDelivery);
            }

            $this->flushCache();

            return $salesOrderDeliveryItem->refresh()->load(self::DETAIL_EAGER_LOADS);
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(SalesOrderDeliveryItem $salesOrderDeliveryItem, SalesOrderDeliveryItemUpdateDTO $data, bool $updateParent): SalesOrderDeliveryItem
    {
        $timer_start = microtime(true);

        try {
            $salesOrderDelivery = SalesOrderDelivery::query()->findOrFail($salesOrderDeliveryItem->sales_order_delivery_id);
            $productId = (int) ProductUnit::query()->whereKey($data->productUnitId)->value('product_id');
            $salesOrderItem = $this->matchSalesOrderItem($salesOrderDelivery, $productId);
            $productUnitQtyBase = $data->qty * $data->productUnitConversionValue;
            $baseUnitCogs = $this->resolveBaseUnitCogs($salesOrderDeliveryItem->company_id, $productId);

            $salesOrderDeliveryItem->sales_order_item_id = $salesOrderItem?->id;
            $salesOrderDeliveryItem->has_sales_order_item_product = ! is_null($salesOrderItem);
            $salesOrderDeliveryItem->qty = $data->qty;
            $salesOrderDeliveryItem->product_unit_id = $data->productUnitId;
            $salesOrderDeliveryItem->product_id = $productId;
            $salesOrderDeliveryItem->product_unit_conversion_value = $data->productUnitConversionValue;
            $salesOrderDeliveryItem->product_unit_qty_base = $productUnitQtyBase;
            $salesOrderDeliveryItem->base_unit_cogs = $baseUnitCogs;
            $salesOrderDeliveryItem->total_cogs = $baseUnitCogs * $productUnitQtyBase;
            $salesOrderDeliveryItem->remarks = $data->remarks;
            $salesOrderDeliveryItem->save();

            $stockTransaction = $salesOrderDeliveryItem->stockTransaction;
            if (! $stockTransaction) {
                $dto = StockTransactionCreateDTO::fromSalesOrderDeliveryItem($salesOrderDeliveryItem);
                $this->stockTransactionActions->create($dto);
            } else {
                $dto = StockTransactionUpdateDTO::fromSalesOrderDeliveryItem($salesOrderDeliveryItem);
                $this->stockTransactionActions->update($stockTransaction, $dto);
            }

            foreach ($data->deleteSerialIds as $deleteId) {
                $salesOrderDeliveryItemSerial = $salesOrderDeliveryItem->serials()->findOrFail($deleteId);
                $this->salesOrderDeliveryItemSerialActions->delete($salesOrderDeliveryItemSerial);
            }

            foreach ($data->serials as $serial) {
                if ($serial['id']) {
                    $salesOrderDeliveryItemSerial = $salesOrderDeliveryItem->serials()->findOrFail($serial['id']);
                    $dto = new SalesOrderDeliveryItemSerialUpdateDTO(
                        serial: $serial['serial'],
                    );

                    $this->salesOrderDeliveryItemSerialActions->update($salesOrderDeliveryItemSerial, $dto);
                } else {
                    $dto = new SalesOrderDeliveryItemSerialCreateDTO(
                        companyId: $salesOrderDeliveryItem->company_id,
                        branchId: $salesOrderDeliveryItem->branch_id,
                        salesOrderDeliveryId: $salesOrderDeliveryItem->sales_order_delivery_id,
                        salesOrderDeliveryItemId: $salesOrderDeliveryItem->id,
                        serial: $serial['serial'],
                    );

                    $this->salesOrderDeliveryItemSerialActions->create($dto);
                }
            }

            if ($updateParent) {
                SalesOrderDeliveryActions::updateSummary($salesOrderDeliveryItem->salesOrderDelivery);
            }

            $this->flushCache();

            return $salesOrderDeliveryItem->refresh()->load(self::DETAIL_EAGER_LOADS);
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(SalesOrderDeliveryItem $salesOrderDeliveryItem, bool $updateParent): bool
    {
        $timer_start = microtime(true);

        try {
            // Design §2 S-B delete guard: the cost snapshot of a sales return line is
            // copied from the delivery line it points at, so a referenced delivery line
            // may never disappear (also closes the document-guard bypass through edit).
            if ($salesOrderDeliveryItem->returnItems()->exists()) {
                throw new Exception('Sales order delivery item cannot be deleted because it is referenced by sales return items.');
            }

            $salesOrderDelivery = $salesOrderDeliveryItem->salesOrderDelivery;

            $stockTransaction = $salesOrderDeliveryItem->stockTransaction;
            if ($stockTransaction) {
                $this->stockTransactionActions->delete($stockTransaction);
            }

            foreach ($salesOrderDeliveryItem->serials as $salesOrderDeliveryItemSerial) {
                $this->salesOrderDeliveryItemSerialActions->delete($salesOrderDeliveryItemSerial);
            }

            $result = $salesOrderDeliveryItem->delete();

            if ($updateParent) {
                SalesOrderDeliveryActions::updateSummary($salesOrderDelivery);
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

    private function matchSalesOrderItem(SalesOrderDelivery $salesOrderDelivery, int $productId): ?SalesOrderItem
    {
        if (! $salesOrderDelivery->sales_order_id || ! $productId) {
            return null;
        }

        return SalesOrderItem::query()
            ->where('sales_order_id', $salesOrderDelivery->sales_order_id)
            ->where('product_id', $productId)
            ->orderBy('id', 'asc')
            ->first();
    }

    /**
     * Latest known NET cost for the product (design §1.5 cost resolver):
     * most recent purchase_invoice_items.base_unit_cogs (invoice date desc, id desc,
     * company-scoped), fallback most recent purchase_order_items.base_unit_cogs,
     * fallback 0.
     */
    private function resolveBaseUnitCogs(int $companyId, int $productId): float
    {
        if (! $productId) {
            return 0.0;
        }

        $latestInvoiceItemCogs = PurchaseInvoiceItem::query()
            ->join('purchase_invoices', 'purchase_invoices.id', '=', 'purchase_invoice_items.purchase_invoice_id')
            ->whereNull('purchase_invoices.deleted_at')
            ->where('purchase_invoice_items.company_id', $companyId)
            ->where('purchase_invoice_items.product_id', $productId)
            ->orderBy('purchase_invoices.date', 'desc')
            ->orderBy('purchase_invoice_items.id', 'desc')
            ->value('purchase_invoice_items.base_unit_cogs');

        if (! is_null($latestInvoiceItemCogs)) {
            return (float) $latestInvoiceItemCogs;
        }

        $latestOrderItemCogs = PurchaseOrderItem::query()
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
            ->whereNull('purchase_orders.deleted_at')
            ->where('purchase_order_items.company_id', $companyId)
            ->where('purchase_order_items.product_id', $productId)
            ->orderBy('purchase_orders.date', 'desc')
            ->orderBy('purchase_order_items.id', 'desc')
            ->value('purchase_order_items.base_unit_cogs');

        if (! is_null($latestOrderItemCogs)) {
            return (float) $latestOrderItemCogs;
        }

        return 0.0;
    }
}
