<?php

namespace App\Services\Impls;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\CapitalGroupService;
use App\Models\CapitalGroup;

class CapitalGroupServiceImpl implements CapitalGroupService
{
    public function create(
        $code,
        $name
    )
    {
        DB::beginTransaction();

        try {
            $capitalgroup = new CapitalGroup();
            $capitalgroup->code = $code;
            $capitalgroup->name = $name;

            $capitalgroup->save();

            DB::commit();

            return $capitalgroup->hId;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function read()
    {
        return CapitalGroup::paginate();
    }

    public function update(
        $id,
        $code,
        $name
    )
    {
        DB::beginTransaction();

        try {
            $capitalgroup = CapitalGroup::where('id', '=', $id);
    
            $retval = $capitalgroup->update([
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

    public function getCapitalGroupById($id)
    {
        return CapitalGroup::find($id);
    }

    public function delete($id)
    {
        $capitalgroup = CapitalGroup::find($id);

        return $capitalgroup->delete();
    }

    public function checkDuplicatedCode($crud_status, $id, $code)
    {
        switch($crud_status) {
            case 'create': 
                $count = CapitalGroup::where('code', '=', $code)
                ->whereNull('deleted_at')
                ->count();
                return $count;
            case 'update':
                $count = CapitalGroup::where('id', '<>' , $id)
                ->where('code', '=', $code)
                ->whereNull('deleted_at')
                ->count();
                return $count;
        }
    }
}