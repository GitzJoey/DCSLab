<?php

namespace App\Services\Impls;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\CapitalGroupService;
use App\Models\CapitalGroup;
use App\Models\User;

class CapitalGroupServiceImpl implements CapitalGroupService
{
    public function create(
        $company_id,
        $code,
        $name
    )
    {
        DB::beginTransaction();

        try {
            $capitalgroup = new CapitalGroup();
            $capitalgroup->company_id = $company_id;
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

    public function read($userId)
    {
        $user = User::find($userId);
        $company_list = $user->companies()->pluck('company_id');
        return CapitalGroup::whereIn('company_id', $company_list)->paginate();
    }

    public function getAllActiveCapitalGroup()
    {
        return CapitalGroup::all();
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
            $capitalgroup = CapitalGroup::where('id', '=', $id);
    
            $retval = $capitalgroup->update([
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