<?php

namespace App\Services\Impls;

use App\Actions\RandomGenerator;

use Exception;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\User;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Services\SupplierService;
use App\Services\UserService;
use App\Services\RoleService;

class SupplierServiceImpl implements SupplierService
{
    public function create(
        int $company_id,
        string $code,
        string $name,
        string $payment_term_type,
        ?int $payment_term = 0,
        ?string $contact = null,
        ?string $address = null,
        ?string $city = null,
        bool $taxable_enterprise,
        string $tax_id,
        ?string $remarks = null,
        int $status,
        array $poc,
        array $products
    ): ?Supplier
    {
        DB::beginTransaction();

        try {
            if ($code == Config::get('const.DEFAULT.KEYWORDS.AUTO')) {
                $code = $this->generateUniqueCode($company_id);
            }

            $usr = $this->createUserPOC($poc);

            $supplier = new Supplier();
            $supplier->company_id = $company_id;
            $supplier->code = $code;
            $supplier->name = $name;
            $supplier->payment_term_type = $payment_term_type;
            $supplier->payment_term = $payment_term;
            $supplier->contact = $contact;
            $supplier->address = $address;
            $supplier->city = $city;
            $supplier->taxable_enterprise = $taxable_enterprise;
            $supplier->tax_id = $tax_id;
            $supplier->remarks = $remarks;
            $supplier->status = $status;
            $supplier->user_id = $usr->id;

            $supplier->save();

            $sp = [];
            foreach($products as $p) {
                $spe = new SupplierProduct();
                $spe->company_id = $company_id;
                $spe->product_id = $p['product_id'];
                $spe->main_product = $p['main_product'];

                array_push($sp, $spe);
            }

            $supplier->supplierProducts()->saveMany($sp);

            DB::commit();

            return $supplier;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    private function createUserPOC(array $poc): User
    {
        $container = Container::getInstance();
        $userService = $container->make(UserService::class);
        $roleService = $container->make(RoleService::class);

        $rolesId = $roleService->readBy('name', 'POS-supplier')->id;

        $profile = [
            'first_name' => $poc['name'],
            'status' => 1
        ];

        $usr = $userService->create($poc['name'], $poc['email'], '', [$rolesId], $profile);

        return $usr;
    }

    public function read(int $companyId, string $search = '', bool $paginate = true, int $perPage = 10)
    {
        if (!$companyId) return null;

        if (empty($search)) {
            $suppliers = Supplier::with('user.profile', 'company', 'supplierProducts.product')->whereCompanyId($companyId)->latest();
        } else {
            $suppliers = Supplier::with('user.profile', 'company', 'supplierProducts.product')->whereCompanyId($companyId)
                ->where('name', 'like', '%'.$search.'%')->latest();
        }

        if ($paginate) {
            $perPage = is_numeric($perPage) ? $perPage : Config::get('const.DEFAULT.PAGINATION_LIMIT');
            return $suppliers->paginate($perPage);
        } else {
            return $suppliers->get();
        }
    }

    public function update(
        int $id,
        int $company_id,
        string $code,
        string $name,
        string $payment_term_type,
        ?int $payment_term = 0,
        ?string $contact = null,
        ?string $address = null,
        ?string $city = null,
        bool $taxable_enterprise,
        string $tax_id,
        ?string $remarks = null,
        int $status,
        array $poc,
        array $products
    ): ?Supplier
    {
        DB::beginTransaction();

        try {
            $supplier = Supplier::find($id);

            if ($code == Config::get('const.DEFAULT.KEYWORDS.AUTO')) {
                $code = $this->generateUniqueCode($company_id);
            }

            $supplier->update([
                'code' => $code,
                'name' => $name,
                'payment_term_type' => $payment_term_type,
                'payment_term' => $payment_term,
                'contact' => $contact,
                'address' => $address,
                'city' => $city,
                'taxable_enterprise' => $taxable_enterprise,
                'tax_id' => $tax_id,
                'remarks' => $remarks,
                'status' => $status
            ]);

            $supplier->supplierProducts()->delete();

            $newSP = [];
            if (!empty($products)) {
                $newSPE = new SupplierProduct();
                $newSPE->company_id = $company_id;
                $newSPE->supplier_id =$supplier->id;
                $newSPE->product_id = $products['product_id'];
                $newSPE->product_id = $products['main_product'];
            
                array_push($newSP, $newSPE); 
            }

            $supplier->supplierProducts()->saveMany($newSP);

            DB::commit();

            return $supplier->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function delete(int $id): bool
    {
        try {
            $supplier = Supplier::find($id);

            $supplier->supplierProducts()->delete();
            $supplier->delete();

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function generateUniqueCode(int $companyId): string
    {
        $rand = new RandomGenerator();
        
        do {
            $code = $rand->generateAlphaNumeric(3).$rand->generateFixedLengthNumber(3);
        } while (!$this->isUniqueCode($code, $companyId));

        return $code;
    }

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool
    {
        $result = Supplier::whereCompanyId($companyId)->where('code', '=' , $code);

        if($exceptId)
            $result = $result->where('id', '<>', $exceptId);

        return $result->count() == 0 ? true:false;
    }
}
