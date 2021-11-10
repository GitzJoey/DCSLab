<?php

namespace App\Services\Impls;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\CompanyUserService;
use App\Models\User;
use App\Models\CompanyUser;

class CompanyUserServiceImpl implements CompanyUserService
{
    public function create(
        $user_id,
        $company_id
    )
    {
        DB::beginTransaction();

        try {
            $CompanyUser = new CompanyUser();
            $CompanyUser->user_id = $user_id;
            $CompanyUser->company_id = $company_id;
            $CompanyUser->save();

            DB::commit();

            return $CompanyUser->hId;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function read($user_id)
    {
        return CompanyUser::whereIn('user_id', $user_id)->paginate();
    }

    public function getAllCompanyUser($user_id)
    {
        // $usr = User::find($userId)->first();
        // $compIds = $usr->companies()->pluck('company_id');
        // return Company::where('status', '=', 1)->whereIn('id',  $compIds)->get();

        return CompanyUser::whereIn('user_id', $user_id)->get();
    }

    public function update(
        $id,
        $user_id,
        $company_id
    )
    {
        DB::beginTransaction();

        try {
            $CompanyUser = CompanyUser::where('id', '=', $id);

            $retval = $CompanyUser->update([
                'user_id' => $user_id,
                'company_id' => $company_id
            ]);

            DB::commit();

            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();

        try {
            $CompanyUser = CompanyUser::find($id);
            $retval = $CompanyUser->delete();

            DB::commit();

            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }
}
