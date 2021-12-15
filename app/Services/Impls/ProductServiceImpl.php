<?php

namespace App\Services\Impls;

use App\Actions\RandomGenerator;
use Exception;
use App\Models\Product;
use App\Models\ProductUnit;

use App\Services\ProductService;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class ProductServiceImpl implements ProductService
{
    public function create(
        int $company_id,
        string $code,
        int $product_group_id,
        int $brand_id,
        string $name,
        int $tax_status,
        int $supplier_id,
        string $remarks,
        int $point,
        bool $use_serial_number,
        bool $has_expiry_date,
        string $product_type,
        string $status,
        array $product_units
    ): Product
    {
        DB::beginTransaction();

        try {
            if ($code == Config::get('const.DEFAULT.KEYWORDS.AUTO')) {
                $code = $this->generateUniqueCode($company_id);
            }

            $product = new Product();
            $product->company_id = $company_id;
            $product->code = $code;
            $product->product_group_id = $product_group_id;
            $product->brand_id = $brand_id;
            $product->name = $name;
            $product->tax_status = $tax_status;
            $product->supplier_id = $supplier_id;
            $product->remarks = $remarks;
            $product->point = $point;
            $product->use_serial_number = $use_serial_number;
            $product->has_expiry_date = $has_expiry_date;
            $product->product_type = $product_type;
            $product->status = $status;

            $product->save();

            $pu = [];
            foreach ($product_units as $product_unit) {
                array_push($pu, new ProductUnit(array (
                    'company_id' => $product_unit['company_id'],
                    'product_id' => $product['id'],
                    'code' => $product_unit['code'],
                    'unit_id' => $product_unit['unit_id'],
                    'conversion_value' => $product_unit['conv_value'],
                    'is_base' => $product_unit['is_base'],
                    'is_primary_unit' => $product_unit['is_primary_unit'],
                    'remarks' => $product_unit['remarks']
                )));
            }

            $product->productUnit()->saveMany($pu);

            DB::commit();

            return $product;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function read(
        int $companyId,
        bool $isProduct = true, 
        bool $isService = true,
        string $search = '',
        bool $paginate = true,
        int $perPage = 10
    )
    {
        if (!$companyId) return null;

        $product = Product::with('productGroup', 'brand', 'supplier', 'productUnits.unit')
                    ->whereCompanyId($companyId);

        if (!$isProduct && $isService) {
            $product = $product->where('product_type', '=', Config::get('const.ENUMS.PRODUCT_TYPE.SVC'));
        } else if ($isProduct && !$isService) {
            $product = $product->where('product_type', '<>', Config::get('const.ENUMS.PRODUCT_TYPE.SVC'));
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
    }

    public function update(
        int $id,
        int $company_id,
        string $code,
        int $product_group_id,
        int $brand_id,
        string $name,
        int $tax_status,
        int $supplier_id,
        string $remarks,
        int $point,
        bool $use_serial_number,
        bool $has_expiry_date,
        string $product_type,
        string $status,
        array $product_units
    ): ?Product
    {
        DB::beginTransaction();

        try {
            $product = Product::where('id', '=', $id);

            if ($code == Config::get('const.DEFAULT.KEYWORDS.AUTO')) {
                $code = $this->generateUniqueCode($company_id);
            }

            $product->update([
                'company_id' => $company_id,
                'code' => $code,
                'product_group_id' => $product_group_id,
                'brand_id' => $brand_id,
                'name' => $name,
                'tax_status' => $tax_status,
                'supplier_id' => $supplier_id,
                'remarks' => $remarks,
                'point' => $point,
                'use_serial_number' => $use_serial_number,
                'has_expiry_date' => $has_expiry_date,
                'product_type' => $product_type,
                'status' => $status
            ]);

            $pu = [];
            foreach ($product_units as $product_unit) {
                array_push($pu, array(
                    'id' => $product_unit['id'],
                    'company_id' => $product_unit['company_id'],
                    'product_id' => $id,
                    'code' => $product_unit['code'],
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

            $puIdsOld = $product->productUnit()->pluck('id')->ToArray();

            $deletedProductUnitIds = [];
            $deletedProductUnitIds = array_diff($puIdsOld, $puIds);            

            foreach ($deletedProductUnitIds as $deletedProductUnitId) {
                $productUnit = $product->productUnit()->whereIn('id', $deletedProductUnitId);
                $productUnit->delete();
            }

            $product->productUnits()->upsert($pu, ['id'], [
                    'code',
                    'unit_id',
                    'is_base',
                    'conversion_value',
                    'is_primary_unit',
                    'remarks'
                ]
            );

            DB::commit();

            return $product;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function delete(int $id): bool
    {
        $product = Product::find($id);

        return $product->delete();
    }

    public function generateUniqueCode(int $companyId): string
    {
        $rand = new RandomGenerator();
        
        do {
            $code = $rand->generateAlphaNumeric(3).$rand->generateFixedLengthNumber(3);
        } while (!$this->isUniqueCode($code, $companyId));

        return $code;
    }

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool
    {
        $result = Product::whereCompanyId($companyId)->where('code', '=' , $code);

        if($exceptId)
            $result = $result->where('id', '<>', $exceptId);

        return $result->count() == 0 ? true:false;
    }
}