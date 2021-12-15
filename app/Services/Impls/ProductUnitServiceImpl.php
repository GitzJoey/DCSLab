<?php

namespace App\Services\Impls;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\ProductUnitService;
use App\Models\ProductUnit;

class ProductUnitServiceImpl implements ProductUnitService
{
    public function create(
        $code,
        $company_id,
        $product_id,
        $unit_id,
        $is_base,
        $conversion_value,
        $is_primary_unit,
        $remarks
    )
    {
        DB::beginTransaction();

        try {
            $productunit = new ProductUnit();
            $productunit->code = $code;
            $productunit->company_id = $company_id;
            $productunit->product_id = $product_id;
            $productunit->unit_id = $unit_id;
            $productunit->is_base = $is_base;
            $productunit->conversion_value = $conversion_value;
            $productunit->is_primary_unit = $is_primary_unit;
            $productunit->remarks = $remarks;

            $productunit->save();

            DB::commit();

            return $productunit->hId;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function read()
    {
        return ProductUnit::with('company')->bySelectedCompany()->paginate();
    }

    public function getAllProductUnit()
    {
        return ProductUnit::all();
    }

    public function update(
        $id,
        $code,
        $company_id,
        $product_id,
        $unit_id,
        $is_base,
        $conversion_value,
        $is_primary_unit,
        $remarks
    )
    {
        DB::beginTransaction();

        try {
            $productunit = ProductUnit::where('id', '=', $id);
    
            $retval = $productunit->update([
                'code' => $code,
                'company_id' => $company_id,
                'product_id' => $product_id,
                'unit_id' => $unit_id,
                'is_base' => $is_base,
                'conversion_value' => $conversion_value,
                'is_primary_unit' => $is_primary_unit,
                'remarks' => $remarks,
            ]);
    
            DB::commit();
    
            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function getProductUnitById($id)
    {
        return ProductUnit::find($id);
    }

    public function delete($id)
    {
        $productunit = ProductUnit::find($id);

        return $productunit->delete();
    }

    public function checkDuplicatedCode($crud_status, $id, $code)
    {
        switch($crud_status) {
            case 'create': 
                $count = ProductUnit::where('code', '=', $code)
                ->whereNull('deleted_at')
                ->count();
                return $count;
            case 'update':
                $count = ProductUnit::where('id', '<>' , $id)
                ->where('code', '=', $code)
                ->whereNull('deleted_at')
                ->count();
                return $count;
        }
    }
}