<?php

namespace App\Actions\Supplier;

use App\DTOs\ExecuteDTO;
use App\Models\Company;
use App\Models\Supplier;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class SupplierActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function create(array $data): Supplier
    {
        $timer_start = microtime(true);

        try {
            $supplier = new Supplier();
            $supplier->company_id = $data['company_id'];
            $supplier->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $supplier->name = $data['name'];
            $supplier->address = $data['address'];
            $supplier->city = $data['city'];
            $supplier->payment_term_type = $data['payment_term_type'];
            $supplier->payment_term = $data['payment_term'];
            $supplier->taxable_enterprise = $data['taxable_enterprise'];
            $supplier->tax_id = $data['tax_id'];
            $supplier->status = $data['status'];
            $supplier->remarks = $data['remarks'];
            $supplier->save();

            $this->flushCache();

            return $supplier;
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
        ?int $includeId,

        ?ExecuteDTO $execute
    ) {
        $query = Supplier::with('company')->select('suppliers.*')
            ->whereCompanyId('suppliers', $companyId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $includeId) {
            $query->where(function ($query) use ($withTrashed, $search) {
                $query->withoutTrashed();
                if ($withTrashed) $query->withTrashed();

                if ($search) {
                    $query->search($search);
                }
            });

            if ($includeId) {
                $query->orWhere('suppliers.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(suppliers.id, '.$includeId.') desc');
        }
        $query->orderBy('suppliers.name', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    empty($search) ? '[empty]' : $search,
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

    public function read(Supplier $supplier): Supplier
    {
        return $supplier->load('company');
    }

    public function update(Supplier $supplier, array $data): Supplier
    {
        $timer_start = microtime(true);

        try {
            $supplier->code = $this->generateUniqueCode($supplier->company_id, $data['code'], $supplier->id);
            $supplier->name = $data['name'];
            $supplier->address = $data['address'];
            $supplier->city = $data['city'];
            $supplier->payment_term_type = $data['payment_term_type'];
            $supplier->payment_term = $data['payment_term'];
            $supplier->taxable_enterprise = $data['taxable_enterprise'];
            $supplier->tax_id = $data['tax_id'];
            $supplier->status = $data['status'];
            $supplier->remarks = $data['remarks'];
            $supplier->save();

            $this->flushCache();

            return $supplier->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(Supplier $supplier): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $supplier->delete();

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
            $count = $company->suppliers()->withTrashed()->count() + 1 + $tryCount;
            $code = 'SUP'.str_pad($count, 3, '0', STR_PAD_LEFT);
            $tryCount++;
        } while (! $this->isUniqueCode($companyId, $code, $exceptId));

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $company = Company::find($companyId);

        if ($company->suppliers()->count() == 0) {
            return true;
        }

        $query = $company->suppliers()->where('code', '=', $code);
        if ($exceptId) {
            $query->where('suppliers.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }

    public function isUniqueName(int $companyId, string $name, ?int $exceptId): bool
    {
        $company = Company::find($companyId);

        if ($company->suppliers()->count() == 0) {
            return true;
        }

        $query = $company->suppliers()->where('name', '=', $name);
        if ($exceptId) {
            $query->where('suppliers.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }
}
