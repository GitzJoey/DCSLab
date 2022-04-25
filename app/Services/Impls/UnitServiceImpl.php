<?php

namespace App\Services\Impls;

use App\Actions\RandomGenerator;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\UnitService;
use App\Models\Unit;

class UnitServiceImpl implements UnitService
{
    public function __construct()
    {
        
    }
    
    public function create(
        int $company_id,
        string $code,
        string $name,
        int $category
    ): ?Unit
    {
        DB::beginTransaction();

        try {
            if ($code == Config::get('const.DEFAULT.KEYWORDS.AUTO')) {
                $code = $this->generateUniqueCode($company_id);
            }

            $unit = new Unit();
            $unit->company_id = $company_id;
            $unit->code = $code;
            $unit->name = $name;
            $unit->category = $category;

            $unit->save();

            DB::commit();

            return $unit;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.auth()->user()->id.'] '.__METHOD__.$e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function read(int $companyId, int $category, string $search = '', bool $paginate = true, ?int $perPage = 10)
    {
        $unit = Unit::whereCompanyId($companyId);
         
        if ($category == Config::get('const.ENUMS.UNIT_CATEGORY.PRODUCTS')) {
            $unit = $unit->where('category', '<>', Config::get('const.ENUMS.UNIT_CATEGORY.SERVICES'));
        } else if ($category == Config::get('const.ENUMS.UNIT_CATEGORY.SERVICES')) {
            $unit = $unit->where('category', '<>', Config::get('const.ENUMS.UNIT_CATEGORY.PRODUCTS'));
        } else {

        }
        
        if (empty($search)) {
            $unit = $unit->latest();
        } else {
            $unit = $unit->where('name', 'like', '%'.$search.'%')->latest();
        }

        if ($paginate) {
            $perPage = is_numeric($perPage) ? $perPage : Config::get('const.DEFAULT.PAGINATION_LIMIT');
            return $unit->paginate($perPage);
        } else {
            return $unit->get();
        }
    }

    public function readBy(string $key, string $value)
    {
        switch(strtoupper($key)) {
            case 'ID':
                return Unit::find($value);
            case 'CATEGORY':
                return Unit::where('category', '=', $value)->get();
            default:
                return null;
        }
    }

    public function update(
        int $id,
        int $company_id,
        string $code,
        string $name,
        int $category
    ): ?Unit
    {
        DB::beginTransaction();

        try {
            $unit = Unit::find($id);

            if ($code == Config::get('const.DEFAULT.KEYWORDS.AUTO')) {
                $code = $this->generateUniqueCode($company_id);
            }

            $unit->update([
                'company_id' => $company_id,
                'code' => $code,
                'name' => $name,
                'category' => $category,
            ]);
    
            DB::commit();
    
            return $unit->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.auth()->user()->id.'] '.__METHOD__.$e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function delete(int $id): bool
    {
        $unit = Unit::find($id);

        return $unit->delete();
    }

    public function generateUniqueCode(int $companyId): string
    {
        $rand = new RandomGenerator();
        $code = '';
        
        do {
            $code = $rand->generateAlphaNumeric(3).$rand->generateFixedLengthNumber(3);
        } while (!$this->isUniqueCode($code, $companyId));

        return $code;
    }

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool
    {
        $result = Unit::whereCompanyId($companyId)->where('code', '=' , $code);

        if($exceptId)
            $result = $result->where('id', '<>', $exceptId);

        return $result->count() == 0 ? true:false;
    }
}