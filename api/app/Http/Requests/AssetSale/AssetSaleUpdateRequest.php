<?php

namespace App\Http\Requests\AssetSale;

use App\Helpers\HashidsHelper;
use App\Models\AssetSale;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidCustomer;
use App\Rules\IsValidDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AssetSaleUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User $user */
        $user = Auth::user();
        $assetSale = $this->route('asset_sale');

        return $assetSale instanceof AssetSale && $user->can('update', $assetSale);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
            'code' => ['required', 'string', 'max:255'],
            'date' => ['required', 'string', new IsValidDate('Y-m-d H:i:s')],
            'due_days' => ['required', 'integer', 'min:0'],
            'customer_id' => ['required', 'integer', 'bail', new ExistsForCompany('customers', $this->company_id), new IsValidCustomer()],
            'remarks' => ['present', 'string', 'max:255'],
            'is_posted' => ['required', 'boolean'],
            'rounding' => ['required', 'numeric'],

            'delete_item_ids' => ['present', 'array'],
            'delete_item_ids.*' => ['required', 'integer', 'distinct', new ExistsForCompany('asset_sale_items', $this->company_id)],
            'items' => ['present', 'array'],
            'items.*.id' => ['present', 'nullable', 'integer', new ExistsForCompany('asset_sale_items', $this->company_id)],
            'items.*.qty' => ['required', 'numeric', 'min:1'],
            'items.*.asset_id' => ['required', 'integer', 'distinct', new ExistsForCompany('assets', $this->company_id)],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.remarks' => ['present', 'string', 'max:255'],
            'items.*.delete_serial_ids' => ['present', 'array'],
            'items.*.delete_serial_ids.*' => ['required', 'integer', 'distinct', new ExistsForCompany('asset_sale_item_serials', $this->company_id)],
            'items.*.serials' => ['present', 'array'],
            'items.*.serials.*.id' => ['present', 'nullable', 'integer', new ExistsForCompany('asset_sale_item_serials', $this->company_id)],
            'items.*.serials.*.serial' => ['required', 'distinct', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.asset_sale.company_id'),
            'branch_id' => trans('validation_attributes.asset_sale.branch_id'),
            'code' => trans('validation_attributes.asset_sale.code'),
            'date' => trans('validation_attributes.asset_sale.date'),
            'due_days' => trans('validation_attributes.asset_sale.due_days'),
            'customer_id' => trans('validation_attributes.asset_sale.customer_id'),
            'remarks' => trans('validation_attributes.asset_sale.remarks'),
            'is_posted' => trans('validation_attributes.asset_sale.is_posted'),
            'rounding' => trans('validation_attributes.asset_sale.rounding'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'customer_id' => $this->filled('customer_id') ? HashidsHelper::decodeId($this->customer_id) : null,
        ]);

        if (is_array($this->input('delete_item_ids'))) {
            $deleteItemIds = [];
            foreach ($this->input('delete_item_ids') as $deleteItemId) {
                $deleteItemIds[] = HashidsHelper::decodeId($deleteItemId);
            }
            $this->merge(['delete_item_ids' => $deleteItemIds]);
        }

        if (is_array($this->input('items'))) {
            $items = [];
            foreach ($this->input('items') as $item) {
                if (! empty($item['id'])) {
                    $item['id'] = HashidsHelper::decodeId($item['id']);
                }
                if (isset($item['asset_id'])) {
                    $item['asset_id'] = HashidsHelper::decodeId($item['asset_id']);
                }

                if (array_key_exists('delete_serial_ids', $item) && is_array($item['delete_serial_ids'])) {
                    $deleteSerialIds = [];
                    foreach ($item['delete_serial_ids'] as $deleteSerialId) {
                        $deleteSerialIds[] = HashidsHelper::decodeId($deleteSerialId);
                    }
                    $item['delete_serial_ids'] = $deleteSerialIds;
                }

                if (array_key_exists('serials', $item) && is_array($item['serials'])) {
                    $serials = [];
                    foreach ($item['serials'] as $serialItem) {
                        if (! empty($serialItem['id'])) {
                            $serialItem['id'] = HashidsHelper::decodeId($serialItem['id']);
                        }
                        $serials[] = $serialItem;
                    }
                    $item['serials'] = $serials;
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

            $assetSale = $this->route('asset_sale');
            if (! $assetSale instanceof AssetSale) {
                return;
            }

            $assetSale->loadMissing('items.serials');

            $items = is_array($this->input('items')) ? $this->input('items') : [];
            if (empty($items)) {
                $validator->errors()->add('items', trans('validation.asset_sale.at_least_one_item'));

                return;
            }

            $this->validateItemSerialCounts($validator, $items);
            $this->validateDuplicateSerials($validator, $items);

            $allowedItemIds = $assetSale->items->pluck('id')->map(fn ($id) => (int) $id)->all();
            $allowedSerialIdsByItemId = $assetSale->items
                ->mapWithKeys(fn ($item) => [
                    (int) $item->id => $item->serials->pluck('id')->map(fn ($id) => (int) $id)->all(),
                ])->all();
            $allowedSerialValuesByItemId = $assetSale->items
                ->mapWithKeys(fn ($item) => [
                    (int) $item->id => $item->serials->pluck('serial')->map(fn ($serial) => trim((string) $serial))->all(),
                ])->all();

            foreach (($this->input('delete_item_ids') ?? []) as $i => $deleteItemId) {
                if (! empty($deleteItemId) && ! in_array((int) $deleteItemId, $allowedItemIds, true)) {
                    $validator->errors()->add('delete_item_ids.'.$i, trans('rules.asset_sale.invalid_item_reference'));
                }
            }

            foreach ($items as $i => $item) {
                if (! is_array($item)) {
                    continue;
                }

                $itemId = $item['id'] ?? null;
                $allowedSerialIds = [];

                if (! empty($itemId)) {
                    if (! in_array((int) $itemId, $allowedItemIds, true)) {
                        $validator->errors()->add('items.'.$i.'.id', trans('rules.asset_sale.invalid_item_reference'));
                    } else {
                        $allowedSerialIds = $allowedSerialIdsByItemId[(int) $itemId] ?? [];
                    }
                }

                foreach (($item['delete_serial_ids'] ?? []) as $j => $deleteSerialId) {
                    if (! empty($deleteSerialId) && ! in_array((int) $deleteSerialId, $allowedSerialIds, true)) {
                        $validator->errors()->add(
                            'items.'.$i.'.delete_serial_ids.'.$j,
                            trans('rules.asset_sale.invalid_item_serial_reference')
                        );
                    }
                }

                foreach (($item['serials'] ?? []) as $j => $serialItem) {
                    $serialId = $serialItem['id'] ?? null;
                    if (! empty($serialId) && ! in_array((int) $serialId, $allowedSerialIds, true)) {
                        $validator->errors()->add(
                            'items.'.$i.'.serials.'.$j.'.id',
                            trans('rules.asset_sale.invalid_item_serial_reference')
                        );
                    }
                }
            }

            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $this->validateOutgoingSerials($validator, $items, $allowedSerialValuesByItemId);
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
                    trans('validation.asset_sale_item.qty_must_be_integer')
                );

                continue;
            }

            $serialCount = (string) count($item['serials'] ?? []);
            if (bccomp($serialCount, (string) $qty, 8) !== 0) {
                $validator->errors()->add(
                    'items.'.$i.'.serials',
                    trans('validation.asset_sale_item.serial_count_not_match_qty')
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
                        trans('validation.asset_sale.duplicate_serial_in_transaction')
                    );
                }

                $seen[$serial] = true;
            }
        }
    }

    private function validateOutgoingSerials($validator, array $items, array $allowedSerialValuesByItemId): void
    {

    }
}
