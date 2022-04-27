<?php

namespace App\Services\Impls;

use App\Actions\RandomGenerator;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

use App\Services\BrandService;

use App\Models\Brand;

class BrandServiceImpl implements BrandService
{
    public function __construct()
    {
        
    }

    public function create(int $company_id, string $code, string $name): Brand
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            if ($code == Config::get('const.DEFAULT.KEYWORDS.AUTO')) {
                $code = $this->generateUniqueCode($company_id);
            }

            $productbrand = new Brand();
            $productbrand->company_id = $company_id;
            $productbrand->code = $code;
            $productbrand->name = $name;

            $productbrand->save();

            DB::commit();

            return $productbrand;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.is_null(auth()->user()) ? '':auth()->user()->id.'] '.__METHOD__.$e);
            return Config::get('const.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.' '.'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function read(int $companyId, string $search = '', bool $paginate = true, int $page, ?int $perPage = 10)
    {
        $timer_start = microtime(true);

        try {
            if (empty($search)) {
                $pb = Brand::whereCompanyId($companyId)->latest();
            } else {
                $pb = Brand::whereCompanyId($companyId)->where('name', 'like', '%'.$search.'%')->latest();
            }
    
            if ($paginate) {
                $perPage = is_numeric($perPage) ? $perPage : Config::get('const.DEFAULT.PAGINATION_LIMIT');
                return $pb->paginate($perPage);
            } else {
                return $pb->get();
            }
        } catch (Exception $e) {
            return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.is_null(auth()->user()) ? '':auth()->user()->id.'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
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
            Log::debug('['.session()->getId().'-'.is_null(auth()->user()) ? '':auth()->user()->id.'] '.__METHOD__.$e);
            return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.is_null(auth()->user()) ? '':auth()->user()->id.'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function update(int $id, int $company_id, string $code, string $name): Brand
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $productbrand = Brand::find($id);

            $productbrand->update([
                'company_id' => $company_id,
                'code' => $code,
                'name' => $name,
            ]);

            DB::commit();

            return $productbrand->refresh();
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
            $productbrand = Brand::find($id);
            if ($productbrand) {
                $retval = $productbrand->delete();
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
            $result = Brand::whereCompanyId($companyId)->where('code', '=' , $code);

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
