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
        // $company_id,
        $code,
        $name,
        $category
    )
    {
        DB::beginTransaction();

        try {
            $productgroup = new ProductGroup();
            // $productgroup->company_id = $company_id;
            $productgroup->code = $code;
            $productgroup->name = $name;
            $productgroup->category = $category;

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

    public function getAllActiveProductGroup()
    {
        return ProductGroup::all();
    }

    public function getAllProductGroup()
    {
        return ProductGroup::where('category', '=', 1)->get();
    }

    public function getAllServiceGroup()
    {
        return ProductGroup::where('category', '=', 2)->get();
    }

    public function GetAllProductandServiceGroup()
    {
        return ProductGroup::where('category', '=', 3)->get();
    }
    
    public function update(
        $id,
        // $company_id,
        $code,
        $name,
        $category
    )
    {
        DB::beginTransaction();

        try {
            $productgroup = ProductGroup::where('id', '=', $id);
    
            $retval = $productgroup->update([
                // 'company_id' => $company_id,
                'code' => $code,
                'name' => $name,
                'category' => $category,
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
        return ProductGroup::find($id);
    }

    public function delete($id)
    {
        $productgroup = ProductGroup::find($id);

        return $productgroup->delete();
    }

    public function checkDuplicatedCode($crud_status, $id, $code)
    {
        switch($crud_status) {
            case 'create': 
                $count = ProductGroup::where('code', '=', $code)
                ->whereNull('deleted_at')
                ->count();
                return $count;
            case 'update':
                $count = ProductGroup::where('id', '<>' , $id)
                ->where('code', '=', $code)
                ->whereNull('deleted_at')
                ->count();
                return $count;
        }
    }
}