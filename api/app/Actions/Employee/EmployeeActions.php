<?php

namespace App\Actions\Employee;

use App\DTOs\ExecuteDTO;
use App\Models\Company;
use App\Models\Employee;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class EmployeeActions
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

        ?int $userId,
        ?int $includeId,

        ?ExecuteDTO $execute
    ) {
        $query = Employee::select('employees.*')
            ->with(self::LIST_EAGER_LOADS)
            ->join('companies', 'companies.id', '=', 'employees.company_id')
            ->whereCompanyId('employees', $companyId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $userId, $includeId) {
            $query->where(function ($query) use ($withTrashed, $search, $userId) {
                $query->withoutTrashed();
                if ($withTrashed) $query->withTrashed();

                if ($search) {
                    $query->search($search);
                }

                if ($userId) {
                    $query->where('employees.user_id', $userId);
                }
            });

            if ($includeId) {
                $query->orWhere('employees.id', $includeId);
            }
        });

        if ($includeId) $query->orderByRaw('FIELD(employees.id, '.$includeId.') desc');
        $query->orderBy('companies.name', 'asc')
            ->orderBy('employees.name', 'asc')
            ->orderBy('employees.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    empty($search) ? '[empty]' : $search,
                    $userId ?? '[null]',
                    $includeId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_employee_'.implode('_', $cacheParams);

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

    public function read(Employee $employee): Employee
    {
        return $employee->load(self::LIST_EAGER_LOADS);
    }

    public function create(array $data): Employee
    {
        $timer_start = microtime(true);

        try {
            $employee = new Employee();
            $employee->company_id = $data['company_id'];
            $employee->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $employee->name = $data['name'];
            $employee->remarks = $data['remarks'];
            $employee->save();

            $this->flushCache();

            return $employee;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(Employee $employee, array $data): Employee
    {
        $timer_start = microtime(true);

        try {
            $employee->company_id = $data['company_id'];
            $employee->code = $this->generateUniqueCode($employee->company_id, $data['code'], $employee->id);
            $employee->name = $data['name'];
            $employee->remarks = $data['remarks'];
            $employee->save();

            $this->flushCache();

            return $employee->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(Employee $employee): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $employee->delete();

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
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            $company = Company::find($companyId);

            $tryCount = 0;
            do {
                $count = $company->employees()->withTrashed()->count() + 1 + $tryCount;
                $code = 'EMP'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        } else {
            return $code;
        }
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = Employee::whereCompanyId('employees', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }

    public function isUniqueName(int $companyId, string $name, ?int $exceptId): bool
    {
        $result = Employee::whereCompanyId('employees', $companyId)->where('name', '=', $name);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
