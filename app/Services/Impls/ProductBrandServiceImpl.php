<?php

namespace App\Services\Impls;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

use App\Services\ProductBrandService;

use App\Models\ProductBrand;
use App\Models\User;

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

            return $productbrand;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function read($companyId, $search = '', $paginate = true, $perPage = 10)
    {
        if (empty($search)) {
            $pb = ProductBrand::whereCompanyId($companyId);
        } else {
            $pb = ProductBrand::whereCompanyId($companyId)->where('name', 'like', '%'.$search.'%');
        }

        if ($paginate) {
            $perPage = is_numeric($perPage) ? $perPage : Config::get('const.DEFAULT.PAGINATION_LIMIT');
            return $pb->paginate($perPage);
        } else {
            return $pb->get();
        }
    }

    public function readBy($key, $value)
    {
        switch (strtoupper($key)) {
            case 'ID':
                return ProductBrand::find($value);
                break;
            default:
                break;
        }
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

            $productbrand->update([
                'company_id' => $company_id,
                'code' => $code,
                'name' => $name,
            ]);

            DB::commit();

            return $productbrand;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function delete($id)
    {
        $productbrand = ProductBrand::find($id);

        return $productbrand->delete();
    }

    public function isUniqueCode($code, $userId, $exceptId)
    {
        $usr = User::find($userId);
        $companies = $usr->companies()->pluck('company_id');

        $result = ProductBrand::whereIn('company_id', $companies)->where('code', '=' , $code);

        if($exceptId)
            $result = $result->where('id', '<>', $exceptId);

        return $result->count() == 0 ? true:false;
    }
}
