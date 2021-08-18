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
        $name
    )
    {
        DB::beginTransaction();

        try {
            $productunit = new ProductUnit();
            $productunit->code = $code;
            $productunit->name = $name;

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
        return ProductUnit::paginate();
    }

    public function getAllProductUnit()
    {
        return ProductUnit::all();
    }

    public function update(
        $id,
        $code,
        $name
    )
    {
        DB::beginTransaction();

        try {
            $productunit = ProductUnit::where('id', '=', $id);
    
            $retval = $productunit->update([
                'code' => $code,
                'name' => $name,
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
        $productunit = ProductUnit::find($id);

        return $productunit->delete();
        
    }
}