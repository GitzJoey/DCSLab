<?php

namespace App\Services\Impls;

use App\Actions\RandomGenerator;
use App\Enums\ActiveStatus;
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
use Illuminate\Support\Facades\Cache;

class SupplierServiceImpl implements SupplierService
{
    public function __construct()
    {
        
    }
    
    public function create(
        int $company_id,
        string $code,
        string $name,
        string $payment_term_type,
        ?int $payment_term = null,
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
        $timer_start = microtime(true);

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
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.$e);
            return Config::get('const.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.' '.'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    private function createUserPOC(array $poc): User
    {
        $timer_start = microtime(true);

        try {
            $container = Container::getInstance();
            $userService = $container->make(UserService::class);
            $roleService = $container->make(RoleService::class);
    
            $rolesId = $roleService->readBy('name', 'POS-supplier')->id;
    
            $profile = [
                'first_name' => $poc['name'],
                'status' => ActiveStatus::ACTIVE
            ];
    
            $usr = $userService->create($poc['name'], $poc['email'], '', [$rolesId], $profile);
    
            return $usr;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.$e);
            return Config::get('const.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.' '.'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }        
    }

    public function read(int $companyId, string $search = '', bool $paginate = true, int $page, int $perPage = 10)
    {
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
                $perPage = is_numeric($perPage) ? $perPage : Config::get('const.DEFAULT.PAGINATION_LIMIT');
                return $suppliers->paginate(abs($perPage));
            } else {
                return $suppliers->get();
            }
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.$e);
            return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    private function readFromCache($key)
    {
        try {
            if (!Config::get('const.DEFAULT.DATA_CACHE.ENABLED')) return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');

            if (!Cache::has($key)) return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');

            return Cache::get($key);
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.$e);
            return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');
        } finally {
            Log::channel('cachehits')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.' Read Key: '.$key);
        }
    }

    private function saveToCache($key, $val)
    {
        try {
            if (empty($key)) return;

            Cache::tags([auth()->user()->id, __METHOD__])->add($key, $val, Config::get('const.DEFAULT.DATA_CACHE.CACHE_TIME.ENV'));
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.$e);
        } finally {
            Log::channel('cachehits')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.' Save Key: '.$key);
        }
    }

    private function flushCache()
    {
        try {
            Cache::tags([auth()->user()->id, __METHOD__])->flush();
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.$e);
        } finally {
            Log::channel('cachehits')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.' Cache Flushed for tags: '.(is_null(auth()->user()) ? '':auth()->user()->id).', '.__METHOD__);
        }
    }

    public function update(
        int $id,
        int $company_id,
        string $code,
        string $name,
        string $payment_term_type,
        ?int $payment_term = null,
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
        $timer_start = microtime(true);

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
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.$e);
            return Config::get('const.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function delete(int $id): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;
        try {
            $supplier = Supplier::find($id);

            if ($supplier) {
                $supplier->supplierProducts()->delete();
                $supplier->delete();
    
                $supplier->user()->with('profile')->first()->profile()->update([
                    'status' => 0
                ]);

                $retval = true;
            }
            DB::commit();

            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.$e);
            return Config::get('const.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function generateUniqueCode(int $companyId): string
    {
        $rand = new RandomGenerator();
        $code = '';
        
        do {
            $code = $rand->generateAlphaNumeric(3).$rand->generateFixedLengthNumber(3);
        } while (!$this->isUniqueCode($code, $companyId));

        return $code;
    }

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool
    {
        $timer_start = microtime(true);

        try {
            $result = Supplier::whereCompanyId($companyId)->where('code', '=' , $code);

            if($exceptId)
                $result = $result->where('id', '<>', $exceptId);
    
            return $result->count() == 0 ? true:false;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.$e);
            return Config::get('const.ERROR_RETURN_VALUE');
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->user()->id).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }
}
