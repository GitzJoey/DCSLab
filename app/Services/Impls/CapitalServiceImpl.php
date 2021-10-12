<?php

namespace App\Services\Impls;

use Exception;
use App\Models\Capital;
use App\Services\CapitalService;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class CapitalServiceImpl implements CapitalService
{
    public function create(
        $ref_number,
        $investor_id,
        $group_id,
        $cash_id,
        $date,
        $amount,
        $remarks,
    )
    {

        DB::beginTransaction();

        try {
            $capital = new Capital();
            $capital->ref_number = $ref_number;
            $capital->investor_id = $investor_id;
            $capital->group_id = $group_id;
            $capital->cash_id = $cash_id;
            $capital->date = $date;
            $capital->amount = $amount;
            $capital->remarks = $remarks;

            $capital->save();

            DB::commit();

            return $capital->hId;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }

    }

    public function read()
    {
        return Capital::with('investor', 'group', 'cash')->paginate();
    }

    public function update(
        $id,
        $investor,
        $capital_group,
        $cash_id,
        $ref_number,
        $date,
        $amount,
        $remarks,
    )
    {
        DB::beginTransaction();

        try {
            $capital = Capital::where('id', '=', $id);

            $retval = $capital->update([
                'investor' => $investor,
                'capital_group' => $capital_group,
                'cash_id' => $cash_id,
                'ref_number' => $ref_number,
                'date' => $date,
                'amount' => $amount,
                'remarks' => $remarks,
            ]);

            DB::commit();

            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function getInvestorById($id)
    {
        return Capital::find($id);
    }

    public function getCapitalGroupById($id)
    {
        return Capital::find($id);
    }

    public function getCashById($id)
    {
        return Capital::find($id);
    }


    public function delete($id)
    {
        $capital = Capital::find($id);

        return $capital->delete();

    }

    public function checkDuplicatedCode($crud_status, $id, $code)
    {
        switch($crud_status) {
            case 'create':
                $count = Capital::where('code', '=', $code)
                ->whereNull('deleted_at')
                ->count();
                return $count;
            case 'update':
                $count = Capital::where('id', '<>' , $id)
                ->where('code', '=', $code)
                ->whereNull('deleted_at')
                ->count();
                return $count;
        }
    }
}
