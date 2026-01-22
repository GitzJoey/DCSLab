<?php

namespace App\Actions\StockTransferProductUnitSerial;

use App\Models\StockTransferProductUnitSerial;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StockTransferProductUnitSerialActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function create(array $data): StockTransferProductUnitSerial
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $stockTransferProductUnitSerial = new StockTransferProductUnitSerial();
            $stockTransferProductUnitSerial->company_id = $data['company_id'];
            $stockTransferProductUnitSerial->branch_id = $data['branch_id'];
            $stockTransferProductUnitSerial->stock_transfer_id = $data['stock_transfer_id'];
            $stockTransferProductUnitSerial->stock_transfer_product_unit_id = $data['stock_transfer_product_unit_id'];
            $stockTransferProductUnitSerial->serial = $data['serial'];
            $stockTransferProductUnitSerial->save();

            DB::commit();

            $this->flushCache();

            return $stockTransferProductUnitSerial;
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    private function readAnyQuery(
        ?bool $withTrashed,

        ?string $search,
        int $companyId,

        ?int $limit
    ) {
        $query = StockTransferProductUnitSerial::select('stock_transfer_product_unit_serials.*')->withTrashed()
            ->with(['company'])
            ->join('companies', 'companies.id', '=', 'stock_transfer_product_unit_serials.company_id')
            ->where(function ($query) use ($withTrashed, $search, $companyId) {
                if ($withTrashed == true) {
                    $query->withTrashed();
                } else {
                    $query->withoutTrashed();
                }

                if ($search) {
                    $query->search($search);
                }

                $query->whereCompanyId($companyId);
            });

        $query->orderBy('companies.name', 'asc')
            ->orderBy('stock_transfer_product_unit_serials.id', 'asc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query;
    }

    public function readAny(
        ?bool $useCache,
        ?bool $withTrashed,

        ?string $search,
        int $companyId,

        bool $paginate,
        ?int $page,
        ?int $perPage,
        ?int $limit
    ): Paginator|Collection {
        $timer_start = microtime(true);
        $recordsCount = 0;

        try {
            $cacheSearch = empty($search) ? '[empty]' : $search;
            $cacheKey = 'readAny_'.$companyId.'-'.$cacheSearch.'-'.$paginate.'-'.$page.'-'.$perPage;
            if ($useCache === true) {
                $cacheResult = $this->readFromCache($cacheKey);

                if (! is_null($cacheResult)) {
                    return $cacheResult;
                }
            }

            $result = null;

            $query = $this->readAnyQuery(
                withTrashed: $withTrashed,
                search: $search,
                companyId: $companyId,
                limit: $paginate ? null : $limit
            );

            if ($paginate) {
                $result = $query->paginate(perPage: $perPage, page: $page);
            } else {
                $result = $query->get();
            }

            $recordsCount = $result->count();

            if ($useCache === true) {
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

    public function read(StockTransferProductUnitSerial $stockTransferProductUnitSerial): StockTransferProductUnitSerial
    {
        return $stockTransferProductUnitSerial->load('company')->first();
    }

    public function getAllActiveStockTransferProductUnitSerial(
        ?array $with,
        ?bool $withTrashed,

        ?string $search,
        int $companyId,
        ?array $includeIds,

        ?int $limit
    ) {
        $timer_start = microtime(true);

        try {
            $query = $this->readAnyQuery(
                withTrashed: $withTrashed,

                search: $search,
                companyId: $companyId,

                limit: $limit
            );

            if ($includeIds) {
                $query = $query->orWhereIn('id', $includeIds);

                $orders = $query->getQuery()->orders;
                $query->reorder();
                $query->orderByRaw('FIELD(id, '.implode(',', $includeIds).') desc');
                if (! empty($orders)) {
                    foreach ($orders as $order) {
                        $query->orderBy($order['column'], $order['direction']);
                    }
                }
            }

            return $query->get();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(StockTransferProductUnitSerial $stockTransferProductUnitSerial, array $data): StockTransferProductUnitSerial
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $stockTransferProductUnitSerial->company_id = $data['company_id'];
            $stockTransferProductUnitSerial->branch_id = $data['branch_id'];
            $stockTransferProductUnitSerial->stock_transfer_id = $data['stock_transfer_id'];
            $stockTransferProductUnitSerial->stock_transfer_product_unit_id = $data['stock_transfer_product_unit_id'];
            $stockTransferProductUnitSerial->serial = $data['serial'];
            $stockTransferProductUnitSerial->save();

            DB::commit();

            $this->flushCache();

            return $stockTransferProductUnitSerial->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(StockTransferProductUnitSerial $stockTransferProductUnitSerial): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $stockTransferProductUnitSerial->delete();

            DB::commit();

            $this->flushCache();

            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }
}
