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
        $code,
        $group_id,
        $brand_id,
        $name,
        $tax_status,
        $remarks,
        $estimated_capital_price,
        $point,
        $is_use_serial,
        $product_type,
        $status
    )
    {

        DB::beginTransaction();

        try {
            $product = new Product();
            $product->code = $code;
            $product->group_id = $group_id;
            $product->brand_id = $brand_id;
            $product->name = $name;
            $product->tax_status = $tax_status;
            $product->remarks = $remarks;
            $product->estimated_capital_price = $estimated_capital_price;
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

    public function update(
        $id,
        $code,
        $group_id,
        $brand_id,
        $name,
        $tax_status,
        $remarks,
        $estimated_capital_price,
        $point,
        $is_use_serial,
        $product_type,
        $status
    )
    {
        DB::beginTransaction();

        try {
            $product = Product::where('id', '=', $id);

            $retval = $product->update([
                'code' => $code,
                'group_id' => $group_id,
                'brand_id' => $brand_id,
                'name' => $name,
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
