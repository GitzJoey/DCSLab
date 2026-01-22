<?php

namespace App\Http\Requests;

use App\Enums\RecordStatusEnum;
use App\Helpers\HashidsHelper;
use App\Models\StockTransfer;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidWarehouse;
use App\Rules\StockTransferStoreValidCode;
use App\Rules\StockTransferUpdateValidCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StockTransferRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $stockTransfer = $this->route('stock_transfer');

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                return $user->can('viewAny', StockTransfer::class) ? true : false;
            case 'read':
                return $user->can('view', StockTransfer::class, $stockTransfer) ? true : false;
            case 'store':
                return $user->can('create', StockTransfer::class) ? true : false;
            case 'update':
                return $user->can('update', StockTransfer::class, $stockTransfer) ? true : false;
            case 'delete':
                return $user->can('delete', StockTransfer::class, $stockTransfer) ? true : false;
            default:
                return false;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                return [
                    'refresh' => ['required', 'boolean'],
                    'with_trashed' => ['required', 'boolean'],

                    'search' => ['nullable', 'string'],
                    'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
                    'status' => ['nullable', 'integer', 'in:'.implode(',', RecordStatusEnum::toArrayValue())],

                    'paginate' => ['required', 'boolean'],
                    'page' => ['nullable', 'required_if:paginate,true', 'numeric', 'min:1'],
                    'per_page' => ['nullable', 'required_if:paginate,true', 'numeric', 'min:10'],
                    'limit' => ['nullable', 'integer', 'min:1'],
                ];
            case 'read':
                return [];
            case 'store':
                return [
                    'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
                    'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
                    'code' => ['required', 'string', 'max:255', new StockTransferStoreValidCode($this->company_id)],
                    'date' => ['required', 'date'],
                    'source_warehouse_id' => ['required', 'integer', new IsValidWarehouse($this->company_id, true)],
                    'destination_warehouse_id' => ['required', 'integer', 'different:source_warehouse_id', new IsValidWarehouse($this->company_id, true)],
                    'remarks' => ['nullable', 'string', 'max:255'],
                    'is_posted' => ['required', 'boolean'],
                ];
            case 'update':
                return [
                    'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
                    'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
                    'code' => ['required', 'string', 'max:255', new StockTransferUpdateValidCode($this->company_id, $this->route('stock_transfer'))],
                    'date' => ['required', 'date'],
                    'source_warehouse_id' => ['required', 'integer', new IsValidWarehouse($this->company_id, true)],
                    'destination_warehouse_id' => ['required', 'integer', 'different:source_warehouse_id', new IsValidWarehouse($this->company_id, true)],
                    'remarks' => ['nullable', 'string', 'max:255'],
                    'is_posted' => ['required', 'boolean'],
                ];
            case 'delete':
                return [

                ];
            default:
                return [
                    '' => 'required',
                ];
        }
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.stock_transfer.company'),
            'branch_id' => trans('validation_attributes.stock_transfer.branch'),
            'code' => trans('validation_attributes.stock_transfer.code'),
            'date' => trans('validation_attributes.stock_transfer.date'),
            'source_warehouse_id' => trans('validation_attributes.stock_transfer.source_warehouse'),
            'destination_warehouse_id' => trans('validation_attributes.stock_transfer.destination_warehouse'),
            'remarks' => trans('validation_attributes.stock_transfer.remarks'),
            'is_posted' => trans('validation_attributes.stock_transfer.is_posted'),
        ];
    }

    public function validationData()
    {
        $additionalArray = [];

        return array_merge($this->all(), $additionalArray);
    }

    public function prepareForValidation()
    {
        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                $this->merge([
                    'with_trashed' => $this->has('with_trashed') ? $this->with_trashed : null,

                    'search' => $this->has('search') ? $this->search : null,
                    'company_id' => $this->has('company_id') ? HashidsHelper::decodeId($this->company_id) : null,

                    'page' => $this->has('page') ? $this->page : null,
                    'per_page' => $this->has('per_page') ? $this->per_page : null,
                    'limit' => $this->has('limit') ? $this->limit : null,
                ]);
                break;
            case 'read':
                $this->merge([]);
                break;
            case 'store':
            case 'update':
                $this->merge([
                    'company_id' => $this->has('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
                    'remarks' => $this->has('remarks') ? $this['remarks'] : null,
                ]);
                break;
            default:
                $this->merge([]);
                break;
        }
    }
}
