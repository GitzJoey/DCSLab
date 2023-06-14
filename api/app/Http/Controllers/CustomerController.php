<?php

namespace App\Http\Controllers;

use App\Actions\Customer\CustomerActions;
use App\Http\Requests\CustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Company;
use App\Models\Customer;
use Exception;

class CustomerController extends BaseController
{
    private $customerActions;

    public function __construct(CustomerActions $customerActions)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->customerActions = $customerActions;
    }

    public function store(CustomerRequest $customerRequest)
    {
        $request = $customerRequest->validated();

        $company_id = $request['company_id'];
        $customer_group_id = $request['customer_group_id'];

        $code = $request['code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            do {
                $code = $this->customerActions->generateUniqueCode();
            } while (! $this->customerActions->isUniqueCode($code, $company_id));
        } else {
            if (! $this->customerActions->isUniqueCode($code, $company_id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')],
                ], 422);
            }
        }

        if (! Company::find($company_id)->customerGroups()->where('id', '=', $customer_group_id)->exists()) {
            return response()->error([
                'customer_group_id' => [trans('rules.valid_customer_group')],
            ], 422);
        }

        $customerArr = [
            'company_id' => $company_id,
            'code' => $code,
            'name' => $request['name'],
            'is_member' => $request['is_member'],
            'customer_group_id' => $request['customer_group_id'],
            'zone' => $request['zone'],
            'max_open_invoice' => $request['max_open_invoice'],
            'max_outstanding_invoice' => $request['max_outstanding_invoice'],
            'max_invoice_age' => $request['max_invoice_age'],
            'payment_term_type' => $request['payment_term_type'],
            'payment_term' => $request['payment_term'],
            'taxable_enterprise' => $request['taxable_enterprise'],
            'tax_id' => $request['tax_id'],
            'remarks' => $request['remarks'],
            'status' => $request['status'],
        ];

        $customerAddressArr = [];
        $count_address = count($request['arr_customer_address_address']);
        for ($i = 0; $i < $count_address; $i++) {
            $arr_customer_address_address = $request['arr_customer_address_address'][$i];
            $arr_customer_address_city = $request['arr_customer_address_city'][$i];
            $arr_customer_address_contact = $request['arr_customer_address_contact'][$i];

            $arr_customer_address_is_main = filter_var($request['arr_customer_address_is_main'][$i], FILTER_VALIDATE_BOOLEAN);
            $arr_customer_address_is_main = $arr_customer_address_is_main == true ? 1 : 0;

            $arr_customer_address_remarks = $request['arr_customer_address_remarks'][$i];

            array_push($customerAddressArr, [
                'company_id' => $company_id,
                'address' => $arr_customer_address_address,
                'city' => $arr_customer_address_city,
                'contact' => $arr_customer_address_contact,
                'is_main' => $arr_customer_address_is_main,
                'remarks' => $arr_customer_address_remarks,
            ]);
        }

        $picArr = [];
        if ($request['pic_create_user'] == true) {
            $first_name = '';
            $last_name = '';
            if ($request['pic_contact_person_name'] == trim($request['pic_contact_person_name']) && strpos($request['pic_contact_person_name'], ' ') !== false) {
                $pieces = explode(' ', $request['pic_contact_person_name']);
                $first_name = $pieces[0];
                $last_name = $pieces[1];
            } else {
                $first_name = $request['pic_contact_person_name'];
            }

            $picArr = [
                'name' => $request['pic_contact_person_name'],
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $request['pic_email'],
                'password' => $request['pic_password'],
            ];
        }

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->customerActions->create(
                $customerArr,
                $customerAddressArr,
                $picArr
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(CustomerRequest $customerRequest)
    {
        $request = $customerRequest->validated();

        $search = $request['search'];
        $paginate = $request['paginate'];
        $page = array_key_exists('page', $request) ? abs($request['page']) : 1;
        $perPage = array_key_exists('per_page', $request) ? abs($request['per_page']) : 10;
        $useCache = array_key_exists('refresh', $request) ? boolval($request['refresh']) : true;

        $companyId = $request['company_id'];

        $result = null;
        $errorMsg = '';

        try {
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        $result = $this->customerActions->readAny(
            companyId: $companyId,
            search: $search,
            paginate: $paginate,
            page: $page,
            perPage: $perPage,
            useCache: $useCache
        );

        if (is_null($result)) {
            return response()->error();
        } else {
            $response = CustomerResource::collection($result);

            return $response;
        }
    }

    public function read(Customer $customer, CustomerRequest $customerRequest)
    {
        $request = $customerRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->customerActions->read($customer);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new CustomerResource($result);

            return $response;
        }
    }

    public function update(Customer $customer, CustomerRequest $customerRequest)
    {
        $request = $customerRequest->validated();

        $company_id = $request['company_id'];
        $customer_group_id = $request['customer_group_id'];

        $code = $request['code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            do {
                $code = $this->customerActions->generateUniqueCode($company_id);
            } while (! $this->customerActions->isUniqueCode($code, $company_id, $customer->id));
        } else {
            if (! $this->customerActions->isUniqueCode($code, $company_id, $customer->id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')],
                ], 422);
            }
        }

        if (! Company::find($company_id)->customerGroups()->where('id', '=', $customer_group_id)->exists()) {
            return response()->error([
                'customer_group_id' => [trans('rules.valid_customer_group')],
            ], 422);
        }

        $customerArr = [
            'company_id' => $company_id,
            'code' => $code,
            'name' => $request['name'],
            'is_member' => $request['is_member'],
            'customer_group_id' => $customer_group_id,
            'zone' => $request['zone'],
            'max_open_invoice' => $request['max_open_invoice'],
            'max_outstanding_invoice' => $request['max_outstanding_invoice'],
            'max_invoice_age' => $request['max_invoice_age'],
            'payment_term_type' => $request['payment_term_type'],
            'payment_term' => $request['payment_term'],
            'taxable_enterprise' => $request['taxable_enterprise'],
            'tax_id' => $request['tax_id'],
            'remarks' => $request['remarks'],
            'status' => $request['status'],
        ];

        $customerAddressArr = [];
        $count_address = count($request['arr_customer_address_address']);
        for ($i = 0; $i < $count_address; $i++) {
            $customer_address_id = $request['arr_customer_address_id'][$i] != 0 ? $request['arr_customer_address_id'][$i] : 0;

            $customer_address_ulid = $request['arr_customer_address_ulid'][$i];

            $arr_customer_address_address = $request['arr_customer_address_address'][$i];

            $arr_customer_address_city = $request['arr_customer_address_city'][$i];

            $arr_customer_address_contact = $request['arr_customer_address_contact'][$i];

            $arr_customer_address_is_main = filter_var($request['arr_customer_address_is_main'][$i], FILTER_VALIDATE_BOOLEAN);
            $arr_customer_address_is_main = $arr_customer_address_is_main == true ? 1 : 0;

            $arr_customer_address_remarks = $request['arr_customer_address_remarks'][$i];

            array_push($customerAddressArr, [
                'id' => $customer_address_id,
                'ulid' => $customer_address_ulid,
                'company_id' => $company_id,
                'customer_id' => $customer->id,
                'address' => $arr_customer_address_address,
                'city' => $arr_customer_address_city,
                'contact' => $arr_customer_address_contact,
                'is_main' => $arr_customer_address_is_main,
                'remarks' => $arr_customer_address_remarks,
            ]);
        }

        $picArr = [];
        if ($request['pic_create_user'] == true && $customer->user_id == null) {
            $first_name = '';
            $last_name = '';
            if ($request['pic_contact_person_name'] == trim($request['pic_contact_person_name']) && strpos($request['pic_contact_person_name'], ' ') !== false) {
                $pieces = explode(' ', $request['pic_contact_person_name']);
                $first_name = $pieces[0];
                $last_name = $pieces[1];
            } else {
                $first_name = $request['pic_contact_person_name'];
            }

            $picArr = [
                'name' => $request['pic_contact_person_name'],
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $request['pic_email'],
                'password' => $request['pic_password'],
            ];
        }

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->customerActions->update(
                $customer,
                $customerArr,
                $customerAddressArr,
                $picArr
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(Customer $customer)
    {
        $result = false;
        $errorMsg = '';

        try {
            $result = $this->customerActions->delete($customer);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
