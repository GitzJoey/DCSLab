<?php

namespace App\Services\Impls;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

use App\Services\BrandService;

use App\Models\Brand;
use App\Models\User;

class BrandServiceImpl implements BrandService
{
    public function create(
        $company_id,
        $code,
        $name
    )
    {
        DB::beginTransaction();

        try {
            $productbrand = new Brand();
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
            $pb = Brand::whereCompanyId($companyId)->latest();
        } else {
            $pb = Brand::whereCompanyId($companyId)->where('name', 'like', '%'.$search.'%')->latest();
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
                return Brand::find($value);
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
            $productbrand = Brand::where('id', '=', $id);

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
        $productbrand = Brand::find($id);

        return $productbrand->delete();
    }

    public function isUniqueCode($code, $userId, $exceptId)
    {
        $usr = User::find($userId);
        $companies = $usr->companies()->pluck('company_id');

        $result = Brand::whereIn('company_id', $companies)->where('code', '=' , $code);

        if($exceptId)
            $result = $result->where('id', '<>', $exceptId);

        return $result->count() == 0 ? true:false;
    }
}
