<?php

namespace App\Services\Impls;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\HouseService;
use App\Models\House;

class HouseServiceImpl implements HouseService
{
    public function create(
        $code,
        $name,
        $status
    )
    {
        DB::beginTransaction();

        try {
            $house = new House();
            $house->code = $code;
            $house->name = $name;
            $house->status = $status;

            $house->save();

            DB::commit();

            return $house->hId;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function read()
    {
        return House::paginate();
    }

    public function update(
        $id,
        $code,
        $name,
        $status
    )
    {
        DB::beginTransaction();

        try {
            $house = House::where('id', '=', $id);

            $retval = $house->update([
                'code' => $code,
                'name' => $name,
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

    public function getHouseById($id)
    {
        return House::find($id);
    }

    public function delete($id)
    {

        
    }
}