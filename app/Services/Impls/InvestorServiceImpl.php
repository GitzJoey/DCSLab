<?php

namespace App\Services\Impls;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\InvestorService;
use App\Models\Investor;
use App\Models\User;

class InvestorServiceImpl implements InvestorService
{
    public function create(
        $company_id,
        $code,
        $name,
        $contact,
        $address,
        $city,
        $tax_number,
        $remarks,
        $status
    )
    {
        DB::beginTransaction();

        try {
            $investor = new Investor();
            $investor->company_id = $company_id;
            $investor->code = $code;
            $investor->name = $name;
            $investor->contact = $contact;
            $investor->address = $address;
            $investor->city = $city;
            $investor->tax_number = $tax_number;
            $investor->remarks = $remarks;
            $investor->status = $status;

            $investor->save();

            DB::commit();

            return $investor->hId;
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
        return Investor::whereIn('company_id', $company_list)->paginate();
    }

    public function getAllActiveInvestor()
    {
        return Investor::all();
    }

    public function update(
        $id,
        $company_id,
        $code,
        $name,
        $contact,
        $address,
        $city,
        $tax_number,
        $remarks,
        $status
    )
    {
        DB::beginTransaction();

        try {
            $investor = Investor::where('id', '=', $id);

            $retval = $investor->update([
                'company_id' => $company_id,
                'code' => $code,
                'name' => $name,
                'contact' => $contact,
                'address' => $address,
                'city' => $city,
                'tax_number' => $tax_number,
                'remarks' => $remarks,
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
        $investor = Investor::find($id);

        return $investor->delete();
    }

    public function checkDuplicatedCode($crud_status, $id, $code)
    {
        switch($crud_status) {
            case 'create': 
                $count = Investor::where('code', '=', $code)
                ->whereNull('deleted_at')
                ->count();
                return $count;
            case 'update':
                $count = investor::where('id', '<>' , $id)
                ->where('code', '=', $code)
                ->whereNull('deleted_at')
                ->count();
                return $count;
        }
    }
}