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
        $unit_id,
        $price,
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
            $product->unit_id = $unit_id;
            $product->price = $price;
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
        return Product::with('group', 'brand', 'unit')->paginate();
        
    }

    public function update(
        $id,
        $code,
        $group_id,
        $brand_id,
        $name,
        $unit_id,
        $price,
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
                'unit_id' => $unit_id,
                'price' => $price, 
                'tax_status' => $tax_status, 
                'remarks' => $remarks, 
                'estimated_capital_price' => $estimated_capital_price, 
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


    public function delete($id)
    {
        $product = Product::find($id);

        return $product->delete();
        
    }
}