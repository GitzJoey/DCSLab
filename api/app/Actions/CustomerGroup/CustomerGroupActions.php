<?php

namespace App\Actions\CustomerGroup;

use App\Actions\Randomizer\RandomizerActions;
use App\Models\CustomerGroup;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class CustomerGroupActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function create(
        array $customerGroupArr
    ): CustomerGroup {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $customerGroup = new CustomerGroup();
            $customerGroup->company_id = $customerGroupArr['company_id'];
            $customerGroup->code = $customerGroupArr['code'];
            $customerGroup->name = $customerGroupArr['name'];
            $customerGroup->max_open_invoice = $customerGroupArr['max_open_invoice'];
            $customerGroup->max_outstanding_invoice = $customerGroupArr['max_outstanding_invoice'];
            $customerGroup->max_invoice_age = $customerGroupArr['max_invoice_age'];
            $customerGroup->payment_term_type = $customerGroupArr['payment_term_type'];
            $customerGroup->payment_term = $customerGroupArr['payment_term'];
            $customerGroup->selling_point = $customerGroupArr['selling_point'];
            $customerGroup->selling_point_multiple = $customerGroupArr['selling_point_multiple'];
            $customerGroup->sell_at_cost = $customerGroupArr['sell_at_cost'];
            $customerGroup->price_markup_percent = $customerGroupArr['price_markup_percent'];
            $customerGroup->price_markup_nominal = $customerGroupArr['price_markup_nominal'];
            $customerGroup->price_markdown_percent = $customerGroupArr['price_markdown_percent'];
            $customerGroup->price_markdown_nominal = $customerGroupArr['price_markdown_nominal'];
            $customerGroup->round_on = $customerGroupArr['round_on'];
            $customerGroup->round_digit = $customerGroupArr['round_digit'];
            $customerGroup->remarks = $customerGroupArr['remarks'];

            $customerGroup->save();

            DB::commit();

            $this->flushCache();

            return $customerGroup;
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function readBy(
        string $key,
        string $value
    ) {
        $timer_start = microtime(true);

        try {
            switch (strtoupper($key)) {
                case 'ID':
                    return CustomerGroup::find($value);
                default:
                    return null;
                    break;
            }
        } catch (Exception $e) {
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

            $customerGroup = count($with) != 0 ? CustomerGroup::with($with) : CustomerGroup::with('company');
            $customerGroup = $customerGroup->whereCompanyId($companyId);

            if ($withTrashed) {
                $customerGroup = $customerGroup->withTrashed();
            }

            if (empty($search)) {
                $customerGroup = $customerGroup->latest();
            } else {
                $customerGroup = $customerGroup->where('name', 'like', '%'.$search.'%')->latest();
            }

            if ($paginate) {
                $perPage = is_numeric($perPage) ? $perPage : Config::get('dcslab.PAGINATION_LIMIT');
                $result = $customerGroup->paginate(perPage: abs($perPage), page: abs($page));
            } else {
                $result = $customerGroup->get();
            }

            if ($useCache) {
                $this->saveToCache($cacheKey, $result);
            }

            return $result;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function read(CustomerGroup $customerGroup): CustomerGroup
    {
        return $customerGroup->first();
    }

    public function update(
        CustomerGroup $customerGroup,
        array $customerGroupArr
    ): CustomerGroup {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $customerGroup->update([
                'code' => $customerGroupArr['code'],
                'name' => $customerGroupArr['name'],
                'max_open_invoice' => $customerGroupArr['max_open_invoice'],
                'max_outstanding_invoice' => $customerGroupArr['max_outstanding_invoice'],
                'max_invoice_age' => $customerGroupArr['max_invoice_age'],
                'payment_term_type' => $customerGroupArr['payment_term_type'],
                'payment_term' => $customerGroupArr['payment_term'],
                'selling_point' => $customerGroupArr['selling_point'],
                'selling_point_multiple' => $customerGroupArr['selling_point_multiple'],
                'sell_at_cost' => $customerGroupArr['sell_at_cost'],
                'price_markup_percent' => $customerGroupArr['price_markup_percent'],
                'price_markup_nominal' => $customerGroupArr['price_markup_nominal'],
                'price_markdown_percent' => $customerGroupArr['price_markdown_percent'],
                'price_markdown_nominal' => $customerGroupArr['price_markdown_nominal'],
                'round_on' => $customerGroupArr['round_on'],
                'round_digit' => $customerGroupArr['round_digit'],
                'remarks' => $customerGroupArr['remarks'],
            ]);

            DB::commit();

            $this->flushCache();

            return $customerGroup->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(CustomerGroup $customerGroup): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $customerGroup->delete();

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

    public function isUniqueCode(string $code, int $companyId, int $exceptId = null): bool
    {
        $result = CustomerGroup::whereCompanyId($companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
