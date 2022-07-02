<?php

namespace App\Services\Impls;

use App\Actions\RandomGenerator;
use App\Enums\RecordStatus;
use Exception;
use Illuminate\Container\Container;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\User;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Services\SupplierService;
use App\Services\UserService;
use App\Services\RoleService;
use App\Traits\CacheHelper;

class SupplierServiceImpl implements SupplierService
{
    use CacheHelper;

    public function __construct()
    {
        
    }
    
    public function create(
        array $supplierArr,
        array $pocArr,
        array $productsArr
    ): Supplier
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $usr = $this->createUserPOC($pocArr);

            $supplier = new Supplier();
            $supplier->company_id = $supplierArr['company_id'];
            $supplier->code = $supplierArr['code'];
            $supplier->name = $supplierArr['name'];
            $supplier->payment_term_type = $supplierArr['payment_term_type'];
            $supplier->payment_term = $supplierArr['payment_term'];
            $supplier->contact = $pocArr['contact'];
            $supplier->address = $pocArr['address'];
            $supplier->city = $pocArr['city'];
            $supplier->taxable_enterprise = $supplierArr['taxable_enterprise'];
            $supplier->tax_id = $pocArr['tax_id'];
            $supplier->remarks = $supplierArr['remarks'];
            $supplier->status = $supplierArr['status'];
            $supplier->user_id = $usr->id;

            $supplier->save();

            $sp = [];
            foreach($productsArr as $p) {
                $spe = new SupplierProduct();
                $spe->company_id = $supplierArr['company_id'];
                $spe->product_id = $p['product_id'];
                $spe->main_product = $p['main_product'];

                array_push($sp, $spe);
            }

            $supplier->supplierProducts()->saveMany($sp);

            DB::commit();

            $this->flushCache();

            return $supplier;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    private function createUserPOC(array $poc): User
    {
        $timer_start = microtime(true);

        try {
            $container = Container::getInstance();
            $userService = $container->make(UserService::class);
            $roleService = $container->make(RoleService::class);
    
            $roles = $roleService->readBy('name', 'POS-supplier')->id;
    
            $profile = [
                'first_name' => $poc['name'],
                'status' => RecordStatus::ACTIVE
            ];
    
            $userArr = [
                'name' => $poc['name'],
                'email' => $poc['email']
            ];

            $usr = $userService->create($userArr, $roles, $profile);
    
            return $usr;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.' '.'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function list(
        int $companyId, 
        string $search = '', 
        bool $paginate = true, 
        int $page = 1, 
        int $perPage = 10, 
        bool $useCache = true
    ): Paginator|Collection
    {
        $cacheKey = '';
        if ($useCache) {
            $cacheKey = 'read_'.$companyId.'-'.(empty($search) ? '[empty]':$search).'-'.$paginate.'-'.$page.'-'.$perPage;
            $cacheResult = $this->readFromCache($cacheKey);

            if (!is_null($cacheResult)) return $cacheResult;
        }

        $result = null;

        $timer_start = microtime(true);

        try {
            if (!$companyId) return null;

            if (empty($search)) {
                $suppliers = Supplier::with('user.profile', 'company', 'supplierProducts.product')->whereCompanyId($companyId)->latest();
            } else {
                $suppliers = Supplier::with('user.profile', 'company', 'supplierProducts.product')->whereCompanyId($companyId)
                    ->where('name', 'like', '%'.$search.'%')->latest();
            }
    
            if ($paginate) {
                $perPage = is_numeric($perPage) ? $perPage : Config::get('dcslab.PAGINATION_LIMIT');
                $result = $suppliers->paginate(abs($perPage));
            } else {
                $result = $suppliers->get();
            }

            if ($useCache) $this->saveToCache($cacheKey, $result);
            
            return $result;
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)'.($useCache ? ' (C)':' (DB)'));
        }
    }

    public function read(Supplier $supplier): Supplier
    {
        return $supplier->with('user.profile', 'company', 'supplierProducts.product')->first();        
    }

    public function update(
        Supplier $supplier,
        array $supplierArr,
        array $pocArr,
        array $productsArr
    ): Supplier
    {
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
                'status' => $supplierArr['status']
            ]);

            $supplier->supplierProducts()->delete();

            $newSP = [];
            foreach($productsArr as $product) {
                $newSPE = new SupplierProduct();
                $newSPE->company_id = $supplierArr['company_id'];
                $newSPE->supplier_id =$supplier->id;
                $newSPE->product_id = $product['product_id'];
                $newSPE->main_product = $product['main_product'];

                array_push($newSP, $newSPE);
            }

            $supplier->supplierProducts()->saveMany($newSP);

            DB::commit();

            $this->flushCache();

            return $supplier->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
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
                'status' => 0
            ]);

            $retval = true;
            
            DB::commit();

            $this->flushCache();

            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function generateUniqueCode(): string
    {
        $rand = new RandomGenerator();
        $code = $rand->generateAlphaNumeric(3).$rand->generateFixedLengthNumber(3);
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
