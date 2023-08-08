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
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class CustomerActions
{
    use CacheHelper;
    use LoggerHelper;

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
                $userActions = app(UserActions::class);

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
        ?int $perPage = 10,
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

            $customer = count($with) != 0 ? Customer::with($with) : Customer::with(['company', 'customerGroup', 'customerAddresses']);

            $customer = $customer->whereCompanyId($companyId);

            if (empty($search)) {
                $customer = $customer->latest();
            } else {
                $customer = $customer->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('zone', 'like', '%'.$search.'%')
                        ->orWhereHas('customerAddresses', function ($query) use ($search) {
                            $query->where('address', 'like', '%'.$search.'%')
                                ->orWhere('city', 'like', '%'.$search.'%');
                        });
                }
                )->latest();
            }

            if ($withTrashed) {
                $customer = $customer->withTrashed();
            }

            if ($paginate) {
                $perPage = is_numeric($perPage) ? $perPage : Config::get('dcslab.PAGINATION_LIMIT');
                $result = $customer->paginate(abs($perPage));
            } else {
                $result = $customer->get();
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
                $userActions = app(UserActions::class);

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
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
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
        $result = Customer::whereCompanyId($companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
