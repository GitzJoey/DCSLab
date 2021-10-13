<?php

namespace App\Services\Impls;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\ExpenseGroupService;
use App\Models\ExpenseGroup;

class ExpenseGroupServiceImpl implements ExpenseGroupService
{
    public function create(
        $company_id,
        $code,
        $name,
    )
    {
        DB::beginTransaction();

        try {
            $warehouse = new ExpenseGroup();
            $warehouse->company_id = $company_id;
            $warehouse->code = $code;
            $warehouse->name = $name;

            $warehouse->save();

            DB::commit();

            return $warehouse->hId;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function read()
    {
        return ExpenseGroup::with('company')->paginate();
    }

    public function update(
        $id,
        $company_id,
        $code,
        $name,
    )
    {
        DB::beginTransaction();

        try {
            $warehouse = ExpenseGroup::where('id', '=', $id);

            $retval = $warehouse->update([
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


    public function delete($id)
    {
        $warehouse = ExpenseGroup::find($id);

        return $warehouse->delete();
        
    }

    public function checkDuplicatedCode($crud_status, $id, $code)
    {
        switch($crud_status) {
            case 'create': 
                $count = ExpenseGroup::where('code', '=', $code)
                ->whereNull('deleted_at')
                ->count();
                return $count;
            case 'update':
                $count = ExpenseGroup::where('id', '<>' , $id)
                ->where('code', '=', $code)
                ->whereNull('deleted_at')
                ->count();
                return $count;
        }
    }
}