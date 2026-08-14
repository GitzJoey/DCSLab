<?php

namespace App\Http\Requests\AssetPurchase;

use App\Helpers\HashidsHelper;
use App\Models\AssetPurchase;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidDate;
use App\Rules\IsValidSupplier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AssetPurchaseStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User $user */
        $user = Auth::user();

        return $user->can('create', AssetPurchase::class);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
            'code' => ['required', 'string', 'max:255'],
            'date' => ['required', 'string', new IsValidDate('Y-m-d H:i:s')],
            'due_days' => ['required', 'integer', 'min:0'],
            'supplier_id' => ['required', 'integer', 'bail', new ExistsForCompany('suppliers', $this->company_id), new IsValidSupplier($this->company_id)],
            'remarks' => ['present', 'string', 'max:255'],
            'is_posted' => ['required', 'boolean'],
            'additional_cost' => ['required', 'numeric', 'min:0'],
            'rounding' => ['required', 'numeric'],

            'items' => ['present', 'array'],
            'items.*.qty' => ['required', 'numeric', 'min:1'],
            'items.*.asset_id' => ['required', 'integer', 'distinct', new ExistsForCompany('assets', $this->company_id)],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.remarks' => ['present', 'string', 'max:255'],
            'items.*.serials' => ['present', 'array'],
            'items.*.serials.*.serial' => ['required', 'distinct', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.asset_purchase.company_id'),
            'branch_id' => trans('validation_attributes.asset_purchase.branch_id'),
            'code' => trans('validation_attributes.asset_purchase.code'),
            'date' => trans('validation_attributes.asset_purchase.date'),
            'due_days' => trans('validation_attributes.asset_purchase.due_days'),
            'supplier_id' => trans('validation_attributes.asset_purchase.supplier_id'),
            'remarks' => trans('validation_attributes.asset_purchase.remarks'),
            'is_posted' => trans('validation_attributes.asset_purchase.is_posted'),
            'additional_cost' => trans('validation_attributes.asset_purchase.additional_cost'),
            'rounding' => trans('validation_attributes.asset_purchase.rounding'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'supplier_id' => $this->filled('supplier_id') ? HashidsHelper::decodeId($this->supplier_id) : null,
        ]);

        if (is_array($this->input('items'))) {
            $items = [];
            foreach ($this->input('items') as $item) {
                if (isset($item['asset_id'])) {
                    $item['asset_id'] = HashidsHelper::decodeId($item['asset_id']);
                }
                $items[] = $item;
            }
            $this->merge(['items' => $items]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $items = is_array($this->input('items')) ? $this->input('items') : [];

            if (empty($items)) {
                $validator->errors()->add('items', trans('validation.asset_purchase.at_least_one_item'));

                return;
            }

            $this->validateItemSerialCounts($validator, $items);
            $this->validateDuplicateSerials($validator, $items);

            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $this->validateIncomingSerialAvailability($validator, $items);
        });
    }

    private function validateItemSerialCounts($validator, array $items): void
    {
        foreach ($items as $i => $item) {
            if (! is_array($item)) {
                continue;
            }

            $qty = $item['qty'] ?? null;
            if (! is_numeric($qty)) {
                continue;
            }

            $normalizedQty = rtrim(rtrim((string) $qty, '0'), '.');
            if ($normalizedQty === '') {
                $normalizedQty = '0';
            }

            if (str_contains($normalizedQty, '.')) {
                $validator->errors()->add(
                    'items.'.$i.'.qty',
                    trans('validation.asset_purchase_item.qty_must_be_integer')
                );

                continue;
            }

            $serialCount = (string) count($item['serials'] ?? []);
            if (bccomp($serialCount, (string) $qty, 8) !== 0) {
                $validator->errors()->add(
                    'items.'.$i.'.serials',
                    trans('validation.asset_purchase_item.serial_count_not_match_qty')
                );
            }
        }
    }

    private function validateDuplicateSerials($validator, array $items): void
    {
        $seen = [];

        foreach ($items as $i => $item) {
            if (! is_array($item)) {
                continue;
            }

            foreach (($item['serials'] ?? []) as $j => $serialItem) {
                if (! is_array($serialItem)) {
                    continue;
                }

                $serial = trim((string) ($serialItem['serial'] ?? ''));
                if ($serial === '') {
                    continue;
                }

                if (isset($seen[$serial])) {
                    $validator->errors()->add(
                        'items.'.$i.'.serials.'.$j.'.serial',
                        trans('validation.asset_purchase.duplicate_serial_in_transaction')
                    );
                }

                $seen[$serial] = true;
            }
        }
    }

    private function validateIncomingSerialAvailability($validator, array $items): void
    {

    }
}
