<?php

namespace App\Services\Impls;

use Exception;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\SupplierProduct;

use App\Services\SupplierService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class SupplierServiceImpl implements SupplierService
{
    public function create(
        $company_id,
        $code,
        $name,
        $payment_term_type,
        $contact,
        $address,
        $city,
        $taxable_enterprise,
        $tax_id,
        $remarks,
        $status,
        $supplier_products
    )
    {
        DB::beginTransaction();

        try {
            $supplier = new Supplier();
            $supplier->company_id = $company_id;
            $supplier->code = $code;
            $supplier->name = $name;
            $supplier->payment_term_type = $payment_term_type;
            $supplier->contact = $contact;
            $supplier->address = $address;
            $supplier->city = $city;
            $supplier->taxable_enterprise = $taxable_enterprise;
            $supplier->tax_id = $tax_id;
            $supplier->remarks = $remarks;
            $supplier->status = $status;
            $supplier->save();

            if (empty($supplier_products) === false) {
                $products = [];
                foreach ($supplier_products as $supplier_product) {
                    array_push($products, new SupplierProduct(array (
                        'company_id' => $company_id,
                        'supplier_id' => $supplier->id,
                        'product_id' => $supplier_product['product_id']
                    )));
                }
                $supplier->supplier_product()->saveMany($products);
            }

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
        return Supplier::with('company', 'supplier_product.product.productGroup', 'supplier_product.product.brand')
            ->bySelectedCompany()
            ->paginate();
    }

    public function getAllSupplier()
    {
        return Supplier::bySelectedCompany()->get();
    }

    public function update(
        $id,
        $company_id,
        $code,
        $name,
        $payment_term_type,
        $contact,
        $address,
        $city,
        $taxable_enterprise,
        $tax_id,
        $remarks,
        $status,
        $supplier_products
    )
    {
        DB::beginTransaction();

        try {
            $supplier = Supplier::where('id', '=', $id);

            $retval = $supplier->update([
                'company_id' => $company_id,
                'code' => $code,
                'name' => $name,
                'payment_term_type' => $payment_term_type,
                'contact' => $contact,
                'address' => $address,
                'city' => $city,
                'taxable_enterprise' => $taxable_enterprise,
                'tax_id' => $tax_id,
                'remarks' => $remarks,
                'status' => $status
            ]);

            $products = [];
            foreach ($supplier_products as $supplier_product) {
                array_push($products, array(
                    'id' => $supplier_product['id'],
                    'company_id' => $company_id,
                    'supplier_id' => $id,
                    'product_id' => $supplier_product['product_id']
                ));
            }

            $productIds = [];
            foreach ($products as $product)
            {
                array_push($productIds, $product['id']);
            }

            $supplier = Supplier::find($id);
            $productIdsOld = $supplier->supplier_product()->pluck('id')->ToArray();

            $deletedSupplierProductIds = [];
            $deletedSupplierProductIds = array_diff($productIdsOld, $productIds);

            foreach ($deletedSupplierProductIds as $deletedSupplierProductId) {
                $supplierProduct = SupplierProduct::find($deletedSupplierProductId);
                $retval = $supplierProduct->delete();
            }

            if (empty($products) === false) {
                $retval = SupplierProduct::upsert(
                    $products, 
                    [
                        'id'
                    ], 
                    [
                        'product_id'
                    ]
                );
            }

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
        $supplier = Supplier::find($id);

        return $supplier->delete();
    }

    public function checkDuplicatedCode($crud_status, $id, $code)
    {
        switch($crud_status) {
            case 'create': 
                $count = Supplier::where('code', '=', $code)
                ->whereNull('deleted_at')
                ->count();
                return $count;
            case 'update':
                $count = Supplier::where('id', '<>' , $id)
                ->where('code', '=', $code)
                ->whereNull('deleted_at')
                ->count();
                return $count;
        }
    }
}