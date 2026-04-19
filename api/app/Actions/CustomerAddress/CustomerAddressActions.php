<?php

namespace App\Actions\CustomerAddress;

use App\DTOs\ExecuteDTO;
use App\Models\CustomerAddress;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class CustomerAddressActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'customer',
    ];

    public function __construct()
    {
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,

        ?string $search,
        ?int $customerId,
        ?int $includeId,

        ?ExecuteDTO $execute
    ) {
        $query = CustomerAddress::select('customer_addresses.*')
            ->with(self::LIST_EAGER_LOADS)
            ->join('companies', 'companies.id', '=', 'customer_addresses.company_id')
            ->whereCompanyId('customer_addresses', $companyId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $customerId, $includeId) {
            $query->where(function ($query) use ($withTrashed, $search, $customerId) {
                $query->withoutTrashed();
                if ($withTrashed) $query->withTrashed();

                if ($search) {
                    $query->where('customer_addresses.code', 'like', '%'.$search.'%')
                        ->orWhere('customer_addresses.remarks', 'like', '%'.$search.'%');
                }

                if ($customerId) {
                    $query->where('customer_addresses.customer_id', $customerId);
                }
            });

            if ($includeId) {
                $query->orWhere('customer_addresses.id', $includeId);
            }
        });

        if ($includeId) $query->orderByRaw('FIELD(customer_addresses.id, '.$includeId.') desc');
        $query->orderBy('companies.name', 'asc')
            ->orderBy('customer_addresses.address', 'asc')
            ->orderBy('customer_addresses.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    empty($search) ? '[empty]' : $search,
                    $customerId ?? '[null]',
                    $includeId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_customer_address_'.implode('_', $cacheParams);

                if ($execute->useCache) {
                    $cacheResult = $this->readFromCache($cacheKey);
                    if ($cacheResult !== Config::get('dcslab.ERROR_RETURN_VALUE')) {
                        return $cacheResult;
                    }
                }

                if ($execute->pagination) {
                    $result = $query->paginate(
                        perPage: $execute->pagination->perPage,
                        columns: ['*'],
                        pageName: 'page',
                        page: $execute->pagination->page
                    );
                } else {
                    if ($execute->get?->limit) {
                        $query->limit($execute->get->limit);
                    }
                    $result = $query->get();
                }

                $recordsCount = $result->count();

                if ($execute->useCache) {
                    $this->saveToCache($cacheKey, $result);
                }

                return $result;
            } catch (Exception $e) {
                $this->loggerDebug(__METHOD__, $e);
                throw $e;
            } finally {
                $execution_time = microtime(true) - $timer_start;
                $this->loggerPerformance(__METHOD__, $execution_time, $recordsCount);
            }
        }

        return $query;
    }

    public function read(CustomerAddress $customerAddress): CustomerAddress
    {
        return $customerAddress->load(self::LIST_EAGER_LOADS);
    }

    public function isUniqueAddress(int $companyId, int $customerId, string $address, ?int $exceptId = null): bool
    {
        $query = CustomerAddress::whereCompanyId('customer_addresses', $companyId)
            ->whereCustomerId($customerId)
            ->whereAddress($address);

        if ($exceptId) {
            $query->where('id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }

    public function create(array $data): CustomerAddress
    {
        $timer_start = microtime(true);

        try {
            $customerAddress = new CustomerAddress();
            $customerAddress->company_id = $data['company_id'];
            $customerAddress->customer_id = $data['customer_id'];
            $customerAddress->address = $data['address'];
            $customerAddress->city = $data['city'];
            $customerAddress->contact = $data['contact'];
            $customerAddress->is_main = $data['is_main'];
            $customerAddress->remarks = $data['remarks'];
            $customerAddress->save();

            $this->flushCache();

            return $customerAddress;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(CustomerAddress $customerAddress, array $data): CustomerAddress
    {
        $timer_start = microtime(true);

        try {
            $customerAddress->address = $data['address'];
            $customerAddress->city = $data['city'];
            $customerAddress->contact = $data['contact'];
            $customerAddress->is_main = $data['is_main'];
            $customerAddress->remarks = $data['remarks'];
            $customerAddress->save();

            $this->flushCache();

            return $customerAddress->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(CustomerAddress $customerAddress): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $customerAddress->delete();

            $this->flushCache();

            return $retval;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }
}
