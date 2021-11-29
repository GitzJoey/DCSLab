<?php

namespace App\Services\Impls;

use Exception;
use App\Models\Product;
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
        $product_units
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

            $pu = [];
            foreach ($product_units as $product_unit) {
                array_push($pu, new ProductUnit(array (
                    'code' => $product_unit['code'],
                    'company_id' => $product_unit['company_id'],
                    'product_id' => $product['id'],
                    'unit_id' => $product_unit['unit_id'],
                    'is_base' => $product_unit['is_base'],
                    'conversion_value' => $product_unit['conv_value'],
                    'is_primary_unit' => $product_unit['is_primary_unit'],
                    'remarks' => $product_unit['remarks']
                )));
            }
            $product->product_unit()->saveMany($pu);

            DB::commit();

            return $product->hId;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function createService(
        $company_id,
        $code,
        $group_id,
        $name,
        $unit_id,
        $tax_status,
        $remarks,
        $point,
        $product_type,
        $status
    )
    {
        DB::beginTransaction();

        try {
            $service = new Product();
            $service->company_id = $company_id;
            $service->code = $code;
            $service->group_id = $group_id;
            $service->name = $name;
            $service->unit_id = $unit_id;
            $service->tax_status = $tax_status;
            $service->remarks = $remarks;
            $service->point = $point;
            $service->product_type = $product_type;
            $service->status = $status;
            $service->save();

            DB::commit();

            return $service->hId;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function read($userId)
    {
        $user = User::find($userId);
        $company_list = $user->companies()->pluck('company_id');
        return Product::with('group', 'brand', 'product_unit.unit')->whereIn('company_id', $company_list)->paginate();
    }

    public function read_product($userId)
    {
        $user = User::find($userId);
        $company_list = $user->companies()->pluck('company_id');
        return Product::with('group', 'brand', 'product_unit.unit', 'supplier')
                ->whereIn('company_id', $company_list)
                ->where('product_type', '<>', 4)
                ->paginate();
    }

    public function read_service($userId)
    {
        $user = User::find($userId);
        $company_list = $user->companies()->pluck('company_id');
        return Product::with('group', 'product_unit.unit')
                ->whereIn('company_id', $company_list)
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
        $tax_status,
        $supplier_id,
        $remarks,
        $point,
        $is_use_serial,
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
                'group_id' => $group_id,
                'brand_id' => $brand_id,
                'name' => $name,
                'tax_status' => $tax_status,
                'supplier_id' => $supplier_id,
                'remarks' => $remarks,
                'point' => $point,
                'is_use_serial' => $is_use_serial,
                'product_type' => $product_type,
                'status' => $status
            ]);

            $pu = [];
            foreach ($product_units as $product_unit) {
                array_push($pu, array(
                    'id' => $product_unit['id'],
                    'code' => $product_unit['code'],
                    'company_id' => $product_unit['company_id'],
                    'product_id' => $id,
                    'unit_id' => $product_unit['unit_id'],
                    'is_base' => $product_unit['is_base'],
                    'conversion_value' => $product_unit['conv_value'],
                    'is_primary_unit' => $product_unit['is_primary_unit'],
                    'remarks' => $product_unit['remarks']
                ));
            }

            $retval = ProductUnit::upsert(
                $pu, 
                [
                    'id', 
                    'code',
                    'company_id',
                    'product_id',
                    'unit_id',
                    'conversion_value',
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

            // $new_pu = [];
            // foreach ($product_units as $product_unit) {
            //     if (is_null($product_unit['id']) == true) {
            //         array_push($new_pu, new ProductUnit(array(
            //             'code' => $product_unit['code'],
            //             'company_id' => $product_unit['company_id'],
            //             'product_id' => $id,
            //             'unit_id' => $product_unit['unit_id'],
            //             'is_base' => $product_unit['is_base'],
            //             'conversion_value' => $product_unit['conv_value'],
            //             'is_primary_unit' => $product_unit['is_primary_unit'],
            //             'remarks' => $product_unit['remarks']
            //         )));
            //     };
            // }
            // if (count($new_pu) > 0) {
            //     $old_product = new Product();
            //     $old_product->id = $id;
            //     $old_product->product_unit()->saveMany($new_pu);
            // };

            // foreach ($product_units as $product_unit) {
            //     if (is_null($product_unit['id']) == false) {
            //         $old_product_unit = ProductUnit::where('id', '=', $product_unit['id']);

            //         $old_product_unit->update([
            //             'code' => $product_unit['code'],
            //             'company_id' => $product_unit['company_id'],
            //             'product_id' => $id,
            //             'unit_id' => $product_unit['unit_id'],
            //             'is_base' => $product_unit['is_base'],
            //             'conversion_value' => $product_unit['conv_value'],
            //             'is_primary_unit' => $product_unit['is_primary_unit'],
            //             'remarks' => $product_unit['remarks']
            //         ]);
            //     };
            // }

            DB::commit();

            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function updateService(
        $id,
        $company_id,
        $code,
        $group_id,
        $name,
        $unit_id,
        $tax_status,
        $remarks,
        $point,
        $product_type,
        $status
    )

    {
        DB::beginTransaction();

        try {
            $product = Product::where('id', '=', $id);

            $retval = $product->update([
                'company_id' => $company_id,
                'code' => $code,
                'group_id' => $group_id,
                'name' => $name,
                'unit_id' => $unit_id,
                'tax_status' => $tax_status,
                'remarks' => $remarks,
                'point' => $point,
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