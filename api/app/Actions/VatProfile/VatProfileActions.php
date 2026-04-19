<?php

namespace App\Actions\VatProfile;

use App\DTOs\ExecuteDTO;
use App\Models\Company;
use App\Models\VatProfile;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class VatProfileActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
    ];

    public function __construct()
    {
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?string $search,

        ?int $includeId,

        ?ExecuteDTO $execute
    ) {
        $query = VatProfile::with(self::LIST_EAGER_LOADS)->select('vat_profiles.*')
            ->where('vat_profiles.company_id', $companyId)
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
                        $query->where('vat_profiles.code', 'like', '%'.$search.'%')
                            ->orWhere('vat_profiles.name', 'like', '%'.$search.'%')
                            ->orWhere('vat_profiles.remarks', 'like', '%'.$search.'%');
                    });
                }
            });

            if ($includeId) {
                $query->orWhere('vat_profiles.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(vat_profiles.id, '.$includeId.') desc');
        }
        $query->orderBy('vat_profiles.name', 'asc');

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

    public function read(VatProfile $vatProfile): VatProfile
    {
        return $vatProfile->load(self::LIST_EAGER_LOADS);
    }

    public function create(array $data): VatProfile
    {
        $timer_start = microtime(true);

        try {
            $vatProfile = new VatProfile();
            $vatProfile->company_id = $data['company_id'];
            $vatProfile->code = $this->generateUniqueCode(
                $data['company_id'],
                $data['code'],
                null,
            );
            $vatProfile->name = $data['name'];
            $vatProfile->vat_rate = $data['vat_rate'];
            $vatProfile->vat_base_numerator = $data['vat_base_numerator'];
            $vatProfile->vat_base_denominator = $data['vat_base_denominator'];
            $vatProfile->remarks = $data['remarks'];
            $vatProfile->is_active = $data['is_active'];
            $vatProfile->save();

            $this->flushCache();

            return $vatProfile;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(VatProfile $vatProfile, array $data): VatProfile
    {
        $timer_start = microtime(true);

        try {
            $vatProfile->code = $this->generateUniqueCode(
                $vatProfile->company_id,
                $data['code'],
                $vatProfile->id,
            );
            $vatProfile->name = $data['name'];
            $vatProfile->vat_rate = $data['vat_rate'];
            $vatProfile->vat_base_numerator = $data['vat_base_numerator'];
            $vatProfile->vat_base_denominator = $data['vat_base_denominator'];
            $vatProfile->remarks = $data['remarks'];
            $vatProfile->is_active = $data['is_active'];
            $vatProfile->save();

            $this->flushCache();

            return $vatProfile->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(VatProfile $vatProfile): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $vatProfile->delete();

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
        if ($code != config('dcslab.KEYWORDS.AUTO')) return $code;

        $company = Company::find($companyId);

        $tryCount = 0;
        do {
            $count = $company->vatProfiles()->withTrashed()->count() + 1 + $tryCount;
            $code = 'VAT'.str_pad($count, 3, '0', STR_PAD_LEFT);
            $tryCount++;
        } while (! $this->isUniqueCode($companyId, $code, $exceptId));

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $company = Company::find($companyId);

        if ($company->vatProfiles()->count() == 0) {
            return true;
        }

        $query = $company->vatProfiles()->where('code', '=', $code);
        if ($exceptId) {
            $query->where('vat_profiles.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }

    public function isUniqueName(int $companyId, string $name, ?int $exceptId): bool
    {
        $company = Company::find($companyId);

        if ($company->vatProfiles()->count() == 0) {
            return true;
        }

        $query = $company->vatProfiles()->where('name', '=', $name);
        if ($exceptId) {
            $query->where('vat_profiles.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }
}
