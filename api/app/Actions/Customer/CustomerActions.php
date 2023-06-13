<?php

namespace App\Actions\Customer;

use App\Actions\Randomizer\RandomizerActions;
use App\Actions\User\UserActions;
use App\Enums\RecordStatus;
use App\Enums\UserRoles;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Role;
use App\Traits\CacheHelper;
use Exception;
use Illuminate\Container\Container;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CustomerActions
{
    use CacheHelper;

    public function __construct()
    {
    }

    public function create(
        array $customerArr,
        array $customerAddressesArr,
        array $picArr,
    ): Customer {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $customer = new Customer();
            $customer->company_id = $customerArr['company_id'];
            $customer->customer_group_id = $customerArr['customer_group_id'];
            $customer->code = $customerArr['code'];
            $customer->is_member = $customerArr['is_member'];
            $customer->name = $customerArr['name'];
            $customer->zone = $customerArr['zone'];
            $customer->max_open_invoice = $customerArr['max_open_invoice'];
            $customer->max_outstanding_invoice = $customerArr['max_outstanding_invoice'];
            $customer->max_invoice_age = $customerArr['max_invoice_age'];
            $customer->payment_term_type = $customerArr['payment_term_type'];
            $customer->payment_term = $customerArr['payment_term'];
            $customer->taxable_enterprise = $customerArr['taxable_enterprise'];
            $customer->tax_id = $customerArr['tax_id'];
            $customer->remarks = $customerArr['remarks'];
            $customer->status = $customerArr['status'];
            $customer->save();

            $ca = [];
            foreach ($customerAddressesArr as $customer_address) {
                array_push($ca, new CustomerAddress([
                    'company_id' => $customer->company_id,
                    'customer_id' => $customer->id,
                    'address' => $customer_address['address'],
                    'city' => $customer_address['city'],
                    'contact' => $customer_address['contact'],
                    'is_main' => $customer_address['is_main'],
                    'remarks' => $customer_address['remarks'],
                ]));
            }

            $customer->customerAddresses()->saveMany($ca);

            if (! empty($picArr)) {
                $container = Container::getInstance();
                $userActions = $container->make(UserActions::class);

                $userArr = [
                    'name' => $picArr['name'],
                    'email' => $picArr['email'],
                    'password' => $picArr['password'],
                ];

                $rolesArr = [];
                array_push($rolesArr, Role::where('name', '=', UserRoles::POS_CUSTOMER->value)->first()->id);

                $profile = [
                    'first_name' => $picArr['first_name'],
                    'last_name' => $picArr['last_name'],
                    'status' => RecordStatus::ACTIVE,
                ];

                $user = $userActions->create($userArr, $rolesArr, $profile);

                $customer->user_id = $user->id;
                $customer->save();
            }

            DB::commit();

            $this->flushCache();

            return $customer;
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
        ?int $perPage = 10,
        array $with = [],
        bool $withTrashed = false,
        bool $useCache = true
    ): Paginator|Collection {
        $timer_start = microtime(true);

        try {
            $cacheKey = '';
            if ($useCache) {
                $cacheKey = 'read_'.$companyId.'-'.(empty($search) ? '[empty]' : $search).'-'.$paginate.'-'.$page.'-'.$perPage;
                $cacheResult = $this->readFromCache($cacheKey);

                if (! is_null($cacheResult)) {
                    return $cacheResult;
                }
            }

            $result = null;

            if (! $companyId) {
                return null;
            }

            $customer = count($with) != 0 ? Customer::with($with) : Customer::with('company', 'customerGroup', 'customerAddresses', 'user.profile');
            $customer = $customer->whereCompanyId($companyId);

            if ($withTrashed) {
                $customer = $customer->withTrashed();
            }

            if (empty($search)) {
                $customer = $customer->latest();
            } else {
                $customer = $customer->where('name', 'like', '%'.$search.'%')->latest();
            }

            if ($paginate) {
                $perPage = is_numeric($perPage) ? abs($perPage) : Config::get('dcslab.PAGINATION_LIMIT');
                $page = is_numeric($page) ? abs($page) : 1;

                $result = $customer->paginate(
                    perPage: $perPage,
                    page: $page
                );
            } else {
                $result = $customer->get();
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

    public function read(Customer $customer): Customer
    {
        return $customer->with('customerGroup', 'customerAddresses')->where('id', '=', $customer->id)->first();
    }

    public function update(
        Customer $customer,
        array $customerArr,
        array $customerAddressesArr,
        array $picArr,
    ): Customer {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $customer->customer_group_id = $customerArr['customer_group_id'];
            $customer->code = $customerArr['code'];
            $customer->is_member = $customerArr['is_member'];
            $customer->name = $customerArr['name'];
            $customer->zone = $customerArr['zone'];
            $customer->max_open_invoice = $customerArr['max_open_invoice'];
            $customer->max_outstanding_invoice = $customerArr['max_outstanding_invoice'];
            $customer->max_invoice_age = $customerArr['max_invoice_age'];
            $customer->payment_term_type = $customerArr['payment_term_type'];
            $customer->payment_term = $customerArr['payment_term'];
            $customer->taxable_enterprise = $customerArr['taxable_enterprise'];
            $customer->tax_id = $customerArr['tax_id'];
            $customer->remarks = $customerArr['remarks'];
            $customer->status = $customerArr['status'];
            $customer->save();

            $ca = [];
            foreach ($customerAddressesArr as $customer_address) {
                array_push($ca, [
                    'id' => $customer_address['id'],
                    'ulid' => $customer_address['ulid'],
                    'company_id' => $customer->company_id,
                    'customer_id' => $customer->id,
                    'address' => $customer_address['address'],
                    'city' => $customer_address['city'],
                    'contact' => $customer_address['contact'],
                    'is_main' => $customer_address['is_main'],
                    'remarks' => $customer_address['remarks'],
                ]);
            }

            $caIds = [];
            foreach ($ca as $caId) {
                array_push($caIds, $caId['id']);
            }

            $caIdsOld = $customer->customerAddresses()->pluck('id')->toArray();

            $deletedCustomerAddressIds = array_diff($caIdsOld, $caIds);

            foreach ($deletedCustomerAddressIds as $deletedCustomerAddressId) {
                $customerAddress = $customer->customerAddresses()->where('id', $deletedCustomerAddressId);
                $customerAddress->delete();
            }

            CustomerAddress::upsert(
                $ca,
                ['id'],
                [
                    'ulid',
                    'company_id',
                    'customer_id',
                    'address',
                    'city',
                    'contact',
                    'is_main',
                    'remarks',
                ]
            );

            if (! empty($picArr)) {
                $container = Container::getInstance();
                $userActions = $container->make(UserActions::class);

                $userArr = [
                    'name' => $picArr['name'],
                    'email' => $picArr['email'],
                    'password' => $picArr['password'],
                ];

                $rolesArr = [];
                array_push($rolesArr, Role::where('name', '=', UserRoles::POS_CUSTOMER->value)->first()->id);

                $profile = [
                    'first_name' => $picArr['first_name'],
                    'last_name' => $picArr['last_name'],
                    'status' => RecordStatus::ACTIVE,
                ];

                $user = $userActions->create($userArr, $rolesArr, $profile);

                $customer->user_id = $user->id;
                $customer->save();
            }

            DB::commit();

            $this->flushCache();

            return $customer->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function delete(Customer $customer): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $customer->delete();

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
        $result = Customer::whereCompanyId($companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
