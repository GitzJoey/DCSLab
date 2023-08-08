<?php

namespace App\Actions\Unit;

use App\Actions\Randomizer\RandomizerActions;
use App\Enums\UnitCategory;
use App\Models\Unit;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class UnitActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function create(
        array $unitArr
    ): Unit {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $unit = new Unit();
            $unit->company_id = $unitArr['company_id'];
            $unit->code = $unitArr['code'];
            $unit->name = $unitArr['name'];
            $unit->description = $unitArr['description'];
            $unit->category = $unitArr['category'];

            $unit->save();

            DB::commit();

            $this->flushCache();

            return $unit;
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
        ?int $category,
        string $search = '',
        bool $paginate = true,
        int $page = 1,
        ?int $perPage = 10,
        array $with = [],
        bool $withTrashed = false,
        bool $useCache = true
    ): Paginator|Collection {
        $timer_start = microtime(true);
        $recordsCount = 0;

        try {
            $cacheKey = 'readAny_'.$companyId.'_'.(empty($search) ? '[empty]' : $search).'-'.$paginate.'-'.$page.'-'.$perPage;
            if ($useCache) {
                $cacheResult = $this->readFromCache($cacheKey);

                if (! is_null($cacheResult)) {
                    return $cacheResult;
                }
            }

            $result = null;

            $unit = count($with) != 0 ? Unit::with($with) : Unit::with(['company']);

            $unit = $unit->whereCompanyId($companyId);

            if ($category) {
                switch ($category) {
                    case UnitCategory::PRODUCTS->value:
                        $unit = $unit->where('category', '=', UnitCategory::PRODUCTS->value);
                        break;
                    case UnitCategory::SERVICES->value:
                        $unit = $unit->where('category', '=', UnitCategory::SERVICES->value);
                        break;
                }
            }

            if (empty($search)) {
                $unit = $unit->latest();
            } else {
                $unit = $unit->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('category', 'like', '%'.$search.'%');
                }
                )->latest();
            }

            if ($withTrashed) {
                $unit = $unit->withTrashed();
            }

            if ($paginate) {
                $perPage = is_numeric($perPage) ? $perPage : Config::get('dcslab.PAGINATION_LIMIT');
                $result = $unit->paginate(abs($perPage));
            } else {
                $result = $unit->get();
            }

            $recordsCount = $result->count();

            $this->saveToCache($cacheKey, $result);

            return $result;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time, $recordsCount);
        }
    }

    public function read(Unit $unit): Unit
    {
        return $unit->first();
    }

    public function readBy(string $key, string $value)
    {
        $timer_start = microtime(true);

        try {
            switch (strtoupper($key)) {
                case 'ID':
                    return Unit::find($value);
                case 'CATEGORY':
                    return Unit::where('category', '=', $value)->get();
                default:
                    return null;
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
        Unit $unit,
        array $unitArr
    ): Unit {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $unit->update([
                'code' => $unitArr['code'],
                'name' => $unitArr['name'],
                'description' => $unitArr['description'],
                'category' => $unitArr['category'],
            ]);

            DB::commit();

            $this->flushCache();

            return $unit->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(Unit $unit): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $unit->delete();

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
        $result = Unit::whereCompanyId($companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }

    public function getUnitDDL(int $companyId, ?int $category): Collection
    {
        $unit = Unit::whereCompanyId($companyId);

        if ($category) {
            switch ($category) {
                case UnitCategory::PRODUCTS->value:
                    $unit = $unit->where('category', '=', UnitCategory::PRODUCTS->value);
                    break;
                case UnitCategory::SERVICES->value:
                    $unit = $unit->where('category', '=', UnitCategory::SERVICES->value);
                    break;
            }
        }

        return $unit->latest()->get();
    }
}
