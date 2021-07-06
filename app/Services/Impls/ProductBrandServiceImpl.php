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
        $code,
        $name
    )
    {
        DB::beginTransaction();

        try {
            $productbrand = new ProductBrand();
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


    public function update(
        $id,
        $code,
        $name
    )
    {
        DB::beginTransaction();

        try {
            $productbrand = ProductBrand::where('id', '=', $id);

            $retval = $productbrand->update([
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

    public function getCompanyById($id)
    {
        return ProductBrandService::find($id);
    }


    public function delete($id)
    {

        
    }

}