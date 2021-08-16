<?php

namespace App\Services\Impls;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\PurchaseSupplierService;
use App\Models\PurchaseSupplier;

class PurchaseSupplierServiceImpl implements PurchaseSupplierService
{
    public function create(
        $code,
        $name,
        $term,
        $contact,
        $address,
        $city,
        $is_tax,
        $tax_number,
        $remarks,
        $status
    )
    {
        DB::beginTransaction();

        try {
            $supplier = new PurchaseSupplier();
            $supplier->code = $code;
            $supplier->name = $name;
            $supplier->term = $term;
            $supplier->contact = $contact;
            $supplier->address = $address;
            $supplier->city = $city;
            $supplier->is_tax = $is_tax;
            $supplier->tax_number = $tax_number;
            $supplier->remarks = $remarks;
            $supplier->status = $status;

            $supplier->save();

            DB::commit();

            return $supplier->hId;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }

    }

    public function read()
    {
        return PurchaseSupplier::paginate();
    }


    public function update(
        $id,
        $code,
        $name,
        $term,
        $contact,
        $address,
        $city,
        $is_tax,
        $tax_number,
        $remarks,
        $status
    )
    {
        DB::beginTransaction();

        try {
            $supplier = PurchaseSupplier::where('id', '=', $id);

            $retval = $supplier->update([
                'code' => $code,
                'name' => $name,
                'term' => $term,
                'contact' => $contact,
                'address' => $address,
                'city' => $city,
                'is_tax' => $is_tax,
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
        $purchasesupplier = PurchaseSupplier::find($id);

        return $purchasesupplier->delete();
        
    }
}