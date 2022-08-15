<?php

namespace App\Services\Impls;

use App\Actions\RandomGenerator;
use App\Enums\UnitCategory;
use App\Models\Unit;
use App\Services\UnitService;
use App\Traits\CacheHelper;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UnitServiceImpl implements UnitService
{
    use CacheHelper;

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
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function list(
        int $companyId,
        int $category,
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
                $cacheKey = 'read_'.$companyId.'-'.$category.'-'.(empty($search) ? '[empty]' : $search).'-'.$paginate.'-'.$page.'-'.$perPage;
                $cacheResult = $this->readFromCache($cacheKey);

                if (!is_null($cacheResult)) {
                    return $cacheResult;
                }
            }

            $result = null;

            $unit = Unit::whereCompanyId($companyId);

            if ($category == UnitCategory::PRODUCTS->value) {
                $unit = $unit->where('category', '<>', UnitCategory::SERVICES->value);
            } elseif ($category == UnitCategory::SERVICES->value) {
                $unit = $unit->where('category', '<>', UnitCategory::PRODUCTS->value);
            } elseif ($category == UnitCategory::PRODUCTS_AND_SERVICES->value) {
            } else {
                return null;
            }

            $unit = count($with) != 0 ? Unit::with($with) : Unit::with('company');
            $unit = $unit->whereCompanyId($companyId);

            if ($withTrashed)
                $unit = $unit->withTrashed();

            if (empty($search)) {
                $unit = $unit->latest();
            } else {
                $unit = $unit->where('name', 'like', '%'.$search.'%')->latest();
            }

            if ($paginate) {
                $perPage = is_numeric($perPage) ? $perPage : Config::get('dcslab.PAGINATION_LIMIT');
                $result = $unit->paginate(perPage: abs($perPage), page: abs($page));
            } else {
                $result = $unit->get();
            }

            if ($useCache) {
                $this->saveToCache($cacheKey, $result);
            }

            return $result;
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)'.($useCache ? ' (C)' : ' (DB)'));
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
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
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
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
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
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function generateUniqueCode(): string
    {
        $rand = new RandomGenerator();
        $code = $rand->generateAlphaNumeric(3).$rand->generateFixedLengthNumber(3);

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
}
