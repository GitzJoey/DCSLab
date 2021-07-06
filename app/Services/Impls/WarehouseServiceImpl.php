<?php

namespace App\Services\Impls;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\WarehouseService;
use App\Models\Warehouse;

class WarehouseServiceImpl implements WarehouseService
{
    public function create(
        $company_id,
        $code,
        $name,
        $address,
        $city,
        $contact,
        $remarks,
        $status
    )
    {
        DB::beginTransaction();

        try {
            $warehouse = new Warehouse();
            $warehouse->company_id = $company_id;
            $warehouse->code = $code;
            $warehouse->name = $name;
            $warehouse->address = $address;
            $warehouse->city = $city;
            $warehouse->contact = $contact;
            $warehouse->remarks = $remarks;
            $warehouse->status = $status;

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
        return Warehouse::with('company')->paginate();
    }


    public function update(
        $id,
        $company_id,
        $code,
        $name,
        $address,
        $city,
        $contact,
        $remarks,
        $status
    )
    {
        DB::beginTransaction();

        try {
            $warehouse = Warehouse::where('id', '=', $id);

            $retval = $warehouse->update([
                'code' => $code,
                'name' => $name,
                'address' => $address,
                'city' => $city,
                'contact' => $contact,
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

        
    }
}