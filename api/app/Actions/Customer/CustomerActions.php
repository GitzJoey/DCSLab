<?php

namespace App\Actions\Customer;

use App\DTOs\ExecuteDTO;
use App\Models\Company;
use App\Models\Customer;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class CustomerActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function create(array $data): Customer
    {
        $timer_start = microtime(true);

        try {
            $customer = new Customer();
            $customer->company_id = $data['company_id'];
            $customer->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $customer->is_member = $data['is_member'];
            $customer->name = $data['name'];
            $customer->group_id = $data['group_id'];
            $customer->zone = $data['zone'];
            $customer->max_open_invoice = $data['max_open_invoice'];
            $customer->max_outstanding_invoice = $data['max_outstanding_invoice'];
            $customer->max_invoice_age = $data['max_invoice_age'];
            $customer->payment_term_type = $data['payment_term_type'];
            $customer->payment_term = $data['payment_term'];
            $customer->taxable_enterprise = $data['taxable_enterprise'];
            $customer->tax_id = $data['tax_id'];
            $customer->status = $data['status'];
            $customer->remarks = $data['remarks'];
            $customer->save();

            // save user (not yet implemented)

            $this->flushCache();

            return $customer;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,

        ?string $search,
        ?bool $isMember,
        ?int $groupId,
        ?string $zone,
        ?int $maxOpenInvoice,
        ?float $maxOutstandingInvoice,
        ?int $maxInvoiceAge,
        ?int $paymentTermType,
        ?int $paymentTerm,
        ?bool $taxableEnterprise,
        ?string $taxId,
        ?int $status,
        ?int $includeId,

        ?ExecuteDTO $execute
    ): Paginator|Collection|Builder {
        $query = Customer::with(['company', 'user', 'group'])
            ->select('customers.*')
            ->where('customers.company_id', $companyId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $isMember,
            $groupId,
            $zone,
            $maxOpenInvoice,
            $maxOutstandingInvoice,
            $maxInvoiceAge,
            $paymentTermType,
            $paymentTerm,
            $taxableEnterprise,
            $taxId,
            $status,
            $includeId
        ) {
            $query->where(function ($query) use (
                $withTrashed,
                $search,
                $isMember,
                $groupId,
                $zone,
                $maxOpenInvoice,
                $maxOutstandingInvoice,
                $maxInvoiceAge,
                $paymentTermType,
                $paymentTerm,
                $taxableEnterprise,
                $taxId,
                $status
            ) {
                $query->withoutTrashed();
                if ($withTrashed) {
                    $query->withTrashed();
                }

                if ($search) {
                    $query->search($search);
                }

                if (! is_null($isMember)) {
                    $query->where('customers.is_member', $isMember);
                }

                if (! is_null($groupId)) {
                    $query->where('customers.group_id', $groupId);
                }

                if (! is_null($zone)) {
                    $query->where('customers.zone', $zone);
                }

                if (! is_null($maxOpenInvoice)) {
                    $query->where('customers.max_open_invoice', $maxOpenInvoice);
                }

                if (! is_null($maxOutstandingInvoice)) {
                    $query->where('customers.max_outstanding_invoice', $maxOutstandingInvoice);
                }

                if (! is_null($maxInvoiceAge)) {
                    $query->where('customers.max_invoice_age', $maxInvoiceAge);
                }

                if (! is_null($paymentTermType)) {
                    $query->where('customers.payment_term_type', $paymentTermType);
                }

                if (! is_null($paymentTerm)) {
                    $query->where('customers.payment_term', $paymentTerm);
                }

                if (! is_null($taxableEnterprise)) {
                    $query->where('customers.taxable_enterprise', $taxableEnterprise);
                }

                if (! is_null($taxId)) {
                    $query->where('customers.tax_id', $taxId);
                }

                if (! is_null($status)) {
                    $query->where('customers.status', $status);
                }
            });

            if ($includeId) {
                $query->orWhere('customers.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(customers.id, '.$includeId.') desc');
        }
        $query->orderBy('customers.name', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    empty($search) ? '[empty]' : $search,
                    $companyId,
                    is_null($isMember) ? '[null]' : ($isMember ? 'true' : 'false'),
                    $groupId ?? '[null]',
                    is_null($zone) || $zone === '' ? '[empty]' : $zone,
                    $maxOpenInvoice ?? '[null]',
                    $maxOutstandingInvoice ?? '[null]',
                    $maxInvoiceAge ?? '[null]',
                    $paymentTermType ?? '[null]',
                    $paymentTerm ?? '[null]',
                    is_null($taxableEnterprise) ? '[null]' : ($taxableEnterprise ? 'true' : 'false'),
                    is_null($taxId) || $taxId === '' ? '[empty]' : $taxId,
                    $status ?? '[null]',
                    $includeId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'readAny_'.implode('-', $cacheParams);

                if ($execute->useCache) {
                    $cacheData = $this->readFromCache($cacheKey);
                    if ($cacheData !== Config::get('dcslab.ERROR_RETURN_VALUE')) {
                        return $cacheData;
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

    public function read(Customer $customer): Customer
    {
        return $customer->load('company', 'user', 'group');
    }

    public function update(Customer $customer, array $data): Customer
    {
        $timer_start = microtime(true);

        try {
            $customer->code = $this->generateUniqueCode($customer->company_id, $data['code'], $customer->id);
            $customer->is_member = $data['is_member'];
            $customer->name = $data['name'];
            $customer->group_id = $data['group_id'];
            $customer->zone = $data['zone'];
            $customer->max_open_invoice = $data['max_open_invoice'];
            $customer->max_outstanding_invoice = $data['max_outstanding_invoice'];
            $customer->max_invoice_age = $data['max_invoice_age'];
            $customer->payment_term_type = $data['payment_term_type'];
            $customer->payment_term = $data['payment_term'];
            $customer->taxable_enterprise = $data['taxable_enterprise'];
            $customer->tax_id = $data['tax_id'];
            $customer->status = $data['status'];
            $customer->remarks = $data['remarks'];
            $customer->save();

            $this->flushCache();

            return $customer->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(Customer $customer): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $customer->delete();

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

    public function generateUniqueCode(int $companyId, string $code, ?int $exceptId): string
    {
        if ($code != config('dcslab.KEYWORDS.AUTO')) {
            return $code;
        }

        $company = Company::find($companyId);

        $tryCount = 0;
        do {
            $count = $company->customers()->withTrashed()->count() + 1 + $tryCount;
            $code = 'C'.str_pad($count, 3, '0', STR_PAD_LEFT);
            $tryCount++;
        } while (! $this->isUniqueCode($companyId, $code, $exceptId));

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $company = Company::find($companyId);

        if ($company->customers()->count() == 0) {
            return true;
        }

        $query = $company->customers()->where('code', '=', $code);
        if ($exceptId) {
            $query->where('customers.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }

    public function isUniqueName(int $companyId, string $name, ?int $exceptId): bool
    {
        $company = Company::find($companyId);

        if ($company->customers()->count() == 0) {
            return true;
        }

        $query = $company->customers()->where('name', '=', $name);
        if ($exceptId) {
            $query->where('customers.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }
}
