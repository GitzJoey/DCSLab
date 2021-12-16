<?php

namespace App\Services\Impls;

use App\Models\Brand;
use Exception;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Services\ProductService;
use App\Models\User;
use App\Models\ProductUnit;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class ProductServiceImpl implements ProductService
{
    public function create(
        $company_id,
        $code,
        $product_group_id,
        $brand_id,
        $name,
        $tax_status,
        $supplier_id,
        $remarks,
        $point,
        $use_serial_number,
        $has_expiry_date,
        $product_type,
        $status,
        $product_units
    )
    {
        DB::beginTransaction();

        try {
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

            return $product->hId;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function read()
    {
        return Product::with('productGroup', 'brand', 'productUnit.unit')
                ->bySelectedCompany()
                ->paginate();
    }

    public function read_product()
    {
        return Product::with('productGroup', 'brand', 'productUnit.unit')
                ->where('product_type', '<>', 4)
                ->bySelectedCompany()
                ->paginate();
    }

    public function read_service()
    {
        return Product::with('productGroup', 'productUnit.unit')
                ->where('product_type', '=', 4)
                ->bySelectedCompany()
                ->paginate();
    }

    public function getAllService()
    {
        return Product::all();
    }

    public function update(
        $id,
        $company_id,
        $code,
        $product_group_id,
        $brand_id,
        $name,
        $tax_status,
        $supplier_id,
        $remarks,
        $point,
        $use_serial_number,
        $has_expiry_date,
        $product_type,
        $status,
        $product_units
    )
    {
        DB::beginTransaction();

        try {
            $product = Product::where('id', '=', $id);

            $retval = $product->update([
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

            $puOld = Product::find($id);
            $puIdsOld = $puOld->productUnit()->pluck('id')->ToArray();

            $deletedProductUnitIds = [];
            $deletedProductUnitIds = array_diff($puIdsOld, $puIds);            

            foreach ($deletedProductUnitIds as $deletedProductUnitId) {
                $productUnit = ProductUnit::find($deletedProductUnitId);
                $retval = $productUnit->delete();
            }

            $retval = ProductUnit::upsert(
                $pu, 
                [
                    'id'
                ], 
                [
                    'code',
                    'unit_id',
                    'is_base',
                    'conversion_value',
                    'is_primary_unit',
                    'remarks'
                ]
            );

            DB::commit();

            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function getProductGroupById($id)
    {
        return ProductGroup::find($id);
    }

    public function getBrandById($id)
    {
        return Brand::find($id);
    }

    public function delete($id)
    {
        $product = Product::find($id);

        return $product->delete();
    }

    public function checkDuplicatedCode($crud_status, $id, $code)
    {
        switch($crud_status) {
            case 'create':
                $count = Product::where('code', '=', $code)
                ->whereNull('deleted_at')
                ->count();
                return $count;
            case 'update':
                $count = Product::where('id', '<>' , $id)
                ->where('code', '=', $code)
                ->whereNull('deleted_at')
                ->count();
                return $count;
        }
    }
}