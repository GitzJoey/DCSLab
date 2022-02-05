<?php

namespace App\Services\Impls;

use App\Actions\RandomGenerator;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\BranchService;
use App\Models\Branch;

class BranchServiceImpl implements BranchService
{
    public function create(
        int $company_id,
        string $code,
        string $name,
        ?string $address = null,
        ?string $city = null,
        ?string $contact = null,
        ?string $remarks = null,
        ?string $status = null,
    ): ?Branch
    {
        DB::beginTransaction();

        try {
            $branch = new Branch();
            $branch->company_id = $company_id;
            $branch->code = $code;
            $branch->name = $name;
            $branch->address = $address;
            $branch->city = $city;
            $branch->contact = $contact;
            $branch->remarks = $remarks;
            $branch->status = $status;

            $branch->save();

            DB::commit();

            return $branch->hId;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function readBy(string $key, string $value)
    {
        switch (strtoupper($key)) {
            case 'ID':
                return Branch::find($value);
            default:
                return null;
                break;
        }
    }

    public function read(int $companyId, string $search = '', bool $paginate = true, ?int $perPage = 10)
    {
        $branch = Branch::with('company')
                    ->whereCompanyId($companyId);

        if (empty($search)) {
            $branch = $branch->latest();
        } else {
            $branch = $branch->where('name', 'like', '%'.$search.'%')->latest();
        }

        if ($paginate) {
            $perPage = is_numeric($perPage) ? $perPage : Config::get('const.DEFAULT.PAGINATION_LIMIT');
            return $branch->paginate($perPage);
        } else {
            return $branch->get();
        }
    }

    public function update(
        int $id,
        int $company_id,
        string $code,
        string $name,
        ?string $address = null,
        ?string $city = null,
        ?string $contact = null,
        ?string $remarks = null,
        ?string $status = null,
    ): ?Branch
    {
        DB::beginTransaction();

        try {
            $branch = Branch::find($id);
    
            $branch->update([
                'company_id' => $company_id,
                'code' => $code,
                'name' => $name,
                'address' => $address,
                'city' => $city,
                'contact' => $contact,
                'remarks' => $remarks,
                'status' => $status,
            ]);

            DB::commit();

            return $branch->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function delete(int $id): bool
    {
        $branch = Branch::find($id);

        return $branch->delete();
    }

    public function generateUniqueCode(int $companyId): string
    {
        $rand = new RandomGenerator();
        
        do {
            $code = $rand->generateAlphaNumeric(3).$rand->generateFixedLengthNumber(3);
        } while (!$this->isUniqueCode($code, $companyId));

        return $code;
    }

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool
    {
        $result = Branch::whereCompanyId($companyId)->where('code', '=' , $code);

        if($exceptId)
            $result = $result->where('id', '<>', $exceptId);

        return $result->count() == 0 ? true:false;
    }
}