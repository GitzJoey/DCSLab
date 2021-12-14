<?php

namespace App\Services\Impls;

use Exception;
use App\Models\Capital;
use App\Models\CapitalGroup;
use App\Models\Cash;
use App\Models\Investor;
use App\Services\CapitalService;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class CapitalServiceImpl implements CapitalService
{
    public function create(
        $company_id,
        $ref_number,
        $investor_id,
        $group_id,
        $cash_id,
        $date,
        $capital_status,
        $amount,
        $remarks,
    )
    {

        DB::beginTransaction();

        try {
            $capital = new Capital();
            $capital->company_id = $company_id;
            $capital->ref_number = $ref_number;
            $capital->investor_id = $investor_id;
            $capital->group_id = $group_id;
            $capital->cash_id = $cash_id;
            $capital->date = $date;
            $capital->capital_status = $capital_status;
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

    public function read($userId)
    {
        return Capital::with('investor', 'group', 'cash', 'company')->bySelectedCompany()->paginate();
    }

    public function update(
        $id,
        $company_id,
        $ref_number,
        $investor_id,
        $group_id,
        $cash_id,
        $date,
        $capital_status,
        $amount,
        $remarks,
    )
    {
        DB::beginTransaction();

        try {
            $capital = Capital::where('id', '=', $id);

            $retval = $capital->update([
                'company_id' => $company_id,
                'ref_number' => $ref_number,
                'investor_id' => $investor_id,
                'group_id' => $group_id,
                'cash_id' => $cash_id,
                'date' => $date,
                'capital_status' => $capital_status,
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
        return Investor::find($id);
    }

    public function getCapitalGroupById($id)
    {
        return CapitalGroup::find($id);
    }

    public function getCashById($id)
    {
        return Cash::find($id);
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