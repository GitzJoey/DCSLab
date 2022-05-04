<?php

namespace App\Services\Impls;

use App\Actions\RandomGenerator;
use App\Enums\ProductGroupCategory;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\ProductGroupService;
use App\Models\ProductGroup;

class ProductGroupServiceImpl implements ProductGroupService
{
    public function __construct()
    {
        
    }
    
    public function create(
        int $company_id,
        string $code,
        string $name,
        string $category
    ): ?ProductGroup
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $productgroup = new ProductGroup();
            $productgroup->company_id = $company_id;
            $productgroup->code = $code;
            $productgroup->name = $name;
            $productgroup->category = $category;

            $productgroup->save();

            DB::commit();

            return $productgroup->hId;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.is_null(auth()->user()) ? '':auth()->user()->id.'] '.__METHOD__.$e);
            return Config::get('const.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.' '.'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function readBy(string $key, string $value)
    {
        $timer_start = microtime(true);

        try {
            switch (strtoupper($key)) {
                case 'ID':
                    return ProductGroup::find($value);
                default:
                    return null;
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

    public function read(int $companyId, ?string $category = null, string $search = '', bool $paginate = true, int $page, ?int $perPage = 10)
    {
        $timer_start = microtime(true);

        try {
            $productGroup = ProductGroup::whereCompanyId($companyId);

            if (!empty($category)) {
                $productGroup = $productGroup->where('category', '=', ProductGroupCategory::PRODUCTS->value);
            } 
    
            if (empty($search)) {
                $productGroup = $productGroup->latest();
            } else {
                $productGroup = $productGroup->where('name', 'like', '%'.$search.'%')->latest();
            }
    
            if ($paginate) {
                $perPage = is_numeric($perPage) ? $perPage : Config::get('const.DEFAULT.PAGINATION_LIMIT');
                return $productGroup->paginate($perPage);
            } else {
                return $productGroup->get();
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
        string $category
    ): ?ProductGroup
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $productgroup = ProductGroup::find($id);
    
            $productgroup->update([
                'company_id' => $company_id,
                'code' => $code,
                'name' => $name,
                'category' => $category,
            ]);

            DB::commit();

            return $productgroup->refresh();
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
            $productgroup = ProductGroup::find($id);
            
            if ($productgroup) {
                $retval = $productgroup->delete();
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
            $result = ProductGroup::whereCompanyId($companyId)->where('code', '=' , $code);

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