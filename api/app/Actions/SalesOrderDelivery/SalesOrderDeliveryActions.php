<?php

namespace App\Actions\SalesOrderDelivery;

use App\Actions\JournalEntry\JournalEntryActions;
use App\Actions\SalesOrder\SalesOrderActions;
use App\Actions\SalesOrderDeliveryCost\SalesOrderDeliveryCostActions;
use App\Actions\SalesOrderDeliveryItem\SalesOrderDeliveryItemActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\JournalEntryCreateDTO;
use App\DTOs\JournalEntryItemDTO;
use App\DTOs\JournalEntryUpdateDTO;
use App\DTOs\SalesOrderDeliveryCostCreateDTO;
use App\DTOs\SalesOrderDeliveryCostUpdateDTO;
use App\DTOs\SalesOrderDeliveryCreateDTO;
use App\DTOs\SalesOrderDeliveryItemCreateDTO;
use App\DTOs\SalesOrderDeliveryItemUpdateDTO;
use App\DTOs\SalesOrderDeliveryUpdateDTO;
use App\Enums\JournalEntryTypeEnum;
use App\Helpers\TimezoneHelper;
use App\Models\JournalEntry;
use App\Models\SalesOrderDelivery;
use App\Models\SalesReturnItem;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Config;

class SalesOrderDeliveryActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct(
        private readonly SalesOrderDeliveryItemActions $salesOrderDeliveryItemActions,
        private readonly SalesOrderDeliveryCostActions $salesOrderDeliveryCostActions,
        private readonly JournalEntryActions $journalEntryActions,
    ) {
    }

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'customer',
        'salesOrder.customer',
        'warehouse',
    ];

    private const DETAIL_EAGER_LOADS = [
        'company',
        'branch',
        'customer',
        'salesOrder.customer',
        'warehouse',
        'items.salesOrderItem',
        'items.productUnit.unit',
        'items.productUnit.product.category',
        'items.productUnit.product.brand',
        'items.productUnit.product.baseProductUnit.unit',
        'items.productUnit.product.images',
        'items.productUnit.product.mainImage',
        'items.serials',
        'costs.cashAccount',
    ];

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,

        ?int $customerId,
        ?int $salesOrderId,
        ?string $startDate,
        ?string $endDate,
        ?int $warehouseId,
        ?bool $isPosted,

        ?ExecuteDTO $execute
    ) {
        $query = SalesOrderDelivery::select('sales_order_deliveries.*');

        if ($execute?->pagination) {
            $query->with(self::DETAIL_EAGER_LOADS);
        } else {
            $query->with(self::LIST_EAGER_LOADS);
        }

        $query
            ->join('companies', 'companies.id', '=', 'sales_order_deliveries.company_id')
            ->whereCompanyId('sales_order_deliveries', $companyId)
            ->whereBranchId('sales_order_deliveries', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $customerId,
            $salesOrderId,
            $startDate,
            $endDate,
            $warehouseId,
            $isPosted,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('sales_order_deliveries.code', 'like', '%'.$search.'%')
                        ->orWhere('sales_order_deliveries.remarks', 'like', '%'.$search.'%');
                });
            }

            if ($customerId) {
                $query->where('sales_order_deliveries.customer_id', $customerId);
            }

            if ($salesOrderId) {
                $query->where('sales_order_deliveries.sales_order_id', $salesOrderId);
            }

            if ($startDate) {
                $query->where('sales_order_deliveries.date', '>=', TimezoneHelper::convertToUTC($startDate));
            }

            if ($endDate) {
                $query->where('sales_order_deliveries.date', '<=', TimezoneHelper::convertToUTC($endDate));
            }

            if ($warehouseId) {
                $query->where('sales_order_deliveries.warehouse_id', $warehouseId);
            }

            if (! is_null($isPosted)) {
                $query->where('sales_order_deliveries.is_posted', $isPosted);
            }
        });

        $query->orderBy('sales_order_deliveries.date', 'desc')
            ->orderBy('sales_order_deliveries.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    $customerId ?? '[null]',
                    $salesOrderId ?? '[null]',
                    $startDate ?? '[null]',
                    $endDate ?? '[null]',
                    $warehouseId ?? '[null]',
                    is_null($isPosted) ? '[null]' : ($isPosted ? 'true' : 'false'),
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_sales_order_delivery_'.implode('_', $cacheParams);

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

    public function read(SalesOrderDelivery $salesOrderDelivery): SalesOrderDelivery
    {
        return $salesOrderDelivery->load(self::DETAIL_EAGER_LOADS);
    }

    public function generateUniqueCode(int $companyId, string $code, ?int $exceptId): string
    {
        if ($code != Config::get('dcslab.KEYWORDS.AUTO')) return $code;

        $tryCount = 0;
        do {
            $count = SalesOrderDelivery::withTrashed()->where('company_id', $companyId)->count() + 1 + $tryCount;
            $code = 'SDL'.str_pad($count, 5, '0', STR_PAD_LEFT);
            $tryCount++;
        } while (! $this->isUniqueCode($companyId, $code, $exceptId));

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = SalesOrderDelivery::where('company_id', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    private function generateDate($date): string
    {
        if ($date instanceof \DateTimeInterface) {
            return $date->format('Y-m-d H:i:s');
        }

        if ($date == config('dcslab.KEYWORDS.AUTO')) {
            $nowLocal = now(TimezoneHelper::getUserTimezone())->toDateTimeString();

            return TimezoneHelper::convertToUTC($nowLocal);
        }

        return TimezoneHelper::convertToUTC($date);
    }

    public function create(SalesOrderDeliveryCreateDTO $data): SalesOrderDelivery
    {
        $timer_start = microtime(true);

        try {
            $salesOrderDelivery = new SalesOrderDelivery();
            $salesOrderDelivery->company_id = $data->companyId;
            $salesOrderDelivery->branch_id = $data->branchId;
            $salesOrderDelivery->customer_id = $data->customerId;
            $salesOrderDelivery->sales_order_id = $data->salesOrderId;
            $salesOrderDelivery->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $salesOrderDelivery->date = $this->generateDate($data->date);
            $salesOrderDelivery->warehouse_id = $data->warehouseId;
            $salesOrderDelivery->remarks = $data->remarks;
            $salesOrderDelivery->is_posted = $data->isPosted;
            $salesOrderDelivery->save();

            foreach ($data->items as $item) {
                $dto = new SalesOrderDeliveryItemCreateDTO(
                    companyId: $salesOrderDelivery->company_id,
                    branchId: $salesOrderDelivery->branch_id,
                    salesOrderDeliveryId: $salesOrderDelivery->id,
                    qty: $item['qty'],
                    productUnitId: $item['product_unit_id'],
                    productUnitConversionValue: $item['product_unit_conversion_value'],
                    remarks: $item['remarks'],
                    serials: $item['serials'],
                );

                $this->salesOrderDeliveryItemActions->create($dto, false);
            }

            foreach ($data->costs as $cost) {
                $dto = new SalesOrderDeliveryCostCreateDTO(
                    companyId: $salesOrderDelivery->company_id,
                    branchId: $salesOrderDelivery->branch_id,
                    salesOrderDeliveryId: $salesOrderDelivery->id,
                    code: $cost['code'],
                    date: $cost['date'],
                    name: $cost['name'],
                    cashAccountId: $cost['cash_account_id'],
                    amount: (float) $cost['amount'],
                    remarks: $cost['remarks'],
                );

                $this->salesOrderDeliveryCostActions->create($dto, false);
            }

            self::updateSummary($salesOrderDelivery);

            $this->syncJournalEntries($salesOrderDelivery);

            if ($salesOrderDelivery->salesOrder) {
                SalesOrderActions::updateSummary($salesOrderDelivery->salesOrder);
            }

            $this->flushCache();

            return $salesOrderDelivery;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(SalesOrderDelivery $salesOrderDelivery, SalesOrderDeliveryUpdateDTO $data): SalesOrderDelivery
    {
        $timer_start = microtime(true);

        try {
            $previousSalesOrder = $salesOrderDelivery->salesOrder;

            $salesOrderDelivery->customer_id = $data->customerId;
            $salesOrderDelivery->sales_order_id = $data->salesOrderId;
            $salesOrderDelivery->code = $this->generateUniqueCode($salesOrderDelivery->company_id, $data->code, $salesOrderDelivery->id);
            $salesOrderDelivery->date = $this->generateDate($data->date);
            $salesOrderDelivery->warehouse_id = $data->warehouseId;
            $salesOrderDelivery->remarks = $data->remarks;
            $salesOrderDelivery->is_posted = $data->isPosted;
            $salesOrderDelivery->save();

            $salesOrderDelivery->unsetRelation('salesOrder');

            foreach ($data->deleteItemIds as $deleteId) {
                $salesOrderDeliveryItem = $salesOrderDelivery->items()->findOrFail($deleteId);
                $this->salesOrderDeliveryItemActions->delete($salesOrderDeliveryItem, false);
            }

            foreach ($data->items as $item) {
                if (! empty($item['id'])) {
                    $salesOrderDeliveryItem = $salesOrderDelivery->items()->findOrFail($item['id']);
                    $dto = new SalesOrderDeliveryItemUpdateDTO(
                        qty: $item['qty'],
                        productUnitId: $item['product_unit_id'],
                        productUnitConversionValue: $item['product_unit_conversion_value'],
                        remarks: $item['remarks'],
                        deleteSerialIds: $item['delete_serial_ids'],
                        serials: $item['serials'],
                    );

                    $this->salesOrderDeliveryItemActions->update($salesOrderDeliveryItem, $dto, false);
                } else {
                    $dto = new SalesOrderDeliveryItemCreateDTO(
                        companyId: $salesOrderDelivery->company_id,
                        branchId: $salesOrderDelivery->branch_id,
                        salesOrderDeliveryId: $salesOrderDelivery->id,
                        qty: $item['qty'],
                        productUnitId: $item['product_unit_id'],
                        productUnitConversionValue: $item['product_unit_conversion_value'],
                        remarks: $item['remarks'],
                        serials: $item['serials'],
                    );

                    $this->salesOrderDeliveryItemActions->create($dto, false);
                }
            }

            foreach ($data->deleteCostIds as $deleteId) {
                $salesOrderDeliveryCost = $salesOrderDelivery->costs()->findOrFail($deleteId);
                $this->salesOrderDeliveryCostActions->delete($salesOrderDeliveryCost, false);
            }

            foreach ($data->costs as $cost) {
                if (! empty($cost['id'])) {
                    $salesOrderDeliveryCost = $salesOrderDelivery->costs()->findOrFail($cost['id']);
                    $dto = new SalesOrderDeliveryCostUpdateDTO(
                        code: $cost['code'],
                        date: $cost['date'],
                        name: $cost['name'],
                        cashAccountId: $cost['cash_account_id'],
                        amount: (float) $cost['amount'],
                        remarks: $cost['remarks'],
                    );

                    $this->salesOrderDeliveryCostActions->update($salesOrderDeliveryCost, $dto, false);
                } else {
                    $dto = new SalesOrderDeliveryCostCreateDTO(
                        companyId: $salesOrderDelivery->company_id,
                        branchId: $salesOrderDelivery->branch_id,
                        salesOrderDeliveryId: $salesOrderDelivery->id,
                        code: $cost['code'],
                        date: $cost['date'],
                        name: $cost['name'],
                        cashAccountId: $cost['cash_account_id'],
                        amount: (float) $cost['amount'],
                        remarks: $cost['remarks'],
                    );

                    $this->salesOrderDeliveryCostActions->create($dto, false);
                }
            }

            self::updateSummary($salesOrderDelivery);

            $this->syncJournalEntries($salesOrderDelivery);

            if ($previousSalesOrder) {
                SalesOrderActions::updateSummary($previousSalesOrder->refresh());
            }

            if ($salesOrderDelivery->salesOrder) {
                if (! $previousSalesOrder || $salesOrderDelivery->salesOrder->id !== $previousSalesOrder->id) {
                    SalesOrderActions::updateSummary($salesOrderDelivery->salesOrder);
                }
            }

            $this->flushCache();

            return $salesOrderDelivery;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public static function updateSummary(SalesOrderDelivery $salesOrderDelivery): void
    {
        $salesOrderDelivery->refresh();

        $salesOrderDelivery->total_cogs = (float) $salesOrderDelivery->items()->sum('total_cogs');
        $salesOrderDelivery->total_cost = (float) $salesOrderDelivery->costs()->sum('amount');
        $salesOrderDelivery->save();
    }

    public function delete(SalesOrderDelivery $salesOrderDelivery): bool
    {
        $timer_start = microtime(true);

        try {
            $hasReturnItems = SalesReturnItem::query()
                ->whereIn(
                    'sales_order_delivery_item_id',
                    $salesOrderDelivery->items()->pluck('sales_order_delivery_items.id')
                )
                ->exists();

            if ($hasReturnItems) {
                throw new Exception('Sales order delivery cannot be deleted because it is referenced by sales return items.');
            }

            $salesOrder = $salesOrderDelivery->salesOrder;

            foreach ($salesOrderDelivery->items as $salesOrderDeliveryItem) {
                $this->salesOrderDeliveryItemActions->delete($salesOrderDeliveryItem, false);
            }

            foreach ($salesOrderDelivery->costs as $salesOrderDeliveryCost) {
                $this->salesOrderDeliveryCostActions->delete($salesOrderDeliveryCost, false);
            }

            $journalEntry = $salesOrderDelivery->journalEntry;
            if ($journalEntry) $this->journalEntryActions->delete($journalEntry);

            $currentMonthEarningsJournalEntry = $salesOrderDelivery->currentMonthEarningsJournalEntry;
            if ($currentMonthEarningsJournalEntry) $this->journalEntryActions->delete($currentMonthEarningsJournalEntry);

            $monthEndClosingJournalEntry = $salesOrderDelivery->monthEndClosingJournalEntry;
            if ($monthEndClosingJournalEntry) $this->journalEntryActions->delete($monthEndClosingJournalEntry);

            $monthToYearClosingJournalEntry = $salesOrderDelivery->monthToYearClosingJournalEntry;
            if ($monthToYearClosingJournalEntry) $this->journalEntryActions->delete($monthToYearClosingJournalEntry);

            $yearToRetainedEarningsClosingJournalEntry = $salesOrderDelivery->yearToRetainedEarningsClosingJournalEntry;
            if ($yearToRetainedEarningsClosingJournalEntry) $this->journalEntryActions->delete($yearToRetainedEarningsClosingJournalEntry);

            $result = $salesOrderDelivery->delete();

            if ($salesOrder) {
                SalesOrderActions::updateSummary($salesOrder->refresh());
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

    /**
     * Design §5 S3 (expense-direction P&L): TRANSACTION Dr cogs_goods_sold /
     * Cr inventory on the header total_cogs + full closing set (§5.1).
     * All entries skip/delete when total_cogs is 0.
     */
    private function syncJournalEntries(SalesOrderDelivery $salesOrderDelivery): void
    {
        $totalCogs = (float) $salesOrderDelivery->total_cogs;
        $company = $salesOrderDelivery->company;
        $hasAmount = $totalCogs > 0;

        $this->syncJournalEntry(
            salesOrderDelivery: $salesOrderDelivery,
            journalEntry: $salesOrderDelivery->journalEntry,
            journalType: JournalEntryTypeEnum::TRANSACTION->value,
            date: (string) $salesOrderDelivery->date,
            items: ! $hasAmount ? [] : [
                new JournalEntryItemDTO(
                    chartOfAccountId: $company->cogsGoodsSoldChartOfAccount?->id,
                    sequence: 1,
                    debit: $totalCogs,
                    credit: 0,
                    remarks: $salesOrderDelivery->remarks,
                ),
                new JournalEntryItemDTO(
                    chartOfAccountId: $company->assetCurrentInventoryChartOfAccount?->id,
                    sequence: 2,
                    debit: 0,
                    credit: $totalCogs,
                    remarks: $salesOrderDelivery->remarks,
                ),
            ],
        );

        $this->syncJournalEntry(
            salesOrderDelivery: $salesOrderDelivery,
            journalEntry: $salesOrderDelivery->currentMonthEarningsJournalEntry,
            journalType: JournalEntryTypeEnum::CURRENT_MONTH_EARNINGS->value,
            date: (string) $salesOrderDelivery->date,
            items: ! $hasAmount ? [] : [
                new JournalEntryItemDTO(
                    chartOfAccountId: $company->equityCurrentMonthEarningsChartOfAccount?->id,
                    sequence: 1,
                    debit: $totalCogs,
                    credit: 0,
                    remarks: $salesOrderDelivery->remarks,
                ),
                new JournalEntryItemDTO(
                    chartOfAccountId: $company->systemSuspenseChartOfAccount?->id,
                    sequence: 2,
                    debit: 0,
                    credit: $totalCogs,
                    remarks: $salesOrderDelivery->remarks,
                ),
            ],
        );

        $this->syncJournalEntry(
            salesOrderDelivery: $salesOrderDelivery,
            journalEntry: $salesOrderDelivery->monthEndClosingJournalEntry,
            journalType: JournalEntryTypeEnum::MONTH_END_CLOSING->value,
            date: $this->endOfMonthDate($salesOrderDelivery),
            items: ! $hasAmount ? [] : [
                new JournalEntryItemDTO(
                    chartOfAccountId: $company->systemSuspenseChartOfAccount?->id,
                    sequence: 1,
                    debit: $totalCogs,
                    credit: 0,
                    remarks: $salesOrderDelivery->remarks,
                ),
                new JournalEntryItemDTO(
                    chartOfAccountId: $company->cogsGoodsSoldChartOfAccount?->id,
                    sequence: 2,
                    debit: 0,
                    credit: $totalCogs,
                    remarks: $salesOrderDelivery->remarks,
                ),
            ],
        );

        $this->syncJournalEntry(
            salesOrderDelivery: $salesOrderDelivery,
            journalEntry: $salesOrderDelivery->monthToYearClosingJournalEntry,
            journalType: JournalEntryTypeEnum::MONTH_TO_YEAR_CLOSING->value,
            date: $this->endOfMonthDate($salesOrderDelivery),
            items: ! $hasAmount ? [] : [
                new JournalEntryItemDTO(
                    chartOfAccountId: $company->equityCurrentYearEarningsChartOfAccount?->id,
                    sequence: 1,
                    debit: $totalCogs,
                    credit: 0,
                    remarks: $salesOrderDelivery->remarks,
                ),
                new JournalEntryItemDTO(
                    chartOfAccountId: $company->equityCurrentMonthEarningsChartOfAccount?->id,
                    sequence: 2,
                    debit: 0,
                    credit: $totalCogs,
                    remarks: $salesOrderDelivery->remarks,
                ),
            ],
        );

        $this->syncJournalEntry(
            salesOrderDelivery: $salesOrderDelivery,
            journalEntry: $salesOrderDelivery->yearToRetainedEarningsClosingJournalEntry,
            journalType: JournalEntryTypeEnum::YEAR_TO_RETAINED_EARNINGS_CLOSING->value,
            date: $this->endOfYearDate($salesOrderDelivery),
            items: ! $hasAmount ? [] : [
                new JournalEntryItemDTO(
                    chartOfAccountId: $company->equityRetainedEarningsChartOfAccount?->id,
                    sequence: 1,
                    debit: $totalCogs,
                    credit: 0,
                    remarks: $salesOrderDelivery->remarks,
                ),
                new JournalEntryItemDTO(
                    chartOfAccountId: $company->equityCurrentYearEarningsChartOfAccount?->id,
                    sequence: 2,
                    debit: 0,
                    credit: $totalCogs,
                    remarks: $salesOrderDelivery->remarks,
                ),
            ],
        );
    }

    private function syncJournalEntry(
        SalesOrderDelivery $salesOrderDelivery,
        ?JournalEntry $journalEntry,
        string $journalType,
        string $date,
        array $items,
    ): void {
        if (count($items) === 0) {
            if ($journalEntry) {
                $this->journalEntryActions->delete($journalEntry);
            }

            return;
        }

        if (! $journalEntry) {
            $journalEntryDTO = new JournalEntryCreateDTO(
                companyId: $salesOrderDelivery->company_id,
                branchId: $salesOrderDelivery->branch_id,
                code: config('dcslab.KEYWORDS.AUTO'),
                date: $date,
                journalType: $journalType,
                sourceType: SalesOrderDelivery::class,
                sourceId: $salesOrderDelivery->id,
                referenceNo: $salesOrderDelivery->code,
                remarks: $salesOrderDelivery->remarks,
                items: $items,
            );
            $this->journalEntryActions->create($journalEntryDTO);
        } else {
            $journalEntryDTO = new JournalEntryUpdateDTO(
                branchId: $salesOrderDelivery->branch_id,
                code: $journalEntry->code,
                date: $date,
                journalType: $journalType,
                referenceNo: $salesOrderDelivery->code,
                remarks: $salesOrderDelivery->remarks,
                items: $items,
            );
            $this->journalEntryActions->update($journalEntry, $journalEntryDTO);
        }
    }

    private function endOfMonthDate(SalesOrderDelivery $salesOrderDelivery): string
    {
        return ($salesOrderDelivery->date instanceof Carbon
            ? $salesOrderDelivery->date->copy()
            : Carbon::parse((string) $salesOrderDelivery->date))
            ->endOfMonth()
            ->format('Y-m-d H:i:s');
    }

    private function endOfYearDate(SalesOrderDelivery $salesOrderDelivery): string
    {
        return ($salesOrderDelivery->date instanceof Carbon
            ? $salesOrderDelivery->date->copy()
            : Carbon::parse((string) $salesOrderDelivery->date))
            ->endOfYear()
            ->format('Y-m-d H:i:s');
    }
}
