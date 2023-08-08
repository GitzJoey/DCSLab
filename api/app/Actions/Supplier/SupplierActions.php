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
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SupplierActions
{
    use CacheHelper;
    use LoggerHelper;

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
                $userActions = app(UserActions::class);

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
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
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
        $timer_start = microtime(true);
        $recordsCount = 0;

        try {
            $cacheKey = 'readAny_'.$companyId.'_'.(empty($search) ? '[empty]' : $search).'-'.$paginate.'-'.$page.'-'.$perPage;
            if ($useCache) {
                $cacheResult = $this->readFromCache($cacheKey);

                if (! is_null($cacheResult)) {
                    return $cacheResult;
                }
            }

            $result = null;

            if (! $companyId) {
                return null;
            }

            $supplier = count($with) != 0 ? Supplier::with($with) : Supplier::with('company', 'user.profile', 'supplierProducts.product');
            $supplier = $supplier->whereCompanyId($companyId);

            if (empty($search)) {
                $supplier = $supplier->latest();
            } else {
                $supplier = $supplier->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('address', 'like', '%'.$search.'%')
                        ->orWhere('city', 'like', '%'.$search.'%');
                })->latest();
            }

            if ($withTrashed) {
                $supplier = $supplier->withTrashed();
            }

            if ($paginate) {
                $perPage = is_numeric($perPage) ? $perPage : Config::get('dcslab.PAGINATION_LIMIT');
                $result = $supplier->paginate(abs($perPage));
            } else {
                $result = $supplier->get();
            }

            $recordsCount = $result->count();

            $this->saveToCache($cacheKey, $result);

            return $result;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time, $recordsCount);
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
                $userActions = app(UserActions::class);

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
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(Supplier $supplier): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;

        try {
            $supplier->user()->with('profile')->first()->profile()->update([
                'status' => 0,
            ]);

            $retval = $supplier->delete();

            DB::commit();

            $this->flushCache();

            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function generateUniqueCode(): string
    {
        $rand = app(RandomizerActions::class);
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
