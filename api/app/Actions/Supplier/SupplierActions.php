<?php

namespace App\Actions\Supplier;

use App\Actions\Randomizer\RandomizerActions;
use App\Actions\User\UserActions;
use App\Enums\RecordStatus;
use App\Enums\UserRoles;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Traits\CacheHelper;
use Exception;
use Illuminate\Container\Container;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SupplierActions
{
    use CacheHelper;

    public function __construct()
    {
    }

    public function create(
        array $supplierArr,
        array $picArr,
        array $productsArr
    ): Supplier {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $supplier = new Supplier();
            $supplier->company_id = $supplierArr['company_id'];
            $supplier->code = $supplierArr['code'];
            $supplier->name = $supplierArr['name'];
            $supplier->payment_term_type = $supplierArr['payment_term_type'];
            $supplier->payment_term = $supplierArr['payment_term'];
            $supplier->contact = $supplierArr['contact'];
            $supplier->address = $supplierArr['address'];
            $supplier->city = $supplierArr['city'];
            $supplier->taxable_enterprise = $supplierArr['taxable_enterprise'];
            $supplier->tax_id = $supplierArr['tax_id'];
            $supplier->remarks = $supplierArr['remarks'];
            $supplier->status = $supplierArr['status'];
            $supplier->save();

            if (! empty($picArr)) {
                $container = Container::getInstance();
                $userActions = $container->make(UserActions::class);

                $userArr = [
                    'name' => $picArr['name'],
                    'email' => $picArr['email'],
                    'password' => $picArr['password'],
                ];

                $rolesArr = [];
                array_push($rolesArr, Role::where('name', '=', UserRoles::POS_SUPPLIER->value)->first()->id);

                $profile = [
                    'first_name' => $picArr['first_name'],
                    'last_name' => $picArr['last_name'],
                    'status' => RecordStatus::ACTIVE,
                ];

                $user = $userActions->create($userArr, $rolesArr, $profile);

                $supplier->user_id = $user->id;
                $supplier->save();
            }

            $supplierProductArr = [];
            foreach ($productsArr as $product) {
                $supplierProduct = new SupplierProduct();
                $supplierProduct->company_id = $supplierArr['company_id'];
                $supplierProduct->product_id = $product['product_id'];
                $supplierProduct->main_product = $product['main_product'];

                array_push($supplierProductArr, $supplierProduct);
            }

            $supplier->supplierProducts()->saveMany($supplierProductArr);

            DB::commit();

            $this->flushCache();

            return $supplier;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function readAny(
        int $companyId,
        string $search = '',
        bool $paginate = true,
        int $page = 1,
        int $perPage = 10,
        array $with = [],
        bool $withTrashed = false,
        bool $useCache = true
    ): Paginator|Collection {
        $cacheKey = '';
        if ($useCache) {
            $cacheKey = 'read_'.$companyId.'-'.(empty($search) ? '[empty]' : $search).'-'.$paginate.'-'.$page.'-'.$perPage;
            $cacheResult = $this->readFromCache($cacheKey);

            if (! is_null($cacheResult)) {
                return $cacheResult;
            }
        }

        $result = null;

        $timer_start = microtime(true);

        try {
            if (! $companyId) {
                return null;
            }

            $supplier = count($with) != 0 ? Supplier::with($with) : Supplier::with('company', 'user.profile', 'supplierProducts.product');
            $supplier = $supplier->whereCompanyId($companyId);

            if ($withTrashed) {
                $supplier = $supplier->withTrashed();
            }

            if (empty($search)) {
                $supplier = $supplier->latest();
            } else {
                $supplier = $supplier->where('name', 'like', '%'.$search.'%')->latest();
            }

            if ($paginate) {
                $perPage = is_numeric($perPage) ? $perPage : Config::get('dcslab.PAGINATION_LIMIT');
                $result = $supplier->paginate(abs($perPage));
            } else {
                $result = $supplier->get();
            }

            if ($useCache) {
                $this->saveToCache($cacheKey, $result);
            }

            return $result;
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)'.($useCache ? ' (C)' : ' (DB)'));
        }
    }

    public function read(Supplier $supplier): Supplier
    {
        return $supplier->with('user.profile', 'company', 'supplierProducts.product')->first();
    }

    public function update(
        Supplier $supplier,
        array $supplierArr,
        array $picArr,
        array $productsArr
    ): Supplier {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $supplier->update([
                'code' => $supplierArr['code'],
                'name' => $supplierArr['name'],
                'payment_term_type' => $supplierArr['payment_term_type'],
                'payment_term' => $supplierArr['payment_term'],
                'contact' => $supplierArr['contact'],
                'address' => $supplierArr['address'],
                'city' => $supplierArr['city'],
                'taxable_enterprise' => $supplierArr['taxable_enterprise'],
                'tax_id' => $supplierArr['tax_id'],
                'remarks' => $supplierArr['remarks'],
                'status' => $supplierArr['status'],
            ]);

            if (! empty($picArr)) {
                $container = Container::getInstance();
                $userActions = $container->make(UserActions::class);

                $userArr = [
                    'name' => $picArr['name'],
                    'email' => $picArr['email'],
                    'password' => $picArr['password'],
                ];

                $rolesArr = [];
                array_push($rolesArr, Role::where('name', '=', UserRoles::POS_SUPPLIER->value)->first()->id);

                $profile = [
                    'first_name' => $picArr['first_name'],
                    'last_name' => $picArr['last_name'],
                    'status' => RecordStatus::ACTIVE,
                ];

                $user = $userActions->create($userArr, $rolesArr, $profile);

                $supplier->user_id = $user->id;
                $supplier->save();
            }

            $supplier->supplierProducts()->delete();
            $newSupplierProducts = [];
            foreach ($productsArr as $product) {
                $newSupplierProduct = new SupplierProduct();
                $newSupplierProduct->company_id = $supplierArr['company_id'];
                $newSupplierProduct->supplier_id = $supplier->id;
                $newSupplierProduct->product_id = $product['product_id'];
                $newSupplierProduct->main_product = $product['main_product'];

                array_push($newSupplierProducts, $newSupplierProduct);
            }
            $supplier->supplierProducts()->saveMany($newSupplierProducts);

            DB::commit();

            $this->flushCache();

            return $supplier->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function delete(Supplier $supplier): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;

        try {
            $supplier->supplierProducts()->delete();
            $supplier->delete();

            $supplier->user()->with('profile')->first()->profile()->update([
                'status' => 0,
            ]);

            $retval = true;

            DB::commit();

            $this->flushCache();

            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function generateUniqueCode(): string
    {
        $rand = new RandomizerActions();
        $code = $rand->generateAlpha().$rand->generateNumeric();

        return $code;
    }

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool
    {
        $result = Supplier::whereCompanyId($companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
