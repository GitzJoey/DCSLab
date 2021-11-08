<?php

namespace App\Services\Impls;

use Exception;
use App\Models\Product;
use App\Services\ProductService;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class ProductServiceImpl implements ProductService
{
    public function create(
        $company_id,
        $code,
        $group_id,
        $brand_id,
        $name,
        $tax_status,
        $supplier_id,
        $remarks,
        $point,
        $is_use_serial,
        $product_type,
        $status,
    )
    {

        DB::beginTransaction();

        try {
            $product = new Product();
            $product->company_id = $company_id;
            $product->code = $code;
            $product->group_id = $group_id;
            $product->brand_id = $brand_id;
            $product->name = $name;
            $product->tax_status = $tax_status;
            $product->supplier_id = $supplier_id;
            $product->remarks = $remarks;
            $product->point = $point;
            $product->is_use_serial = $is_use_serial;
            $product->product_type = $product_type;
            $product->status = $status;
            $product->save();

            

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
        return Product::with('group', 'brand', 'product_unit.unit')->paginate();
    }

    public function read_product()
    {
        return Product::with('group', 'brand', 'product_unit.unit', 'supplier')
                ->where('product_type', '<>', 4)
                ->paginate();
    }

    public function read_service()
    {
        return Product::with('group', 'brand', 'product_unit.unit')
                ->where('product_type', '=', 4)
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
        $group_id,
        $brand_id,
        $name,
        $product_unit,
        $unit,
        $tax_status,
        $remarks,
        $estimated_capital_price,
        $point,
        $is_use_serial,
        $product_type,
        $status,
    )
    {
        DB::beginTransaction();

        try {
            $product = Product::where('id', '=', $id);

            $retval = $product->update([
                'company_id' => $company_id,
                'code' => $code,
                'group_id' => $group_id,
                'brand_id' => $brand_id,
                'name' => $name,
                'product_unit' => $product_unit,
                'unit' => $unit,
                'tax_status' => $tax_status,
                'remarks' => $remarks,
                'estimated_capital_price' => $estimated_capital_price,
                'point' => $point,
                'is_use_serial' => $is_use_serial,
                'product_type' => $product_type,
                'status' => $status

            ]);

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
        return Product::find($id);
    }

    public function getProductBrandById($id)
    {
        return Product::find($id);
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
