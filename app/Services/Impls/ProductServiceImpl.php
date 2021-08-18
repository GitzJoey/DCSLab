<?php

namespace App\Services\Impls;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\ProductService;
use App\Models\Product;

class ProductServiceImpl implements ProductService
{
    public function create(
        $code,
        $group_id,
        $brand_id,
        $name,
        $unit_id,
        $price,
        $tax_status ,
        $information,
        $estimated_capital_price,
        $is_use_serial,
        $is_buy,
        $is_production_material,
        $is_production_result,
        $is_sell,
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
            $product->information = $information;
            $product->estimated_capital_price = $estimated_capital_price;
            $product->is_use_serial = $is_use_serial;
            $product->is_buy = $is_buy;
            $product->is_production_material = $is_production_material;
            $product->is_production_result = $is_production_result;
            $product->is_sell = $is_sell;
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
        $tax_status ,
        $information,
        $estimated_capital_price,
        $is_use_serial,
        $is_buy,
        $is_production_material,
        $is_production_result,
        $is_sell,
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
                'information' => $information, 
                'estimated_capital_price' => $estimated_capital_price, 
                'is_use_serial' => $is_use_serial, 
                'is_buy' => $is_buy, 
                'is_production_material' => $is_production_material, 
                'is_production_result' => $is_production_result, 
                'is_sell' => $is_sell, 
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