<?php

namespace App\Actions\Brand;

use App\Actions\Randomizer\RandomizerActions;
use App\Models\Brand;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class BrandActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function create(
        array $brandArr
    ): Brand {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $brand = new Brand();
            $brand->company_id = $brandArr['company_id'];
            $brand->code = $brandArr['code'];
            $brand->name = $brandArr['name'];

            $brand->save();

            DB::commit();

            $this->flushCache();

            return $brand;
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function readAny(
        int $companyId,
        string $search = '',
        bool $paginate = true,
        int $page = 1,
        ?int $perPage = 10,
        array $with = [],
        bool $withTrashed = false,
        bool $useCache = true
    ): Paginator|Collection {
        $timer_start = microtime(true);

        try {
            $cacheKey = '';
            if ($useCache) {
                $cacheKey = 'read_'.$companyId.'-'.(empty($search) ? '[empty]' : $search).'-'.$paginate.'-'.$page.'-'.$perPage;
                $cacheResult = $this->readFromCache($cacheKey);

                if (! is_null($cacheResult)) {
                    return $cacheResult;
                }
            }

            $result = null;

            if (count($with) != 0) {
                $brand = Brand::with($with)->whereCompanyId($companyId);
            } else {
                $brand = Brand::whereCompanyId($companyId);
            }

            if ($withTrashed) {
                $brand = $brand->withTrashed();
            }

            if (empty($search)) {
                $brand = $brand->latest();
            } else {
                $brand = $brand->where('name', 'like', '%'.$search.'%')->latest();
            }

            if ($paginate) {
                $perPage = is_numeric($perPage) ? abs($perPage) : Config::get('dcslab.PAGINATION_LIMIT');
                $page = is_numeric($page) ? abs($page) : 1;

                $result = $brand->paginate(
                    perPage: $perPage,
                    page: $page
                );
            } else {
                $result = $brand->get();
            }

            if ($useCache) {
                $this->saveToCache($cacheKey, $result);
            }

            return $result;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function read(Brand $brand): Brand
    {
        return $brand->first();
    }

    public function readBy(string $key, string $value)
    {
        $timer_start = microtime(true);

        try {
            switch (strtoupper($key)) {
                case 'ID':
                    return Brand::find($value);
                    break;
                default:
                    break;
            }
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(
        Brand $brand,
        array $brandArr
    ): Brand {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $code = $brandArr['code'];
            $name = $brandArr['name'];

            $brand->update([
                'code' => $code,
                'name' => $name,
            ]);

            DB::commit();

            $this->flushCache();

            return $brand->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(Brand $brand): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $brand->delete();

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

    public function generateUniqueCode(): string
    {
        $rand = app(RandomizerActions::class);
        $code = $rand->generateAlpha().$rand->generateNumeric();

        return $code;
    }

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool
    {
        $result = Brand::whereCompanyId($companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }

    public function getBrandDDL(int $companyId): Collection
    {
        $brand = Brand::whereCompanyId($companyId);

        return $brand->latest()->get();
    }
}
