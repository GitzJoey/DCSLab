<?php

namespace App\Services\Impls;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\UnitService;
use App\Models\Unit;

class UnitServiceImpl implements UnitService
{
    public function create(
        $company_id,
        $code,
        $name
    )
    {
        DB::beginTransaction();

        try {
            $unit = new Unit();
            $unit->company_id = $company_id;
            $unit->code = $code;
            $unit->name = $name;

            $unit->save();

            DB::commit();

            return $unit->hId;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function read()
    {
        return Unit::paginate();
    }

    public function getAllActiveUnit()
    {
        return Unit::all();
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
            $unit = Unit::where('id', '=', $id);
    
            $retval = $unit->update([
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

    public function getUnitById($id)
    {
        return Unit::find($id);
    }

    public function delete($id)
    {
        $unit = Unit::find($id);

        return $unit->delete();
        
    }

    public function checkDuplicatedCode($crud_status, $id, $code)
    {
        switch($crud_status) {
            case 'create': 
                $count = Unit::where('code', '=', $code)
                ->whereNull('deleted_at')
                ->count();
                return $count;
            case 'update':
                $count = Unit::where('id', '<>' , $id)
                ->where('code', '=', $code)
                ->whereNull('deleted_at')
                ->count();
                return $count;
        }
    }
}