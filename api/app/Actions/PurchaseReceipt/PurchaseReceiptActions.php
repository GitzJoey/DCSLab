<?php

namespace App\Actions\PurchaseReceipt;

use App\DTOs\ExecuteDTO;
use App\Models\Company;
use App\Models\PurchaseReceipt;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseReceiptActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,

        ?int $purchaseId,
        ?int $warehouseId,
        ?bool $isPosted,
        ?bool $isValid,

        ?ExecuteDTO $execute
    ) {
        $query = PurchaseReceipt::select('purchase_receipts.*')
            ->with(['company', 'branch', 'purchase', 'warehouse'])
            ->join('companies', 'companies.id', '=', 'purchase_receipts.company_id')
            ->whereCompanyId('purchase_receipts', $companyId)
            ->whereBranchId('purchase_receipts', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $purchaseId,
            $warehouseId,
            $isPosted,
            $isValid,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->search($search);
            }

            if ($purchaseId) {
                $query->where('purchase_receipts.purchase_id', $purchaseId);
            }

            if ($warehouseId) {
                $query->where('purchase_receipts.warehouse_id', $warehouseId);
            }

            if (! is_null($isPosted)) {
                $query->where('purchase_receipts.is_posted', $isPosted);
            }

            if (! is_null($isValid)) {
                $query->where('purchase_receipts.is_valid', $isValid);
            }
        });

        $query->orderBy('purchase_receipts.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    $purchaseId ?? '[null]',
                    $warehouseId ?? '[null]',
                    is_null($isPosted) ? '[null]' : ($isPosted ? 'true' : 'false'),
                    is_null($isValid) ? '[null]' : ($isValid ? 'true' : 'false'),
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_purchase_receipt_'.implode('_', $cacheParams);

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
                        page: $execute->pagination->page,
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

    public function read(PurchaseReceipt $purchaseReceipt): PurchaseReceipt
    {
        return $purchaseReceipt->load([
            'company',
            'branch',
            'purchase',
            'warehouse',
        ]);
    }

    public function create(array $data): PurchaseReceipt
    {
        $timer_start = microtime(true);

        try {
            $purchaseReceipt = new PurchaseReceipt();
            $purchaseReceipt->company_id = $data['company_id'];
            $purchaseReceipt->branch_id = $data['branch_id'];
            $purchaseReceipt->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $purchaseReceipt->purchase_id = $data['purchase_id'];
            $purchaseReceipt->warehouse_id = $data['warehouse_id'];
            $purchaseReceipt->is_posted = $data['is_posted'];
            $purchaseReceipt->is_valid = $data['is_valid'];
            $purchaseReceipt->save();

            $this->flushCache();

            return $purchaseReceipt;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(PurchaseReceipt $purchaseReceipt, array $data): PurchaseReceipt
    {
        $timer_start = microtime(true);

        try {
            $purchaseReceipt->company_id = $data['company_id'];
            $purchaseReceipt->branch_id = $data['branch_id'];
            $purchaseReceipt->code = $this->generateUniqueCode($data['company_id'], $data['code'], $purchaseReceipt->id);
            $purchaseReceipt->purchase_id = $data['purchase_id'];
            $purchaseReceipt->warehouse_id = $data['warehouse_id'];
            $purchaseReceipt->is_posted = $data['is_posted'];
            $purchaseReceipt->is_valid = $data['is_valid'];
            $purchaseReceipt->save();

            $this->flushCache();

            return $purchaseReceipt->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseReceipt $purchaseReceipt): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $purchaseReceipt->delete();

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

    public function generateUniqueCode(int $companyId, string $code, ?int $exceptId): string
    {
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            $company = Company::find($companyId);

            $tryCount = 0;
            do {
                $count = $company->purchaseReceipts()->withTrashed()->count() + 1 + $tryCount;
                $code = 'WH'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        } else {
            return $code;
        }
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = PurchaseReceipt::whereCompanyId('purchase_receipt', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
