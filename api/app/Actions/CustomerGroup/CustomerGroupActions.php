<?php

namespace App\Actions\CustomerGroup;

use App\Actions\ChartOfAccount\ChartOfAccountActions;
use App\DTOs\ChartOfAccountCreateDTO;
use App\DTOs\ChartOfAccountUpdateDTO;
use App\DTOs\CustomerGroupCreateDTO;
use App\DTOs\CustomerGroupUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\Models\Company;
use App\Models\CustomerGroup;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;

class CustomerGroupActions
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
        $query = CustomerGroup::with(self::LIST_EAGER_LOADS)->select('customer_groups.*')
            ->where('customer_groups.company_id', $companyId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $includeId,
        ) {
            $query->where(function ($query) use (
                $withTrashed,
                $search,
            ) {
                $query->withoutTrashed();
                if ($withTrashed) {
                    $query->withTrashed();
                }

                if ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('customer_groups.code', 'like', '%'.$search.'%')
                            ->orWhere('customer_groups.name', 'like', '%'.$search.'%')
                            ->orWhere('customer_groups.remarks', 'like', '%'.$search.'%');
                    });
                }
            });

            if ($includeId) {
                $query->orWhere('customer_groups.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(customer_groups.id, '.$includeId.') desc');
        }
        $query->orderBy('customer_groups.name', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    empty($search) ? '[empty]' : $search,
                    $companyId,
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

    public function read(CustomerGroup $customerGroup): CustomerGroup
    {
        return $customerGroup->load(self::LIST_EAGER_LOADS);
    }

    public function create(CustomerGroupCreateDTO $data): CustomerGroup
    {
        $timer_start = microtime(true);

        try {
            $customerGroup = new CustomerGroup();
            $customerGroup->company_id = $data->companyId;
            $customerGroup->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $customerGroup->name = $data->name;
            $customerGroup->max_open_invoice = $data->maxOpenInvoice;
            $customerGroup->max_outstanding_invoice = $data->maxOutstandingInvoice;
            $customerGroup->max_invoice_age = $data->maxInvoiceAge;
            $customerGroup->payment_term_type = $data->paymentTermType;
            $customerGroup->payment_term = $data->paymentTerm;
            $customerGroup->selling_point = $data->sellingPoint;
            $customerGroup->selling_point_multiple = $data->sellingPointMultiple;
            $customerGroup->sell_at_cost = $data->sellAtCost;
            $customerGroup->price_markup_percent = $data->priceMarkupPercent;
            $customerGroup->price_markup_nominal = $data->priceMarkupNominal;
            $customerGroup->price_markdown_percent = $data->priceMarkdownPercent;
            $customerGroup->price_markdown_nominal = $data->priceMarkdownNominal;
            $customerGroup->rounding_type = $data->roundingType;
            $customerGroup->rounding_digit = $data->roundingDigit;
            $customerGroup->remarks = $data->remarks;
            $customerGroup->save();

            $parentChartOfAccount = $customerGroup->company->assetCurrentAccountReceivableChartOfAccount;
            if (! $parentChartOfAccount) {
                throw new InvalidArgumentException('Customer group chart of account parent must exist in company.');
            }

            $chartOfAccountDTO = new ChartOfAccountCreateDTO(
                companyId: $customerGroup->company_id,
                scope: 'user',
                systemKey: null,
                parentId: $parentChartOfAccount->id,
                sourceType: CustomerGroup::class,
                sourceId: $customerGroup->id,
                code: $parentChartOfAccount->code.'.'.$customerGroup->code,
                name: $customerGroup->name,
                normalBalance: 'debit',
                isGroup: true,
                isActive: true,
                remarks: $customerGroup->remarks,
            );
            $this->chartOfAccountActions->create($chartOfAccountDTO);

            $this->flushCache();

            return $customerGroup;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(CustomerGroup $customerGroup, CustomerGroupUpdateDTO $data): CustomerGroup
    {
        $timer_start = microtime(true);

        try {
            $customerGroup->code = $this->generateUniqueCode($customerGroup->company_id, $data->code, $customerGroup->id);
            $customerGroup->name = $data->name;
            $customerGroup->max_open_invoice = $data->maxOpenInvoice;
            $customerGroup->max_outstanding_invoice = $data->maxOutstandingInvoice;
            $customerGroup->max_invoice_age = $data->maxInvoiceAge;
            $customerGroup->payment_term_type = $data->paymentTermType;
            $customerGroup->payment_term = $data->paymentTerm;
            $customerGroup->selling_point = $data->sellingPoint;
            $customerGroup->selling_point_multiple = $data->sellingPointMultiple;
            $customerGroup->sell_at_cost = $data->sellAtCost;
            $customerGroup->price_markup_percent = $data->priceMarkupPercent;
            $customerGroup->price_markup_nominal = $data->priceMarkupNominal;
            $customerGroup->price_markdown_percent = $data->priceMarkdownPercent;
            $customerGroup->price_markdown_nominal = $data->priceMarkdownNominal;
            $customerGroup->rounding_type = $data->roundingType;
            $customerGroup->rounding_digit = $data->roundingDigit;
            $customerGroup->remarks = $data->remarks;
            $customerGroup->save();

            $parentChartOfAccount = $customerGroup->company->assetCurrentAccountReceivableChartOfAccount;
            if (! $parentChartOfAccount) {
                throw new InvalidArgumentException('Customer group chart of account parent must exist in company.');
            }

            $chartOfAccount = $customerGroup->chartOfAccount;
            if ($chartOfAccount) {
                $chartOfAccountDTO = new ChartOfAccountUpdateDTO(
                    parentId: $parentChartOfAccount->id,
                    code: $parentChartOfAccount->code.'.'.$customerGroup->code,
                    name: $customerGroup->name,
                    normalBalance: 'debit',
                    isGroup: true,
                    isActive: true,
                    remarks: $customerGroup->remarks,
                );
                $chartOfAccount = $this->chartOfAccountActions->update($chartOfAccount, $chartOfAccountDTO);
            } else {
                $chartOfAccountDTO = new ChartOfAccountCreateDTO(
                    companyId: $customerGroup->company_id,
                    scope: 'user',
                    systemKey: null,
                    parentId: $parentChartOfAccount->id,
                    sourceType: CustomerGroup::class,
                    sourceId: $customerGroup->id,
                    code: $parentChartOfAccount->code.'.'.$customerGroup->code,
                    name: $customerGroup->name,
                    normalBalance: 'debit',
                    isGroup: true,
                    isActive: true,
                    remarks: $customerGroup->remarks,
                );
                $chartOfAccount = $this->chartOfAccountActions->create($chartOfAccountDTO);
            }

            foreach ($customerGroup->customers as $customer) {
                $customerChartOfAccount = $customer->chartOfAccount;
                if (! $customerChartOfAccount) {
                    continue;
                }

                $customerChartOfAccountDTO = new ChartOfAccountUpdateDTO(
                    parentId: $chartOfAccount->id,
                    code: $chartOfAccount->code.'.'.$customer->code,
                    name: $customer->name,
                    normalBalance: 'debit',
                    isGroup: false,
                    isActive: $customer->status === \App\Enums\RecordStatusEnum::ACTIVE,
                    remarks: $customer->remarks,
                );
                $this->chartOfAccountActions->update($customerChartOfAccount, $customerChartOfAccountDTO);
            }

            $this->flushCache();

            return $customerGroup->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(CustomerGroup $customerGroup): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $chartOfAccount = $customerGroup->chartOfAccount;
            if ($chartOfAccount) {
                foreach ($customerGroup->customers as $customer) {
                    $customerChartOfAccount = $customer->chartOfAccount;
                    if ($customerChartOfAccount) {
                        $this->chartOfAccountActions->delete($customerChartOfAccount);
                    }
                }

                $this->chartOfAccountActions->delete($chartOfAccount);
            }

            $retval = $customerGroup->delete();

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
            $count = $company->customerGroups()->withTrashed()->count() + 1 + $tryCount;
            $code = 'CG'.str_pad($count, 3, '0', STR_PAD_LEFT);
            $tryCount++;
        } while (! $this->isUniqueCode($companyId, $code, $exceptId));

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $company = Company::find($companyId);

        if ($company->customerGroups()->count() == 0) {
            return true;
        }

        $query = $company->customerGroups()->where('code', '=', $code);
        if ($exceptId) {
            $query->where('customer_groups.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }

    public function isUniqueName(int $companyId, string $name, ?int $exceptId): bool
    {
        $company = Company::find($companyId);

        if ($company->customerGroups()->count() == 0) {
            return true;
        }

        $query = $company->customerGroups()->where('name', '=', $name);
        if ($exceptId) {
            $query->where('customer_groups.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }
}
