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

class AssetPurchaseUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User $user */
        $user = Auth::user();
        $assetPurchase = $this->route('asset_purchase');

        return $assetPurchase instanceof AssetPurchase && $user->can('update', $assetPurchase);
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

            'delete_item_ids' => ['present', 'array'],
            'delete_item_ids.*' => ['required', 'integer', 'distinct', new ExistsForCompany('asset_purchase_items', $this->company_id)],
            'items' => ['present', 'array'],
            'items.*.id' => ['present', 'nullable', 'integer', new ExistsForCompany('asset_purchase_items', $this->company_id)],
            'items.*.qty' => ['required', 'numeric', 'min:1'],
            'items.*.asset_id' => ['required', 'integer', 'distinct', new ExistsForCompany('assets', $this->company_id)],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.remarks' => ['present', 'string', 'max:255'],
            'items.*.delete_serial_ids' => ['present', 'array'],
            'items.*.delete_serial_ids.*' => ['required', 'integer', 'distinct', new ExistsForCompany('asset_purchase_item_serials', $this->company_id)],
            'items.*.serials' => ['present', 'array'],
            'items.*.serials.*.id' => ['present', 'nullable', 'integer', new ExistsForCompany('asset_purchase_item_serials', $this->company_id)],
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

            $assetPurchase = $this->route('asset_purchase');
            if (! $assetPurchase instanceof AssetPurchase) {
                return;
            }

            $assetPurchase->loadMissing('items.serials');

            $items = is_array($this->input('items')) ? $this->input('items') : [];
            if (empty($items)) {
                $validator->errors()->add('items', trans('validation.asset_purchase.at_least_one_item'));

                return;
            }

            $this->validateItemSerialCounts($validator, $items);
            $this->validateDuplicateSerials($validator, $items);

            $allowedItemIds = $assetPurchase->items->pluck('id')->map(fn ($id) => (int) $id)->all();
            $allowedSerialIdsByItemId = $assetPurchase->items
                ->mapWithKeys(fn ($item) => [
                    (int) $item->id => $item->serials->pluck('id')->map(fn ($id) => (int) $id)->all(),
                ])
                ->all();
            $allowedSerialValuesByItemId = $assetPurchase->items
                ->mapWithKeys(fn ($item) => [
                    (int) $item->id => $item->serials->pluck('serial')->map(fn ($serial) => trim((string) $serial))->all(),
                ])
                ->all();

            foreach (($this->input('delete_item_ids') ?? []) as $i => $deleteItemId) {
                if (! empty($deleteItemId) && ! in_array((int) $deleteItemId, $allowedItemIds, true)) {
                    $validator->errors()->add('delete_item_ids.'.$i, trans('rules.asset_purchase.invalid_item_reference'));
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
                        $validator->errors()->add('items.'.$i.'.id', trans('rules.asset_purchase.invalid_item_reference'));
                    } else {
                        $allowedSerialIds = $allowedSerialIdsByItemId[(int) $itemId] ?? [];
                    }
                }

                foreach (($item['delete_serial_ids'] ?? []) as $j => $deleteSerialId) {
                    if (! empty($deleteSerialId) && ! in_array((int) $deleteSerialId, $allowedSerialIds, true)) {
                        $validator->errors()->add(
                            'items.'.$i.'.delete_serial_ids.'.$j,
                            trans('rules.asset_purchase.invalid_item_serial_reference')
                        );
                    }
                }

                foreach (($item['serials'] ?? []) as $j => $serialItem) {
                    $serialId = $serialItem['id'] ?? null;
                    if (! empty($serialId) && ! in_array((int) $serialId, $allowedSerialIds, true)) {
                        $validator->errors()->add(
                            'items.'.$i.'.serials.'.$j.'.id',
                            trans('rules.asset_purchase.invalid_item_serial_reference')
                        );
                    }
                }
            }

            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $this->validateIncomingSerialAvailability(
                validator: $validator,
                items: $items,
                allowedSerialValuesByItemId: $allowedSerialValuesByItemId,
            );
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

    private function validateIncomingSerialAvailability($validator, array $items, array $allowedSerialValuesByItemId): void
    {

    }
}
