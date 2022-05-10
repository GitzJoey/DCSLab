<?php

namespace App\Services\Impls;

use App\Models\Product;
use App\Models\ProductUnit;

use App\Actions\RandomGenerator;
use App\Enums\ProductType;
use App\Services\ProductService;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class ProductServiceImpl implements ProductService
{
    public function __construct()
    {
        
    }
    
    public function create(
        int $company_id,
        string $code,
        int $product_group_id,
        int $brand_id,
        string $name,
        bool $taxable_supply,
        int $standard_rated_supply,
        bool $price_include_vat,
        ?string $remarks = null,
        int $point,
        bool $use_serial_number,
        bool $has_expiry_date,
        string $product_type,
        int $status,
        array $product_units
    ): ?Product
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            if ($code == Config::get('const.DEFAULT.KEYWORDS.AUTO')) {
                $code = $this->generateUniqueCodeForProduct($company_id);
            }

            $product = new Product();
            $product->company_id = $company_id;
            $product->code = $code;
            $product->product_group_id = $product_group_id;
            $product->brand_id = $brand_id;
            $product->name = $name;
            $product->taxable_supply = $taxable_supply;
            $product->standard_rated_supply = $standard_rated_supply;
            $product->price_include_vat = $price_include_vat;
            $product->remarks = $remarks;
            $product->point = $point;
            $product->use_serial_number = $use_serial_number;
            $product->has_expiry_date = $has_expiry_date;
            $product->product_type = $product_type;
            $product->status = $status;

            $product->save();

            $pu = [];
            foreach ($product_units as $product_unit) {
                $code = $product_unit['code'];
                if ($code == Config::get('const.DEFAULT.KEYWORDS.AUTO')) {
                    $code = $this->generateUniqueCodeForProductUnits($company_id);
                }
    
                array_push($pu, new ProductUnit(array (
                    'company_id' => $product_unit['company_id'],
                    'product_id' => $product['id'],
                    'code' => $code,
                    'unit_id' => $product_unit['unit_id'],
                    'conversion_value' => $product_unit['conv_value'],
                    'is_base' => $product_unit['is_base'],
                    'is_primary_unit' => $product_unit['is_primary_unit'],
                    'remarks' => $product_unit['remarks']
                )));
            }

            if (!empty($pu) && $this->checkUniqueCodeInArray($pu)) {
                $product->productUnits()->saveMany($pu);
            }

            DB::commit();

            return $product;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.$e);
            return Config::get('const.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.' '.'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function read(
        int $companyId,
        bool $isProduct = true, 
        bool $isService = true,
        string $search = '',
        bool $paginate = true,
        int $page,
        ?int $perPage = 10
    )
    {
        $timer_start = microtime(true);

        try {
            if (!$companyId) return null;

            $product = Product::with('productGroup', 'brand', 'productUnits.unit')
                        ->whereCompanyId($companyId);
    
            if (!$isProduct && $isService) {
                $product = $product->where('product_type', '=', ProductType::SERVICE->value);
            } else if ($isProduct && !$isService) {
                $product = $product->where('product_type', '<>', ProductType::SERVICE->value);
            } else if ($isProduct && $isService) {
                
            } else {
                return null;
            }
    
            if (empty($search)) {
                $product = $product->latest();
            } else {
                $product = $product->where('name', 'like', '%'.$search.'%')->latest();
            }
    
            if ($paginate) {
                $perPage = is_numeric($perPage) ? $perPage : Config::get('const.DEFAULT.PAGINATION_LIMIT');
                return $product->paginate($perPage);
            } else {
                return $product->get();
            }
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.$e);
            return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
        
    }

    private function readFromCache($key)
    {
        try {
            if (!Config::get('const.DEFAULT.DATA_CACHE.ENABLED')) return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');

            if (!Cache::has($key)) return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');

            return Cache::get($key);
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.$e);
            return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');
        } finally {
            Log::channel('cachehits')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] Read Key'.$key);
        }
    }

    private function saveToCache($key, $val)
    {
        try {
            if (empty($key)) return;

            Cache::tags([auth()->user()->id, __METHOD__])->add($key, $val, Config::get('const.DEFAULT.DATA_CACHE.CACHE_TIME.ENV'));
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.$e);
        } finally {
            Log::channel('cachehits')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] Save Key'.$key);
        }
    }

    private function flushCache()
    {
        try {
            Cache::tags([auth()->user()->id, __METHOD__])->flush();
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.$e);
        } finally {
            Log::channel('cachehits')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] Cache Flushed for tags '.auth()->user()->id.', '.__METHOD__);
        }
    }

    public function update(
        int $id,
        int $company_id,
        string $code,
        int $product_group_id,
        int $brand_id,
        string $name,
        bool $taxable_supply,
        int $standard_rated_supply,
        bool $price_include_vat,
        ?string $remarks = null,
        int $point,
        bool $use_serial_number,
        bool $has_expiry_date,
        string $product_type,
        int $status,
        array $product_units
    ): ?Product
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $product = Product::find($id);

            if ($code == Config::get('const.DEFAULT.KEYWORDS.AUTO')) {
                $code = $this->generateUniqueCodeForProduct($company_id);
            }

            $product->update([
                'company_id' => $company_id,
                'code' => $code,
                'product_group_id' => $product_group_id,
                'brand_id' => $brand_id,
                'name' => $name,
                'taxable_supply' => $taxable_supply,
                'standard_rated_supply' => $standard_rated_supply,
                'price_include_vat' => $price_include_vat,
                'remarks' => $remarks,
                'point' => $point,
                'use_serial_number' => $use_serial_number,
                'has_expiry_date' => $has_expiry_date,
                'product_type' => $product_type,
                'status' => $status
            ]);

            $pu = [];
            foreach ($product_units as $product_unit) {
                $code = $product_unit['code'];
                if ($code == Config::get('const.DEFAULT.KEYWORDS.AUTO')) {
                    $code = $this->generateUniqueCodeForProductUnits($company_id);
                }

                array_push($pu, array(
                    'id' => $product_unit['id'],
                    'company_id' => $product_unit['company_id'],
                    'product_id' => $id,
                    'code' => $code,
                    'unit_id' => $product_unit['unit_id'],
                    'conversion_value' => $product_unit['conv_value'],
                    'is_base' => $product_unit['is_base'],
                    'is_primary_unit' => $product_unit['is_primary_unit'],
                    'remarks' => $product_unit['remarks']
                ));
            }

            $puIds = [];
            foreach ($pu as $puId)
            {
                array_push($puIds, $puId['id']);
            }

            $puIdsOld = $product->productUnits()->pluck('id')->ToArray();

            $deletedProductUnitIds = [];
            $deletedProductUnitIds = array_diff($puIdsOld, $puIds);            

            foreach ($deletedProductUnitIds as $deletedProductUnitId) {
                $productUnit = $product->productUnits()->whereIn('id', $deletedProductUnitId);
                $productUnit->delete();
            }

            if (!empty($pu) && $this->checkUniqueCodeInArray($pu)) {
                $product->productUnits()->upsert($pu, ['id'], [
                    'code',
                    'unit_id',
                    'is_base',
                    'conversion_value',
                    'is_primary_unit',
                    'remarks'
                ]);
            }

            DB::commit();

            return $product;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.$e);
            return Config::get('const.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function delete(int $id): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;
        try {
            $product = Product::find($id);

            if ($product) {
                $product->productUnits()->delete();

                $retval = $product->delete();
            }

            DB::commit();

            return $retval; 
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.$e);
            return Config::get('const.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function generateUniqueCodeForProduct(int $companyId): string
    {
        $rand = new RandomGenerator();
        $code = '';

        do {
            $code = $rand->generateAlphaNumeric(3).$rand->generateFixedLengthNumber(3);
        } while (!$this->isUniqueCodeForProduct($code, $companyId));

        return $code;
    }

    public function generateUniqueCodeForProductUnits(int $companyId): string
    {
        $rand = new RandomGenerator();
        $code = '';
        
        do {
            $code = $rand->generateAlphaNumeric(3).$rand->generateFixedLengthNumber(3);
        } while (!$this->isUniqueCodeForProductUnits($code, $companyId));

        return $code;
    }

    public function isUniqueCodeForProduct(string $code, int $companyId, ?int $exceptId = null): bool
    {
        $timer_start = microtime(true);

        try {
            $result = Product::whereCompanyId($companyId)->where('code', '=' , $code);

            if($exceptId)
                $result = $result->where('id', '<>', $exceptId);
    
            return $result->count() == 0 ? true:false;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.$e);
            return Config::get('const.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function isUniqueCodeForProductUnits(string $code, int $companyId, ?int $exceptId = null): bool
    {
        $timer_start = microtime(true);

        try {
            $result = ProductUnit::whereCompanyId($companyId)->where('code', '=' , $code);

            if($exceptId)
                $result = $result->where('id', '<>', $exceptId);
    
            return $result->count() == 0 ? true:false;
        }  catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.$e);
            return Config::get('const.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    private function checkUniqueCodeInArray(array $productUnits): bool
    {
        $timer_start = microtime(true);

        try {
            $allCodes = Arr::pluck($productUnits, 'code');

            return (count($allCodes) == count(array_unique($allCodes)));
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.$e);
            return Config::get('const.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }
}