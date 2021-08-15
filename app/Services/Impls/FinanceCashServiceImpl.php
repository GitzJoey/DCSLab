<?php

namespace App\Services\Impls;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\FinanceCashService;
use App\Models\FinanceCash;

class FinanceCashServiceImpl implements FinanceCashService
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
            $cash = new FinanceCash();
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
        return FinanceCash::paginate();
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
            $cash = FinanceCash::where('id', '=', $id);

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
        $financecash = FinanceCash::find($id);

        return $financecash->delete();
        
    }
}