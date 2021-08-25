<?php

namespace App\Services\Impls;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\ProductGroupService;
use App\Models\ProductGroup;

class ProductGroupServiceImpl implements ProductGroupService
{
    public function create(
        $code,
        $name
    )
    {
        DB::beginTransaction();

        try {
            $productgroup = new ProductGroup();
            $productgroup->code = $code;
            $productgroup->name = $name;

            $productgroup->save();

            DB::commit();

            return $productgroup->hId;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function read()
    {
        return ProductGroup::paginate();
    }

    public function getAllProductGroup()
    {
        return ProductGroup::all();
    }

    public function update(
        $id,
        $code,
        $name
    )
    {
        DB::beginTransaction();

        try {
            $productgroup = ProductGroup::where('id', '=', $id);
    
            $retval = $productgroup->update([
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
        $productgroup = ProductGroup::find($id);

        return $productgroup->delete();
    }

    public function checkDuplicatedCode($id, $code)
    {
        $count = ProductGroup::where('id', '<>' , $id)
        ->where('code', '=', $code)
        ->count();
        return $count;
    }
}