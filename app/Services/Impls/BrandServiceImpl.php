<?php

namespace App\Services\Impls;

use App\Actions\RandomGenerator;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

use App\Services\BrandService;

use App\Models\Brand;
use App\Models\User;

class BrandServiceImpl implements BrandService
{
    public function create(int $company_id, string $code, string $name): Brand
    {
        DB::beginTransaction();

        try {
            if ($code == Config::get('const.DEFAULT.KEYWORDS.AUTO')) {
                $code = $this->generateUniqueCode($company_id);
            }

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

    public function read(int $companyId, string $search = '', bool $paginate = true, ?int $perPage = 10)
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

    public function readBy(string $key, string $value)
    {
        switch (strtoupper($key)) {
            case 'ID':
                return Brand::find($value);
                break;
            default:
                break;
        }
    }

    public function update(int $id, int $company_id, string $code, string $name): Brand
    {
        DB::beginTransaction();

        try {
            $productbrand = Brand::find($id);

            $productbrand->update([
                'company_id' => $company_id,
                'code' => $code,
                'name' => $name,
            ]);

            DB::commit();

            return $productbrand->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function delete(int $id): bool
    {
        $productbrand = Brand::find($id);

        return $productbrand->delete();
    }

    public function generateUniqueCode(int $companyId): string
    {
        $rand = new RandomGenerator();
        
        do {
            $code = $rand->generateAlphaNumeric(3).$rand->generateFixedLengthNumber(3);
        } while (!$this->isUniqueCode($code, $companyId));

        return $code;
    }

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool
    {
        $result = Brand::whereCompanyId($companyId)->where('code', '=' , $code);

        if($exceptId)
            $result = $result->where('id', '<>', $exceptId);

        return $result->count() == 0 ? true:false;
    }
}
