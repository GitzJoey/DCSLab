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

use App\Services\SupplierService;
use App\Services\UserService;
use App\Services\RoleService;

class SupplierServiceImpl implements SupplierService
{
    public function create(
        int $company_id,
        string $code,
        string $name,
        string $term,
        string $contact,
        string $address,
        string $city,
        bool $is_tax,
        string $tax_id,
        string $remarks,
        int $status,
        array $poc,
        array $products
    ): Supplier
    {
        DB::beginTransaction();

        try {
            $usr = $this->createUserPOC($poc);

            $supplier = new Supplier();
            $supplier->code = $code;
            $supplier->name = $name;
            $supplier->payment_term_type = $term;
            $supplier->contact = $contact;
            $supplier->address = $address;
            $supplier->city = $city;
            $supplier->is_tax = $is_tax;
            $supplier->tax_id = $tax_id;
            $supplier->remarks = $remarks;
            $supplier->status = $status;
            $supplier->user_id = $usr->id;

            $supplier->save();

            $supplier->products->attach($products);

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

        $usr = $userService->create($poc['name'], $poc['email'], '', $rolesId, $profile);

        return $usr;
    }

    public function read(int $companyId, string $search = '', bool $paginate = true, int $perPage = 10)
    {
        if (!$companyId) return null;

        if (empty($search)) {
            $suppliers = Supplier::with('user.profile', 'company', 'products')->whereCompanyId($companyId)->latest();
        } else {
            $suppliers = Supplier::with('user.profile', 'company', 'products')->whereCompanyId($companyId)
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
        string $code,
        string $name,
        string $term,
        string $contact,
        string $address,
        string $city,
        bool $is_tax,
        string $tax_id,
        string $remarks,
        int $status,
        array $poc,
        array $products
    ): Supplier
    {
        DB::beginTransaction();

        try {
            $supplier = Supplier::where('id', '=', $id);

            $supplier->update([
                'code' => $code,
                'name' => $name,
                'term' => $term,
                'contact' => $contact,
                'address' => $address,
                'city' => $city,
                'taxable_enterprise' => $is_tax,
                'tax_id' => $tax_id,
                'remarks' => $remarks,
                'status' => $status
            ]);

            DB::commit();

            return $supplier;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }


    public function delete(int $id): bool
    {
        $supplier = Supplier::find($id);

        $retval = $supplier->delete();

        return $retval;
    }

    public function generateUniqueCode(int $companyId): string
    {
        return '';
    }

    public function isUniqueCode(string $code, int $companyId, int $exceptId): bool
    {
        $result = Supplier::whereCompanyId($companyId)->where('code', '=' , $code);

        if($exceptId)
            $result = $result->where('id', '<>', $exceptId);

        return $result->count() == 0 ? true:false;
    }
}
