<?php

namespace App\Http\Requests;

use App\Enums\RecordStatusEnum;
use App\Helpers\HashidsHelper;
use App\Models\StockTransferProductUnitSerial;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidStockTransfer;
use App\Rules\IsValidStockTransferProductUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StockTransferProductUnitSerialRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $stockTransferProductUnitSerial = $this->route('stock_transfer_product_unit_serial');

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                return $user->can('viewAny', StockTransferProductUnitSerial::class) ? true : false;
            case 'read':
                return $user->can('view', StockTransferProductUnitSerial::class, $stockTransferProductUnitSerial) ? true : false;
            case 'store':
                return $user->can('create', StockTransferProductUnitSerial::class) ? true : false;
            case 'update':
                return $user->can('update', StockTransferProductUnitSerial::class, $stockTransferProductUnitSerial) ? true : false;
            case 'delete':
                return $user->can('delete', StockTransferProductUnitSerial::class, $stockTransferProductUnitSerial) ? true : false;
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
                    'stock_transfer_id' => ['required', 'integer', new IsValidStockTransfer()],
                    'stock_transfer_product_unit_id' => ['required', 'integer', new IsValidStockTransferProductUnit()],
                    'serial' => ['required', 'string', 'max:255'],
                ];
            case 'update':
                return [
                    'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
                    'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
                    'stock_transfer_id' => ['required', 'integer', new IsValidStockTransfer()],
                    'stock_transfer_product_unit_id' => ['required', 'integer', new IsValidStockTransferProductUnit()],
                    'serial' => ['required', 'string', 'max:255'],
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
            'company_id' => trans('validation_attributes.stock_transfer_product_unit_serial.company'),
            'branch_id' => trans('validation_attributes.stock_transfer_product_unit_serial.branch'),
            'stock_transfer_id' => trans('validation_attributes.stock_transfer_product_unit_serial.stock_transfer'),
            'stock_transfer_product_unit_id' => trans('validation_attributes.stock_transfer_product_unit_serial.stock_transfer_product_unit'),
            'serial' => trans('validation_attributes.stock_transfer_product_unit_serial.serial'),
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
