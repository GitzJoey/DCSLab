<?php

namespace App\Services\Impls;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\ProductBrandService;
use App\Models\ProductBrand;

class ProductBrandServiceImpl implements ProductBrandService
{
    public function create(
        $company_id,
        $code,
        $name
    )
    {
        DB::beginTransaction();

        try {
            $productbrand = new ProductBrand();
            $productbrand->company_id = $company_id;
            $productbrand->code = $code;
            $productbrand->name = $name;

            $productbrand->save();

            DB::commit();

            return $productbrand->hId;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function read()
    {
        return ProductBrand::paginate();
    }

    public function getAllProductBrand()
    {
        return ProductBrand::all();
    }

    public function update(
        $id,
        $company_id,
        $code,
        $name
    )
    {
        DB::beginTransaction();

        try {
            $productbrand = ProductBrand::where('id', '=', $id);

            $retval = $productbrand->update([
                'company_id' => $company_id,
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

    public function getProductBrandById($id)
    {
        return ProductBrand::find($id);
    }

    public function delete($id)
    {
        $productbrand = ProductBrand::find($id);

        return $productbrand->delete();
        
    }

    public function checkDuplicatedCode($crud_status, $id, $code)
    {
        switch($crud_status) {
            case 'create': 
                $count = ProductBrand::where('code', '=', $code)
                ->whereNull('deleted_at')
                ->count();
                return $count;
            case 'update':
                $count = ProductBrand::where('id', '<>' , $id)
                ->where('code', '=', $code)
                ->whereNull('deleted_at')
                ->count();
                return $count;
        }
    }
}