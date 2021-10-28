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
            $expensegroup = new ExpenseGroup();
            $expensegroup->company_id = $company_id;
            $expensegroup->code = $code;
            $expensegroup->name = $name;

            $expensegroup->save();

            DB::commit();

            return $expensegroup->hId;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function read()
    {
        return ExpenseGroup::paginate();
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
            $expensegroup = ExpenseGroup::where('id', '=', $id);

            $retval = $expensegroup->update([
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


    public function delete($id)
    {
        $expensegroup = ExpenseGroup::find($id);

        return $expensegroup->delete();
        
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