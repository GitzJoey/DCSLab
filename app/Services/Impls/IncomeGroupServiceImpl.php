<?php

namespace App\Services\Impls;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\IncomeGroupService;
use App\Models\IncomeGroup;

class IncomeGroupServiceImpl implements IncomeGroupService
{
    public function create(
        $company_id,
        $code,
        $name,
    )
    {
        DB::beginTransaction();

        try {
            $incomegroup = new IncomeGroup();
            $incomegroup->company_id = $company_id;
            $incomegroup->code = $code;
            $incomegroup->name = $name;

            $incomegroup->save();

            DB::commit();

            return $incomegroup->hId;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function read()
    {
        return IncomeGroup::paginate();
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
            $incomegroup = IncomeGroup::where('id', '=', $id);

            $retval = $incomegroup->update([
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
        $incomegroup = IncomeGroup::find($id);

        return $incomegroup->delete();
    }

    public function checkDuplicatedCode($crud_status, $id, $code)
    {
        switch($crud_status) {
            case 'create': 
                $count = IncomeGroup::where('code', '=', $code)
                ->whereNull('deleted_at')
                ->count();
                return $count;
            case 'update':
                $count = IncomeGroup::where('id', '<>' , $id)
                ->where('code', '=', $code)
                ->whereNull('deleted_at')
                ->count();
                return $count;
        }
    }
}