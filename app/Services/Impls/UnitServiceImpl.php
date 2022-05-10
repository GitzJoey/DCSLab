<?php

namespace App\Services\Impls;

use App\Actions\RandomGenerator;
use App\Enums\UnitCategory;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\UnitService;
use App\Models\Unit;
use Illuminate\Support\Facades\Cache;

class UnitServiceImpl implements UnitService
{
    public function __construct()
    {
        
    }
    
    public function create(
        int $company_id,
        string $code,
        string $name,
        int $category
    ): ?Unit
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            if ($code == Config::get('const.DEFAULT.KEYWORDS.AUTO')) {
                $code = $this->generateUniqueCode($company_id);
            }

            $unit = new Unit();
            $unit->company_id = $company_id;
            $unit->code = $code;
            $unit->name = $name;
            $unit->category = $category;

            $unit->save();

            DB::commit();

            return $unit;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.is_null(auth()->user()) ? '':auth()->user()->id.'] '.__METHOD__.$e);
            return Config::get('const.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.' '.'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function read(int $companyId, int $category, string $search = '', bool $paginate = true, int $page, ?int $perPage = 10)
    {
        $timer_start = microtime(true);

        try {
            $unit = Unit::whereCompanyId($companyId);
         
            if ($category == UnitCategory::PRODUCTS) {
                $unit = $unit->where('category', '<>', UnitCategory::SERVICES->value);
            } else if ($category == UnitCategory::SERVICES) {
                $unit = $unit->where('category', '<>', UnitCategory::PRODUCTS->value);
            } else {
    
            }
            
            if (empty($search)) {
                $unit = $unit->latest();
            } else {
                $unit = $unit->where('name', 'like', '%'.$search.'%')->latest();
            }
    
            if ($paginate) {
                $perPage = is_numeric($perPage) ? $perPage : Config::get('const.DEFAULT.PAGINATION_LIMIT');
                return $unit->paginate($perPage);
            } else {
                return $unit->get();
            }
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.is_null(auth()->user()) ? '':auth()->user()->id.'] '.__METHOD__.$e);
            return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.is_null(auth()->user()) ? '':auth()->user()->id.'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    private function readFromCache($key)
    {
        try {
            if (!Config::get('const.DEFAULT.DATA_CACHE.ENABLED')) return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');

            if (!Cache::has($key)) return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');

            return Cache::get($key);
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.is_null(auth()->user()) ? '':auth()->user()->id.'] '.__METHOD__.$e);
            return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');
        } finally {
            Log::channel('cachehits')->info('['.session()->getId().'-'.is_null(auth()->user()) ? '':auth()->user()->id.'] Read Key'.$key);
        }
    }

    private function saveToCache($key, $val)
    {
        try {
            if (empty($key)) return;

            Cache::tags([auth()->user()->id, __METHOD__])->add($key, $val, Config::get('const.DEFAULT.DATA_CACHE.CACHE_TIME.ENV'));
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.is_null(auth()->user()) ? '':auth()->user()->id.'] '.__METHOD__.$e);
        } finally {
            Log::channel('cachehits')->info('['.session()->getId().'-'.is_null(auth()->user()) ? '':auth()->user()->id.'] Save Key'.$key);
        }
    }

    private function flushCache()
    {
        try {
            Cache::tags([auth()->user()->id, __METHOD__])->flush();
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.is_null(auth()->user()) ? '':auth()->user()->id.'] '.__METHOD__.$e);
        } finally {
            Log::channel('cachehits')->info('['.session()->getId().'-'.is_null(auth()->user()) ? '':auth()->user()->id.'] Cache Flushed for tags '.auth()->user()->id.', '.__METHOD__);
        }
    }

    public function readBy(string $key, string $value)
    {
        $timer_start = microtime(true);

        try {
            switch(strtoupper($key)) {
                case 'ID':
                    return Unit::find($value);
                case 'CATEGORY':
                    return Unit::where('category', '=', $value)->get();
                default:
                    return null;
            }
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.is_null(auth()->user()) ? '':auth()->user()->id.'] '.__METHOD__.$e);
            return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.is_null(auth()->user()) ? '':auth()->user()->id.'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function update(
        int $id,
        int $company_id,
        string $code,
        string $name,
        int $category
    ): ?Unit
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $unit = Unit::find($id);

            if ($code == Config::get('const.DEFAULT.KEYWORDS.AUTO')) {
                $code = $this->generateUniqueCode($company_id);
            }

            $unit->update([
                'company_id' => $company_id,
                'code' => $code,
                'name' => $name,
                'category' => $category,
            ]);
    
            DB::commit();
    
            return $unit->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.is_null(auth()->user()) ? '':auth()->user()->id.'] '.__METHOD__.$e);
            return Config::get('const.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.is_null(auth()->user()) ? '':auth()->user()->id.'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function delete(int $id): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;

        try {
            $unit = Unit::find($id);

            if ($unit) {
                $retval = $unit->delete();
            }

            DB::commit();

            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.is_null(auth()->user()) ? '':auth()->user()->id.'] '.__METHOD__.$e);
            return Config::get('const.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.is_null(auth()->user()) ? '':auth()->user()->id.'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function generateUniqueCode(int $companyId): string
    {
        $rand = new RandomGenerator();
        $code = '';
        
        do {
            $code = $rand->generateAlphaNumeric(3).$rand->generateFixedLengthNumber(3);
        } while (!$this->isUniqueCode($code, $companyId));

        return $code;
    }

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool
    {
        $timer_start = microtime(true);

        try {
            $result = Unit::whereCompanyId($companyId)->where('code', '=' , $code);

            if($exceptId)
                $result = $result->where('id', '<>', $exceptId);
    
            return $result->count() == 0 ? true:false;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.is_null(auth()->user()) ? '':auth()->user()->id.'] '.__METHOD__.$e);
            return Config::get('const.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.is_null(auth()->user()) ? '':auth()->user()->id.'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }
}