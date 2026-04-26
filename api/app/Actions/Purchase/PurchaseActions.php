<?php

namespace App\Actions\Purchase;

use App\Actions\PurchaseAdditionalCost\PurchaseAdditionalCostActions;
use App\Actions\PurchaseGlobalDiscount\PurchaseGlobalDiscountActions;
use App\Actions\PurchaseItem\PurchaseItemActions;
use App\Actions\PurchaseReceipt\PurchaseReceiptActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\PurchaseDirectCreateDTO;
use App\DTOs\PurchaseDirectUpdateDTO;
use App\DTOs\PurchaseGlobalDiscountCreateDTO;
use App\DTOs\PurchaseGlobalDiscountUpdateDTO;
use App\DTOs\PurchaseItemCreateDTO;
use App\DTOs\PurchaseItemUpdateDTO;
use App\DTOs\PurchaseManualCreateDTO;
use App\DTOs\PurchaseManualUpdateDTO;
use App\DTOs\PurchaseReceiptCreateDTO;
use App\DTOs\PurchaseReceiptUpdateDTO;
use App\Enums\DiscountTypeEnum;
use App\Enums\PurchaseReceiptModeEnum;
use App\Helpers\TimezoneHelper;
use App\Models\Company;
use App\Models\Purchase;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'supplier',
        'purchaseOrder',
    ];

    private const DETAIL_EAGER_LOADS = [
        'company',
        'branch',
        'supplier',
        'purchaseOrder.supplier',
        'items.purchaseOrderItem.purchaseOrder.supplier',
        'items.productUnit.unit',
        'items.productUnit.product.category',
        'items.productUnit.product.brand',
        'items.productUnit.product.baseProductUnit.unit',
        'items.productUnit.product.images',
        'items.productUnit.product.mainImage',
        'items.vatProfile',
        'items.productUnitPriceDiscounts',
        'items.subtotalDiscounts',
        'globalDiscounts',
        'additionalCosts.category',
        'additionalCosts.paidImmediatelyCashAccount',
        'additionalCosts.payments.cashAccount',
        'payments.cashAccount',
        'directReceipt.supplier',
        'directReceipt.warehouse',
        'manualReceipts.supplier',
        'manualReceipts.warehouse',
        'manualReceipts.items.purchaseItem',
        'manualReceipts.items.productUnit.unit',
        'manualReceipts.items.productUnit.product.category',
        'manualReceipts.items.productUnit.product.brand',
        'manualReceipts.items.productUnit.product.baseProductUnit.unit',
        'manualReceipts.items.productUnit.product.images',
        'manualReceipts.items.productUnit.product.mainImage',
        'manualReceipts.items.serials',
        'purchaseOrderDownPaymentAllocations.purchaseOrderDownPayment',
        'purchaseReturnAllocations.purchaseReturn',
        'purchaseReturns.supplier',
    ];

    public function __construct(
        private readonly PurchaseItemActions $purchaseItemActions,
        private readonly PurchaseGlobalDiscountActions $purchaseGlobalDiscountActions,
        private readonly PurchaseAdditionalCostActions $purchaseAdditionalCostActions,
        private readonly PurchaseReceiptActions $purchaseReceiptActions,
    ) {
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
        $query = Purchase::select('purchases.*');

        if ($execute?->pagination) {
            $query->with(self::DETAIL_EAGER_LOADS);
        } else {
            $query->with(self::LIST_EAGER_LOADS);
        }

        $query->join('companies', 'companies.id', '=', 'purchases.company_id')
            ->whereCompanyId('purchases', $companyId)
            ->whereBranchId('purchases', $branchId)
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
                $query->where(function ($query) use ($search) {
                    $query->where('purchases.code', 'like', '%'.$search.'%')
                        ->orWhere('purchases.tax_invoice_number', 'like', '%'.$search.'%')
                        ->orWhere('purchases.remarks', 'like', '%'.$search.'%');
                });
            }

            if ($startDate) {
                $query->where('purchases.date', '>=', TimezoneHelper::convertToUTC($startDate));
            }

            if ($endDate) {
                $query->where('purchases.date', '<=', TimezoneHelper::convertToUTC($endDate));
            }

            if ($supplierId) {
                $query->where('purchases.supplier_id', $supplierId);
            }
        });

        $query->orderBy('purchases.date', 'desc')
            ->orderBy('purchases.id', 'asc');

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

                $cacheKey = 'read_any_purchase_'.implode('_', $cacheParams);

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

    public function read(Purchase $purchase): Purchase
    {
        return $purchase->load(self::DETAIL_EAGER_LOADS);
    }

    public function generateUniqueCode(int $companyId, string $code, ?int $exceptId): string
    {
        if ($code != Config::get('dcslab.KEYWORDS.AUTO')) return $code;

        $company = Company::find($companyId);

        $tryCount = 0;
        do {
            $count = $company->purchases()->withTrashed()->count() + 1 + $tryCount;
            $code = 'PU'.str_pad($count, 5, '0', STR_PAD_LEFT);
            $tryCount++;
        } while (! $this->isUniqueCode($companyId, $code, $exceptId));

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = Purchase::where('company_id', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    private function generateDate(string $date): string
    {
        if ($date == config('dcslab.KEYWORDS.AUTO')) {
            $nowLocal = now(TimezoneHelper::getUserTimezone())->toDateTimeString();

            return TimezoneHelper::convertToUTC($nowLocal);
        }

        return TimezoneHelper::convertToUTC($date);
    }

    public function createDirect(PurchaseDirectCreateDTO $data): Purchase
    {
        $timer_start = microtime(true);

        try {
            $purchase = new Purchase();
            $purchase->company_id = $data->companyId;
            $purchase->branch_id = $data->branchId;
            $purchase->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $purchase->date = $this->generateDate($data->date);
            $purchase->due_days = $data->dueDays;
            $purchase->supplier_id = $data->supplierId;
            $purchase->purchase_order_id = $data->purchaseOrderId;
            $purchase->receipt_mode = PurchaseReceiptModeEnum::DIRECT;
            $purchase->tax_invoice_number = $data->taxInvoiceNumber;
            $purchase->tax_invoice_vat_base = $data->taxInvoiceVatBase;
            $purchase->tax_invoice_vat = $data->taxInvoiceVat;
            $purchase->remarks = $data->remarks;
            $purchase->is_posted = $data->isPosted;
            $purchase->additional_cost = $data->additionalCost;
            $purchase->rounding = $data->rounding;
            $purchase->save();

            for ($i = 0; $i < count($data->items); $i++) {
                $dto = new PurchaseItemCreateDTO(
                    companyId: $purchase->company_id,
                    branchId: $purchase->branch_id,
                    purchaseId: $purchase->id,
                    purchaseOrderItemId: $data->items[$i]['purchase_order_item_id'],
                    qty: $data->items[$i]['qty'],
                    productUnitId: $data->items[$i]['product_unit_id'],
                    productUnitConversionValue: $data->items[$i]['product_unit_conversion_value'],
                    productUnitPrice: $data->items[$i]['product_unit_price'],
                    productUnitIsPriceIncludeVat: $data->items[$i]['product_unit_is_price_include_vat'],
                    productUnitPriceDiscounts: $data->items[$i]['product_unit_price_discounts'],
                    subtotalDiscounts: $data->items[$i]['subtotal_discounts'],
                    vatProfileId: $data->items[$i]['vat_profile_id'],
                    vatRate: $data->items[$i]['vat_rate'],
                    vatBaseNumerator: $data->items[$i]['vat_base_numerator'],
                    vatBaseDenominator: $data->items[$i]['vat_base_denominator'],
                    remarks: $data->items[$i]['remarks'],
                );

                $result = $this->purchaseItemActions->create($dto, false);
                $data->items[$i]['id'] = $result->id;
            }

            $items = [];
            foreach ($data->items as $item) {
                $items[] = [
                    'purchase_item_id' => $item['id'],
                    'qty' => $item['qty'],
                    'product_unit_id' => $item['product_unit_id'],
                    'product_unit_conversion_value' => $item['product_unit_conversion_value'],
                    'remarks' => $item['remarks'],
                    'serials' => $item['serials'],
                ];
            }

            $dto = new PurchaseReceiptCreateDTO(
                companyId: $purchase->company_id,
                branchId: $purchase->branch_id,
                supplierId: $purchase->supplier_id,
                purchaseId: $purchase->id,
                isFromDirectPurchase: true,
                code: config('dcslab.KEYWORDS.AUTO'),
                date: $purchase->date,
                warehouseId: $data->directReceiptWarehouseId,
                remarks: $purchase->remarks,
                isPosted: $purchase->is_posted,
                items: $items,
            );

            $this->purchaseReceiptActions->create($dto, false);

            foreach ($data->globalDiscounts as $globalDiscount) {
                $dto = new PurchaseGlobalDiscountCreateDTO(
                    companyId: $purchase->company_id,
                    branchId: $purchase->branch_id,
                    purchaseId: $purchase->id,
                    sequence: $globalDiscount['sequence'],
                    discountType: $globalDiscount['discount_type'],
                    discountValue: $globalDiscount['discount_value'],
                );

                $this->purchaseGlobalDiscountActions->create($dto);
            }

            foreach ($data->additionalCosts as $additionalCost) {
                $this->purchaseAdditionalCostActions->create([
                    'company_id' => $purchase->company_id,
                    'branch_id' => $purchase->branch_id,
                    'purchase_id' => $purchase->id,
                    'purchase_additional_cost_category_id' => $additionalCost['purchase_additional_cost_category_id'],
                    'code' => $additionalCost['code'],
                    'date' => $additionalCost['date'],
                    'due_days' => $additionalCost['due_days'],
                    'paid_immediately_cash_account_id' => $additionalCost['paid_immediately_cash_account_id'],
                    'amount_paid_immediately' => $additionalCost['amount_paid_immediately'],
                    'amount_payable' => $additionalCost['amount_payable'],
                    'remarks' => $additionalCost['remarks'],
                ]);
            }

            self::updateSummary($purchase);

            $this->flushCache();

            return $purchase;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function createManual(PurchaseManualCreateDTO $data): Purchase
    {
        $timer_start = microtime(true);

        try {
            $purchase = new Purchase();
            $purchase->company_id = $data->companyId;
            $purchase->branch_id = $data->branchId;
            $purchase->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $purchase->date = $this->generateDate($data->date);
            $purchase->due_days = $data->dueDays;
            $purchase->supplier_id = $data->supplierId;
            $purchase->purchase_order_id = $data->purchaseOrderId;
            $purchase->receipt_mode = PurchaseReceiptModeEnum::MANUAL;
            $purchase->tax_invoice_number = $data->taxInvoiceNumber;
            $purchase->tax_invoice_vat_base = $data->taxInvoiceVatBase;
            $purchase->tax_invoice_vat = $data->taxInvoiceVat;
            $purchase->remarks = $data->remarks;
            $purchase->is_posted = $data->isPosted;
            $purchase->additional_cost = $data->additionalCost;
            $purchase->rounding = $data->rounding;
            $purchase->save();

            foreach ($data->items as $item) {
                $dto = new PurchaseItemCreateDTO(
                    companyId: $purchase->company_id,
                    branchId: $purchase->branch_id,
                    purchaseId: $purchase->id,
                    purchaseOrderItemId: $item['purchase_order_item_id'],
                    qty: $item['qty'],
                    productUnitId: $item['product_unit_id'],
                    productUnitConversionValue: $item['product_unit_conversion_value'],
                    productUnitPrice: $item['product_unit_price'],
                    productUnitIsPriceIncludeVat: $item['product_unit_is_price_include_vat'],
                    productUnitPriceDiscounts: $item['product_unit_price_discounts'],
                    subtotalDiscounts: $item['subtotal_discounts'],
                    vatProfileId: $item['vat_profile_id'],
                    vatRate: $item['vat_rate'],
                    vatBaseNumerator: $item['vat_base_numerator'],
                    vatBaseDenominator: $item['vat_base_denominator'],
                    remarks: $item['remarks'],
                );

                $this->purchaseItemActions->create($dto, false);
            }

            foreach ($data->globalDiscounts as $globalDiscount) {
                $dto = new PurchaseGlobalDiscountCreateDTO(
                    companyId: $purchase->company_id,
                    branchId: $purchase->branch_id,
                    purchaseId: $purchase->id,
                    sequence: $globalDiscount['sequence'],
                    discountType: $globalDiscount['discount_type'],
                    discountValue: $globalDiscount['discount_value'],
                );

                $this->purchaseGlobalDiscountActions->create($dto);
            }

            foreach ($data->additionalCosts as $additionalCost) {
                $this->purchaseAdditionalCostActions->create([
                    'company_id' => $purchase->company_id,
                    'branch_id' => $purchase->branch_id,
                    'purchase_id' => $purchase->id,
                    'purchase_additional_cost_category_id' => $additionalCost['purchase_additional_cost_category_id'],
                    'code' => $additionalCost['code'],
                    'date' => $additionalCost['date'],
                    'due_days' => $additionalCost['due_days'],
                    'paid_immediately_cash_account_id' => $additionalCost['paid_immediately_cash_account_id'],
                    'amount_paid_immediately' => $additionalCost['amount_paid_immediately'],
                    'amount_payable' => $additionalCost['amount_payable'],
                    'remarks' => $additionalCost['remarks'],
                ]);
            }

            self::updateSummary($purchase);

            $this->flushCache();

            return $purchase;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function updateDirect(Purchase $purchase, PurchaseDirectUpdateDTO $data): Purchase
    {
        $timer_start = microtime(true);

        try {
            $purchase->code = $this->generateUniqueCode($purchase->company_id, $data->code, $purchase->id);
            $purchase->date = $this->generateDate($data->date);
            $purchase->due_days = $data->dueDays;
            $purchase->supplier_id = $data->supplierId;
            $purchase->purchase_order_id = $data->purchaseOrderId;
            $purchase->receipt_mode = PurchaseReceiptModeEnum::DIRECT;
            $purchase->tax_invoice_number = $data->taxInvoiceNumber;
            $purchase->tax_invoice_vat_base = $data->taxInvoiceVatBase;
            $purchase->tax_invoice_vat = $data->taxInvoiceVat;
            $purchase->remarks = $data->remarks;
            $purchase->is_posted = $data->isPosted;
            $purchase->additional_cost = $data->additionalCost;
            $purchase->rounding = $data->rounding;
            $purchase->save();

            foreach ($data->deleteItemIds as $deleteId) {
                $purchaseItem = $purchase->items()->findOrFail($deleteId);
                $this->purchaseItemActions->delete($purchaseItem, false);
            }

            for ($i = 0; $i < count($data->items); $i++) {
                if (! empty($data->items[$i]['id'])) {
                    $purchaseItem = $purchase->items()->findOrFail($data->items[$i]['id']);
                    $dto = new PurchaseItemUpdateDTO(
                        purchaseOrderItemId: $data->items[$i]['purchase_order_item_id'],
                        qty: $data->items[$i]['qty'],
                        productUnitId: $data->items[$i]['product_unit_id'],
                        productUnitConversionValue: $data->items[$i]['product_unit_conversion_value'],
                        productUnitPrice: $data->items[$i]['product_unit_price'],
                        productUnitIsPriceIncludeVat: $data->items[$i]['product_unit_is_price_include_vat'],
                        deleteProductUnitPriceDiscountIds: $data->items[$i]['delete_product_unit_price_discount_ids'],
                        productUnitPriceDiscounts: $data->items[$i]['product_unit_price_discounts'],
                        deleteSubtotalDiscountIds: $data->items[$i]['delete_subtotal_discount_ids'],
                        subtotalDiscounts: $data->items[$i]['subtotal_discounts'],
                        vatProfileId: $data->items[$i]['vat_profile_id'],
                        vatRate: $data->items[$i]['vat_rate'],
                        vatBaseNumerator: $data->items[$i]['vat_base_numerator'],
                        vatBaseDenominator: $data->items[$i]['vat_base_denominator'],
                        remarks: $data->items[$i]['remarks'],
                    );

                    $result = $this->purchaseItemActions->update($purchaseItem, $dto, false);
                } else {
                    $dto = new PurchaseItemCreateDTO(
                        companyId: $purchase->company_id,
                        branchId: $purchase->branch_id,
                        purchaseId: $purchase->id,
                        purchaseOrderItemId: $data->items[$i]['purchase_order_item_id'],
                        qty: $data->items[$i]['qty'],
                        productUnitId: $data->items[$i]['product_unit_id'],
                        productUnitConversionValue: $data->items[$i]['product_unit_conversion_value'],
                        productUnitPrice: $data->items[$i]['product_unit_price'],
                        productUnitIsPriceIncludeVat: $data->items[$i]['product_unit_is_price_include_vat'],
                        productUnitPriceDiscounts: $data->items[$i]['product_unit_price_discounts'],
                        subtotalDiscounts: $data->items[$i]['subtotal_discounts'],
                        vatProfileId: $data->items[$i]['vat_profile_id'],
                        vatRate: $data->items[$i]['vat_rate'],
                        vatBaseNumerator: $data->items[$i]['vat_base_numerator'],
                        vatBaseDenominator: $data->items[$i]['vat_base_denominator'],
                        remarks: $data->items[$i]['remarks'],
                    );

                    $result = $this->purchaseItemActions->create($dto, false);
                }

                $data->items[$i]['id'] = $result->id;
            }

            $items = [];
            foreach ($data->items as $item) {
                $items[] = [
                    'purchase_item_id' => $item['id'],
                    'qty' => $item['qty'],
                    'product_unit_id' => $item['product_unit_id'],
                    'product_unit_conversion_value' => $item['product_unit_conversion_value'],
                    'remarks' => $item['remarks'],
                    'serials' => $item['serials'],
                ];
            }

            $purchaseReceipt = $purchase->directReceipt;
            if ($purchaseReceipt) {
                $dto = new PurchaseReceiptUpdateDTO(
                    supplierId: $purchase->supplier_id,
                    purchaseId: $purchase->id,
                    code: $purchaseReceipt->code,
                    date: $purchase->date,
                    isFromDirectPurchase: true,
                    warehouseId: $data->directReceiptWarehouseId,
                    remarks: $purchase->remarks,
                    isPosted: $purchase->is_posted,
                    items: $items,
                );

                $this->purchaseReceiptActions->update($purchaseReceipt, $dto, false);
            } else {
                $dto = new PurchaseReceiptCreateDTO(
                    companyId: $purchase->company_id,
                    branchId: $purchase->branch_id,
                    supplierId: $purchase->supplier_id,
                    purchaseId: $purchase->id,
                    isFromDirectPurchase: true,
                    code: config('dcslab.KEYWORDS.AUTO'),
                    date: $purchase->date,
                    warehouseId: $data->directReceiptWarehouseId,
                    remarks: $purchase->remarks,
                    isPosted: $purchase->is_posted,
                    items: $items,
                );

                $this->purchaseReceiptActions->create($dto, false);
            }

            foreach ($data->deleteGlobalDiscountIds as $deleteId) {
                $purchaseGlobalDiscount = $purchase->globalDiscounts()->findOrFail($deleteId);
                $this->purchaseGlobalDiscountActions->delete($purchaseGlobalDiscount);
            }

            foreach ($data->globalDiscounts as $globalDiscount) {
                if (! empty($globalDiscount['id'])) {
                    $purchaseGlobalDiscount = $purchase->globalDiscounts()->findOrFail($globalDiscount['id']);
                    $dto = new PurchaseGlobalDiscountUpdateDTO(
                        sequence: $globalDiscount['sequence'],
                        discountType: $globalDiscount['discount_type'],
                        discountValue: $globalDiscount['discount_value'],
                    );

                    $this->purchaseGlobalDiscountActions->update($purchaseGlobalDiscount, $dto);
                } else {
                    $dto = new PurchaseGlobalDiscountCreateDTO(
                        companyId: $purchase->company_id,
                        branchId: $purchase->branch_id,
                        purchaseId: $purchase->id,
                        sequence: $globalDiscount['sequence'],
                        discountType: $globalDiscount['discount_type'],
                        discountValue: $globalDiscount['discount_value'],
                    );

                    $this->purchaseGlobalDiscountActions->create($dto);
                }
            }

            foreach ($data->deleteAdditionalCostIds as $deleteId) {
                $purchaseAdditionalCost = $purchase->additionalCosts()->findOrFail($deleteId);
                $this->purchaseAdditionalCostActions->delete($purchaseAdditionalCost);
            }

            foreach ($data->additionalCosts as $additionalCost) {
                if (! empty($additionalCost['id'])) {
                    $purchaseAdditionalCost = $purchase->additionalCosts()->findOrFail($additionalCost['id']);

                    $this->purchaseAdditionalCostActions->update($purchaseAdditionalCost, [
                        'purchase_additional_cost_category_id' => $additionalCost['purchase_additional_cost_category_id'],
                        'code' => $additionalCost['code'],
                        'date' => $additionalCost['date'],
                        'due_days' => $additionalCost['due_days'],
                        'paid_immediately_cash_account_id' => $additionalCost['paid_immediately_cash_account_id'],
                        'amount_paid_immediately' => $additionalCost['amount_paid_immediately'],
                        'amount_payable' => $additionalCost['amount_payable'],
                        'remarks' => $additionalCost['remarks'],
                    ]);
                } else {
                    $this->purchaseAdditionalCostActions->create([
                        'company_id' => $purchase->company_id,
                        'branch_id' => $purchase->branch_id,
                        'purchase_id' => $purchase->id,
                        'purchase_additional_cost_category_id' => $additionalCost['purchase_additional_cost_category_id'],
                        'code' => $additionalCost['code'],
                        'date' => $additionalCost['date'],
                        'due_days' => $additionalCost['due_days'],
                        'paid_immediately_cash_account_id' => $additionalCost['paid_immediately_cash_account_id'],
                        'amount_paid_immediately' => $additionalCost['amount_paid_immediately'],
                        'amount_payable' => $additionalCost['amount_payable'],
                        'remarks' => $additionalCost['remarks'],
                    ]);
                }
            }

            self::updateSummary($purchase);

            $this->flushCache();

            return $purchase;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function updateManual(Purchase $purchase, PurchaseManualUpdateDTO $data): Purchase
    {
        $timer_start = microtime(true);

        try {
            $purchase->code = $this->generateUniqueCode($purchase->company_id, $data->code, $purchase->id);
            $purchase->date = $this->generateDate($data->date);
            $purchase->due_days = $data->dueDays;
            $purchase->supplier_id = $data->supplierId;
            $purchase->purchase_order_id = $data->purchaseOrderId;
            $purchase->receipt_mode = PurchaseReceiptModeEnum::MANUAL;
            $purchase->tax_invoice_number = $data->taxInvoiceNumber;
            $purchase->tax_invoice_vat_base = $data->taxInvoiceVatBase;
            $purchase->tax_invoice_vat = $data->taxInvoiceVat;
            $purchase->remarks = $data->remarks;
            $purchase->is_posted = $data->isPosted;
            $purchase->additional_cost = $data->additionalCost;
            $purchase->rounding = $data->rounding;
            $purchase->save();

            foreach ($data->deleteItemIds as $deleteId) {
                $purchaseItem = $purchase->items()->findOrFail($deleteId);
                $this->purchaseItemActions->delete($purchaseItem, false);
            }

            foreach ($data->items as $item) {
                if (! empty($item['id'])) {
                    $purchaseItem = $purchase->items()->findOrFail($item['id']);
                    $dto = new PurchaseItemUpdateDTO(
                        purchaseOrderItemId: $item['purchase_order_item_id'],
                        qty: $item['qty'],
                        productUnitId: $item['product_unit_id'],
                        productUnitConversionValue: $item['product_unit_conversion_value'],
                        productUnitPrice: $item['product_unit_price'],
                        productUnitIsPriceIncludeVat: $item['product_unit_is_price_include_vat'],
                        deleteProductUnitPriceDiscountIds: $item['delete_product_unit_price_discount_ids'],
                        productUnitPriceDiscounts: $item['product_unit_price_discounts'],
                        deleteSubtotalDiscountIds: $item['delete_subtotal_discount_ids'],
                        subtotalDiscounts: $item['subtotal_discounts'],
                        vatProfileId: $item['vat_profile_id'],
                        vatRate: $item['vat_rate'],
                        vatBaseNumerator: $item['vat_base_numerator'],
                        vatBaseDenominator: $item['vat_base_denominator'],
                        remarks: $item['remarks'],
                    );

                    $this->purchaseItemActions->update($purchaseItem, $dto, false);
                } else {
                    $dto = new PurchaseItemCreateDTO(
                        companyId: $purchase->company_id,
                        branchId: $purchase->branch_id,
                        purchaseId: $purchase->id,
                        purchaseOrderItemId: $item['purchase_order_item_id'],
                        qty: $item['qty'],
                        productUnitId: $item['product_unit_id'],
                        productUnitConversionValue: $item['product_unit_conversion_value'],
                        productUnitPrice: $item['product_unit_price'],
                        productUnitIsPriceIncludeVat: $item['product_unit_is_price_include_vat'],
                        productUnitPriceDiscounts: $item['product_unit_price_discounts'],
                        subtotalDiscounts: $item['subtotal_discounts'],
                        vatProfileId: $item['vat_profile_id'],
                        vatRate: $item['vat_rate'],
                        vatBaseNumerator: $item['vat_base_numerator'],
                        vatBaseDenominator: $item['vat_base_denominator'],
                        remarks: $item['remarks'],
                    );

                    $this->purchaseItemActions->create($dto, false);
                }
            }

            foreach ($data->deleteGlobalDiscountIds as $deleteId) {
                $purchaseGlobalDiscount = $purchase->globalDiscounts()->findOrFail($deleteId);
                $this->purchaseGlobalDiscountActions->delete($purchaseGlobalDiscount);
            }

            foreach ($data->globalDiscounts as $globalDiscount) {
                if (! empty($globalDiscount['id'])) {
                    $purchaseGlobalDiscount = $purchase->globalDiscounts()->findOrFail($globalDiscount['id']);
                    $dto = new PurchaseGlobalDiscountUpdateDTO(
                        sequence: $globalDiscount['sequence'],
                        discountType: $globalDiscount['discount_type'],
                        discountValue: $globalDiscount['discount_value'],
                    );

                    $this->purchaseGlobalDiscountActions->update($purchaseGlobalDiscount, $dto);
                } else {
                    $dto = new PurchaseGlobalDiscountCreateDTO(
                        companyId: $purchase->company_id,
                        branchId: $purchase->branch_id,
                        purchaseId: $purchase->id,
                        sequence: $globalDiscount['sequence'],
                        discountType: $globalDiscount['discount_type'],
                        discountValue: $globalDiscount['discount_value'],
                    );

                    $this->purchaseGlobalDiscountActions->create($dto);
                }
            }

            foreach ($data->deleteAdditionalCostIds as $deleteId) {
                $purchaseAdditionalCost = $purchase->additionalCosts()->findOrFail($deleteId);
                $this->purchaseAdditionalCostActions->delete($purchaseAdditionalCost);
            }

            foreach ($data->additionalCosts as $additionalCost) {
                if (! empty($additionalCost['id'])) {
                    $purchaseAdditionalCost = $purchase->additionalCosts()->findOrFail($additionalCost['id']);

                    $this->purchaseAdditionalCostActions->update($purchaseAdditionalCost, [
                        'purchase_additional_cost_category_id' => $additionalCost['purchase_additional_cost_category_id'],
                        'code' => $additionalCost['code'],
                        'date' => $additionalCost['date'],
                        'due_days' => $additionalCost['due_days'],
                        'paid_immediately_cash_account_id' => $additionalCost['paid_immediately_cash_account_id'],
                        'amount_paid_immediately' => $additionalCost['amount_paid_immediately'],
                        'amount_payable' => $additionalCost['amount_payable'],
                        'remarks' => $additionalCost['remarks'],
                    ]);
                } else {
                    $this->purchaseAdditionalCostActions->create([
                        'company_id' => $purchase->company_id,
                        'branch_id' => $purchase->branch_id,
                        'purchase_id' => $purchase->id,
                        'purchase_additional_cost_category_id' => $additionalCost['purchase_additional_cost_category_id'],
                        'code' => $additionalCost['code'],
                        'date' => $additionalCost['date'],
                        'due_days' => $additionalCost['due_days'],
                        'paid_immediately_cash_account_id' => $additionalCost['paid_immediately_cash_account_id'],
                        'amount_paid_immediately' => $additionalCost['amount_paid_immediately'],
                        'amount_payable' => $additionalCost['amount_payable'],
                        'remarks' => $additionalCost['remarks'],
                    ]);
                }
            }

            self::updateSummary($purchase);

            $this->flushCache();

            return $purchase;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public static function updateSummary(Purchase $purchase): void
    {
        $purchase->refresh();

        $purchase->item_total_before_global_discount = (float) $purchase->items()->sum('subtotal_after_discount');
        $purchase->global_discount = (function () use ($purchase) {
            $beforeDiscount = (float) $purchase->item_total_before_global_discount;
            $afterDiscount = $beforeDiscount;

            foreach ($purchase->globalDiscounts()->orderBy('sequence')->orderBy('id')->get() as $globalDiscount) {
                $discountType = $globalDiscount->discount_type instanceof DiscountTypeEnum
                    ? $globalDiscount->discount_type
                    : DiscountTypeEnum::resolveToEnum($globalDiscount->discount_type);
                $discountValue = (float) $globalDiscount->discount_value;

                if ($discountType === DiscountTypeEnum::PERCENTAGE) {
                    $afterDiscount -= $afterDiscount * $discountValue / 100;
                } else {
                    $afterDiscount -= $discountValue;
                }

                if ($afterDiscount < 0) {
                    $afterDiscount = 0;
                }
            }

            return $beforeDiscount - $afterDiscount;
        })();

        foreach ($purchase->items as $purchaseItem) {
            $purchaseItem->global_discount = (function () use ($purchaseItem, $purchase) {
                $itemTotalBeforeGlobalDiscount = (float) $purchase->item_total_before_global_discount;
                $purchaseGlobalDiscount = (float) $purchase->global_discount;

                if ($itemTotalBeforeGlobalDiscount <= 0 || $purchaseGlobalDiscount <= 0) return 0;

                $value = ((float) $purchaseItem->subtotal_after_discount / $itemTotalBeforeGlobalDiscount) * $purchaseGlobalDiscount;

                return $value < 0 ? 0 : $value;
            })();
            $purchaseItem->subtotal_after_global_discount = $purchaseItem->subtotal_after_discount - $purchaseItem->global_discount;
            $purchaseItem->save();
        }

        $globalDiscountDifference = round((float) $purchase->global_discount - (float) $purchase->items()->sum('global_discount'), 8);
        if (abs($globalDiscountDifference) > 0.00000001) {
            $lastGlobalDiscountPurchaseItem = $purchase->items
                ->filter(fn ($purchaseItem) => (float) $purchaseItem->subtotal_after_discount > 0)
                ->last();

            if ($lastGlobalDiscountPurchaseItem) {
                $lastGlobalDiscountPurchaseItem->global_discount = max(
                    0,
                    (float) $lastGlobalDiscountPurchaseItem->global_discount + $globalDiscountDifference
                );
                $lastGlobalDiscountPurchaseItem->subtotal_after_global_discount = max(
                    0,
                    (float) $lastGlobalDiscountPurchaseItem->subtotal_after_discount - (float) $lastGlobalDiscountPurchaseItem->global_discount
                );
                $lastGlobalDiscountPurchaseItem->save();
            }
        }

        $purchase->item_total_after_global_discount = (float) $purchase->items()->sum('subtotal_after_global_discount');

        foreach ($purchase->items as $purchaseItem) {
            $purchaseItem->vat_base = (function () use ($purchaseItem) {
                $subtotalAfterGlobalDiscount = (float) $purchaseItem->subtotal_after_global_discount;
                $vatRate = (float) $purchaseItem->vat_rate;
                $vatBaseFactor = $purchaseItem->vat_base_denominator > 0
                    ? (float) $purchaseItem->vat_base_numerator / (float) $purchaseItem->vat_base_denominator
                    : 0;

                if ($subtotalAfterGlobalDiscount <= 0 || $vatRate <= 0 || $vatBaseFactor <= 0) return 0;

                if ($purchaseItem->product_unit_is_price_include_vat) {
                    $subtotalAfterGlobalDiscount = $subtotalAfterGlobalDiscount / (1 + ($vatRate / 100));
                }

                return $subtotalAfterGlobalDiscount * $vatBaseFactor;
            })();
            $purchaseItem->vat = (function () use ($purchaseItem) {
                $vatBase = (float) $purchaseItem->vat_base;
                $vatRate = (float) $purchaseItem->vat_rate;

                if ($vatBase <= 0 || $vatRate <= 0) return 0;

                $value = $vatBase * ($vatRate / 100);

                return $value < 0 ? 0 : $value;
            })();
            $purchaseItem->subtotal_after_vat = (function () use ($purchaseItem) {
                $subtotalAfterGlobalDiscount = (float) $purchaseItem->subtotal_after_global_discount;
                $vat = (float) $purchaseItem->vat;

                if ($purchaseItem->product_unit_is_price_include_vat) {
                    return $subtotalAfterGlobalDiscount;
                }

                return $subtotalAfterGlobalDiscount + $vat;
            })();
            $purchaseItem->save();
        }

        $purchase->vat_base = (float) $purchase->items()->sum('vat_base');
        $purchase->vat = (float) $purchase->items()->sum('vat');
        $purchase->item_total_after_vat = (float) $purchase->items()->sum('subtotal_after_vat');
        $purchase->additional_cost = (float) $purchase->additionalCosts()->sum('amount_total');

        foreach ($purchase->items as $purchaseItem) {
            $purchaseItem->additional_cost = (function () use ($purchaseItem, $purchase) {
                $itemTotalAfterVat = (float) $purchase->item_total_after_vat;
                $purchaseAdditionalCost = (float) $purchase->additional_cost;

                if ($itemTotalAfterVat <= 0 || $purchaseAdditionalCost <= 0) return 0;

                return ((float) $purchaseItem->subtotal_after_vat / $itemTotalAfterVat) * $purchaseAdditionalCost;
            })();
            $purchaseItem->rounding = (function () use ($purchaseItem, $purchase) {
                $purchaseItemSubtotalAfterVat = (float) $purchaseItem->subtotal_after_vat;
                $itemTotalAfterVat = (float) $purchase->item_total_after_vat;
                $purchaseRounding = (float) $purchase->rounding;

                if ($itemTotalAfterVat <= 0 || $purchaseRounding == 0 || $purchaseItemSubtotalAfterVat <= 0) return 0;

                return ($purchaseItemSubtotalAfterVat / $itemTotalAfterVat) * $purchaseRounding;
            })();
            $purchaseItem->save();
        }

        $additionalCostDifference = round((float) $purchase->additional_cost - (float) $purchase->items()->sum('additional_cost'), 8);
        if (abs($additionalCostDifference) > 0.00000001) {
            $lastAdditionalCostPurchaseItem = $purchase->items
                ->filter(fn ($purchaseItem) => (float) $purchaseItem->subtotal_after_vat > 0)
                ->last();

            if ($lastAdditionalCostPurchaseItem) {
                $lastAdditionalCostPurchaseItem->additional_cost = max(
                    0,
                    (float) $lastAdditionalCostPurchaseItem->additional_cost + $additionalCostDifference
                );
                $lastAdditionalCostPurchaseItem->save();
            }
        }

        $roundingDifference = round((float) $purchase->rounding - (float) $purchase->items()->sum('rounding'), 8);
        if (abs($roundingDifference) > 0.00000001) {
            $lastRoundingPurchaseItem = $purchase->items
                ->filter(function ($purchaseItem) {
                    return ((float) $purchaseItem->subtotal_after_vat + (float) $purchaseItem->additional_cost) > 0;
                })
                ->last();

            if ($lastRoundingPurchaseItem) {
                $lastRoundingPurchaseItem->rounding = (float) $lastRoundingPurchaseItem->rounding + $roundingDifference;
                $lastRoundingPurchaseItem->amount_payable = (float) $lastRoundingPurchaseItem->subtotal_after_vat
                    + (float) $lastRoundingPurchaseItem->additional_cost
                    + (float) $lastRoundingPurchaseItem->rounding;
                $lastRoundingPurchaseItem->cogs = (function () use ($lastRoundingPurchaseItem) {
                    $qty = (float) $lastRoundingPurchaseItem->qty;
                    $amountPayable = (float) $lastRoundingPurchaseItem->amount_payable;

                    if ($qty <= 0 || $amountPayable <= 0) return 0;

                    return $amountPayable / $qty;
                })();
                $lastRoundingPurchaseItem->total_cogs = (function () use ($lastRoundingPurchaseItem) {
                    $qty = (float) $lastRoundingPurchaseItem->qty;
                    $cogs = (float) $lastRoundingPurchaseItem->cogs;

                    if ($qty <= 0 || $cogs <= 0) return 0;

                    return $qty * $cogs;
                })();
                $lastRoundingPurchaseItem->base_unit_cogs = (function () use ($lastRoundingPurchaseItem) {
                    $productUnitQtyBase = (float) $lastRoundingPurchaseItem->product_unit_qty_base;
                    $totalCogs = (float) $lastRoundingPurchaseItem->total_cogs;

                    if ($productUnitQtyBase <= 0 || $totalCogs <= 0) return 0;

                    return $totalCogs / $productUnitQtyBase;
                })();
                $lastRoundingPurchaseItem->save();
            }
        }

        foreach ($purchase->items as $purchaseItem) {
            $purchaseItem->amount_payable = (float) $purchaseItem->subtotal_after_vat
                + (float) $purchaseItem->additional_cost
                + (float) $purchaseItem->rounding;
            $purchaseItem->cogs = (function () use ($purchaseItem) {
                $qty = (float) $purchaseItem->qty;
                $amountPayable = (float) $purchaseItem->amount_payable;

                if ($qty <= 0 || $amountPayable <= 0) return 0;

                return $amountPayable / $qty;
            })();
            $purchaseItem->total_cogs = (function () use ($purchaseItem) {
                $qty = (float) $purchaseItem->qty;
                $cogs = (float) $purchaseItem->cogs;

                if ($qty <= 0 || $cogs <= 0) return 0;

                return $qty * $cogs;
            })();
            $purchaseItem->base_unit_cogs = (function () use ($purchaseItem) {
                $productUnitQtyBase = (float) $purchaseItem->product_unit_qty_base;
                $totalCogs = (float) $purchaseItem->total_cogs;

                if ($productUnitQtyBase <= 0 || $totalCogs <= 0) return 0;

                return $totalCogs / $productUnitQtyBase;
            })();
            $purchaseItem->save();
        }

        $purchase->amount_payable = (float) $purchase->items()->sum('amount_payable');
        $purchase->amount_paid_by_purchase_order_down_payment = (float) $purchase->purchaseOrderDownPaymentAllocations()->sum('amount');
        $purchase->amount_paid_by_purchase_return = (float) $purchase->purchaseReturnAllocations()->sum('amount');
        $purchase->amount_paid_total =
            $purchase->amount_paid_by_purchase_order_down_payment +
            $purchase->amount_paid_by_purchase_return +
            (float) $purchase->payments()->sum('amount');
        $purchase->amount_due = max(0, (float) $purchase->amount_payable - (float) $purchase->amount_paid_total);
        $purchase->is_paid_off = $purchase->amount_due == 0;

        foreach ($purchase->items as $purchaseItem) {
            $qtyTargetBase = (float) $purchaseItem->product_unit_qty_base;
            $qtyReceivedBase = (float) $purchaseItem->receiptItems()->sum('product_unit_qty_base');

            $purchaseItem->qty_received_base = $qtyReceivedBase;
            $purchaseItem->qty_outstanding_base = max($qtyTargetBase - $qtyReceivedBase, 0);
            $purchaseItem->qty_excess_base = max($qtyReceivedBase - $qtyTargetBase, 0);
            $purchaseItem->save();
        }

        $purchase->item_total_count = (function () use ($purchase) {
            return $purchase->items()->count();
        })();
        $purchase->item_matched_count = (function () use ($purchase) {
            $itemMatchedCount = 0;

            foreach ($purchase->items as $purchaseItem) {
                if (! $purchaseItem->receiptItems()->exists()) {
                    continue;
                }

                if ((float) $purchaseItem->qty_excess_base > 0) {
                    continue;
                }

                if ((float) $purchaseItem->qty_outstanding_base > 0) {
                    continue;
                }

                $itemMatchedCount++;
            }

            return $itemMatchedCount;
        })();
        $purchase->item_less_count = (function () use ($purchase) {
            $itemLessCount = 0;

            foreach ($purchase->items as $purchaseItem) {
                if (! $purchaseItem->receiptItems()->exists()) {
                    continue;
                }

                if ((float) $purchaseItem->qty_excess_base > 0) {
                    continue;
                }

                if ((float) $purchaseItem->qty_outstanding_base > 0) {
                    $itemLessCount++;
                }
            }

            return $itemLessCount;
        })();
        $purchase->item_more_count = (function () use ($purchase) {
            $itemMoreCount = 0;

            foreach ($purchase->items as $purchaseItem) {
                if (! $purchaseItem->receiptItems()->exists()) {
                    continue;
                }

                if ((float) $purchaseItem->qty_excess_base > 0) {
                    $itemMoreCount++;
                }
            }

            return $itemMoreCount;
        })();
        $purchase->item_unlinked_count = (function () use ($purchase) {
            $itemUnlinkedCount = 0;

            foreach ($purchase->items as $purchaseItem) {
                if (! $purchaseItem->receiptItems()->exists()) {
                    $itemUnlinkedCount++;
                }
            }

            return $itemUnlinkedCount;
        })();
        $purchase->progress_status = (function () use ($purchase) {
            if ($purchase->item_total_count === 0) {
                return 'unlinked';
            }

            if ($purchase->item_unlinked_count === $purchase->item_total_count) {
                return 'unlinked';
            }

            if ($purchase->item_matched_count === $purchase->item_total_count) {
                return 'matched';
            }

            return 'unmatched';
        })();

        $purchase->save();
    }

    public function delete(Purchase $purchase): bool
    {
        $timer_start = microtime(true);

        try {
            if (
                $purchase->payments()->exists()
                || $purchase->purchaseOrderDownPaymentAllocations()->exists()
                || $purchase->purchaseReturnAllocations()->exists()
                || $purchase->purchaseReturns()->exists()
            ) {
                throw new Exception('Purchase cannot be deleted because it already has related transactions.');
            }

            foreach ($purchase->items as $purchaseItem) {
                $this->purchaseItemActions->delete($purchaseItem, false);
            }

            foreach ($purchase->globalDiscounts as $purchaseGlobalDiscount) {
                $this->purchaseGlobalDiscountActions->delete($purchaseGlobalDiscount);
            }

            foreach ($purchase->receipts as $purchaseReceipt) {
                $this->purchaseReceiptActions->delete($purchaseReceipt, false);
            }

            $result = $purchase->delete();

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
