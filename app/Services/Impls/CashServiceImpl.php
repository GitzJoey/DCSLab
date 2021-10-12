<?php

namespace App\Services\Impls;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\CashService;
use App\Models\Cash;

class CashServiceImpl implements CashService
{
    public function create(
        $code,
        $name,
        $is_bank,
        $status
    )
    {
        DB::beginTransaction();

        try {
            $cash = new Cash();
            $cash->code = $code;
            $cash->name = $name;
            $cash->is_bank = $is_bank;
            $cash->status = $status;

            $cash->save();

            DB::commit();

            return $cash->hId;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }

    }

    public function read()
    {
        return Cash::paginate();
    }

    public function getAllActiveCash()
    {
        return Cash::where('status', '=', 1)->get();
    }

    public function update(
        $id,
        $code,
        $name,
        $is_bank,
        $status
    )
    {
        DB::beginTransaction();

        try {
            $cash = Cash::where('id', '=', $id);

            $retval = $cash->update([
                'code' => $code,
                'name' => $name,
                'is_bank' => $is_bank,
                'status' => $status
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
        $cash = Cash::find($id);

        return $cash->delete();
        
    }

    public function checkDuplicatedCode($crud_status, $id, $code)
    {
        switch($crud_status) {
            case 'create': 
                $count = Cash::where('code', '=', $code)
                ->whereNull('deleted_at')
                ->count();
                return $count;
            case 'update':
                $count = Cash::where('id', '<>' , $id)
                ->where('code', '=', $code)
                ->whereNull('deleted_at')
                ->count();
                return $count;
        }
    }
}