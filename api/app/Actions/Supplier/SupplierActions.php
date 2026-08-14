<?php

namespace App\Actions\Supplier;

use App\Actions\ChartOfAccount\ChartOfAccountActions;
use App\DTOs\ChartOfAccountCreateDTO;
use App\DTOs\ChartOfAccountUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\DTOs\SupplierCreateDTO;
use App\DTOs\SupplierUpdateDTO;
use App\Enums\RecordStatusEnum;
use App\Models\Company;
use App\Models\Supplier;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;

class SupplierActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
    ];

    public function __construct(
        private readonly ChartOfAccountActions $chartOfAccountActions,
    ) {
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,

        ?string $search,
        ?int $includeId,

        ?ExecuteDTO $execute
    ) {
        $query = Supplier::with(self::LIST_EAGER_LOADS)->select('suppliers.*')
            ->whereCompanyId('suppliers', $companyId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $includeId) {
            $query->where(function ($query) use ($withTrashed, $search) {
                $query->withoutTrashed();
                if ($withTrashed) $query->withTrashed();

                if ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('suppliers.code', 'like', '%'.$search.'%')
                            ->orWhere('suppliers.name', 'like', '%'.$search.'%')
                            ->orWhere('suppliers.address', 'like', '%'.$search.'%')
                            ->orWhere('suppliers.city', 'like', '%'.$search.'%')
                            ->orWhere('suppliers.payment_term_type', 'like', '%'.$search.'%')
                            ->orWhere('suppliers.payment_term', 'like', '%'.$search.'%')
                            ->orWhere('suppliers.tax_id', 'like', '%'.$search.'%')
                            ->orWhere('suppliers.remarks', 'like', '%'.$search.'%');
                    });
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
        return $supplier->load(self::LIST_EAGER_LOADS);
    }

    public function create(SupplierCreateDTO $data): Supplier
    {
        $timer_start = microtime(true);

        try {
            $supplier = new Supplier();
            $supplier->company_id = $data->companyId;
            $supplier->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $supplier->name = $data->name;
            $supplier->address = $data->address;
            $supplier->city = $data->city;
            $supplier->payment_term_type = $data->paymentTermType;
            $supplier->payment_term = $data->paymentTerm;
            $supplier->taxable_enterprise = $data->taxableEnterprise;
            $supplier->tax_id = $data->taxId;
            $supplier->status = $data->status;
            $supplier->remarks = $data->remarks;
            $supplier->save();

            $parentChartOfAccount = $supplier->company->liabilityAccountPayableChartOfAccount;
            if (! $parentChartOfAccount) {
                throw new InvalidArgumentException('Supplier chart of account parent must exist in company.');
            }

            $chartOfAccountDTO = new ChartOfAccountCreateDTO(
                companyId: $supplier->company_id,
                scope: 'user',
                systemKey: null,
                parentId: $parentChartOfAccount->id,
                sourceType: Supplier::class,
                sourceId: $supplier->id,
                code: $parentChartOfAccount->code.'.'.$supplier->code,
                name: $supplier->name,
                normalBalance: 'credit',
                isGroup: false,
                isActive: $supplier->status === RecordStatusEnum::ACTIVE,
                remarks: $supplier->remarks,
            );
            $this->chartOfAccountActions->create($chartOfAccountDTO);

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

    public function update(Supplier $supplier, SupplierUpdateDTO $data): Supplier
    {
        $timer_start = microtime(true);

        try {
            $supplier->code = $this->generateUniqueCode($supplier->company_id, $data->code, $supplier->id);
            $supplier->name = $data->name;
            $supplier->address = $data->address;
            $supplier->city = $data->city;
            $supplier->payment_term_type = $data->paymentTermType;
            $supplier->payment_term = $data->paymentTerm;
            $supplier->taxable_enterprise = $data->taxableEnterprise;
            $supplier->tax_id = $data->taxId;
            $supplier->status = $data->status;
            $supplier->remarks = $data->remarks;
            $supplier->save();

            $parentChartOfAccount = $supplier->company->liabilityAccountPayableChartOfAccount;
            if (! $parentChartOfAccount) {
                throw new InvalidArgumentException('Supplier chart of account parent must exist in company.');
            }

            $chartOfAccount = $supplier->chartOfAccount;
            if ($chartOfAccount) {
                $chartOfAccountDTO = new ChartOfAccountUpdateDTO(
                    parentId: $parentChartOfAccount->id,
                    code: $parentChartOfAccount->code.'.'.$supplier->code,
                    name: $supplier->name,
                    normalBalance: 'credit',
                    isGroup: false,
                    isActive: $supplier->status === RecordStatusEnum::ACTIVE,
                    remarks: $supplier->remarks,
                );
                $this->chartOfAccountActions->update($chartOfAccount, $chartOfAccountDTO);
            } else {
                $chartOfAccountDTO = new ChartOfAccountCreateDTO(
                    companyId: $supplier->company_id,
                    scope: 'user',
                    systemKey: null,
                    parentId: $parentChartOfAccount->id,
                    sourceType: Supplier::class,
                    sourceId: $supplier->id,
                    code: $parentChartOfAccount->code.'.'.$supplier->code,
                    name: $supplier->name,
                    normalBalance: 'credit',
                    isGroup: false,
                    isActive: $supplier->status === RecordStatusEnum::ACTIVE,
                    remarks: $supplier->remarks,
                );
                $this->chartOfAccountActions->create($chartOfAccountDTO);
            }

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
            $chartOfAccount = $supplier->chartOfAccount;
            if ($chartOfAccount) {
                $this->chartOfAccountActions->delete($chartOfAccount);
            }

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
