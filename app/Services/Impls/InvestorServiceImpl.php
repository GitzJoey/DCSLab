<?php

namespace App\Services\Impls;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\InvestorService;
use App\Models\Investor;

class InvestorServiceImpl implements InvestorService
{
    public function create(
        $code,
        $name,
        $term,
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
            $investor->code = $code;
            $investor->name = $name;
            $investor->term = $term;
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

    public function read()
    {
        return Investor::paginate();
    }


    public function update(
        $id,
        $code,
        $name,
        $term,
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
                'code' => $code,
                'name' => $name,
                'term' => $term,
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