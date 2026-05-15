<?php

namespace App\Http\Requests\AssetAdjustment;

use App\Helpers\HashidsHelper;
use App\Models\AssetAdjustment;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AssetAdjustmentUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $assetAdjustment = $this->route('asset_adjustment');

        return $user->can('update', AssetAdjustment::class, $assetAdjustment) ? true : false;
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
            'code' => ['required', 'string', 'max:255'],
            'date' => ['required', 'string', new IsValidDate('Y-m-d H:i:s')],
            'remarks' => ['present', 'string', 'max:255'],
            'is_posted' => ['required', 'boolean'],

            'delete_in_item_ids' => ['present', 'array'],
            'delete_in_item_ids.*' => ['required', 'integer', 'distinct', new ExistsForCompany('asset_adjustment_in_items', $this->company_id)],
            'in_items' => ['present', 'array'],
            'in_items.*.id' => ['present', 'nullable', 'integer', new ExistsForCompany('asset_adjustment_in_items', $this->company_id)],
            'in_items.*.qty' => ['required', 'numeric', 'min:1'],
            'in_items.*.asset_id' => ['required', 'integer', 'distinct', new ExistsForCompany('assets', $this->company_id)],
            'in_items.*.remarks' => ['present', 'string', 'max:255'],
            'in_items.*.delete_serial_ids' => ['present', 'array'],
            'in_items.*.delete_serial_ids.*' => ['required', 'integer', 'distinct', new ExistsForCompany('asset_adjustment_in_item_serials', $this->company_id)],
            'in_items.*.serials' => ['present', 'array'],
            'in_items.*.serials.*.id' => ['present', 'nullable', 'integer', new ExistsForCompany('asset_adjustment_in_item_serials', $this->company_id)],
            'in_items.*.serials.*.serial' => ['required', 'distinct', 'string', 'max:255'],

            'delete_out_item_ids' => ['present', 'array'],
            'delete_out_item_ids.*' => ['required', 'integer', 'distinct', new ExistsForCompany('asset_adjustment_out_items', $this->company_id)],
            'out_items' => ['present', 'array'],
            'out_items.*.id' => ['present', 'nullable', 'integer', new ExistsForCompany('asset_adjustment_out_items', $this->company_id)],
            'out_items.*.qty' => ['required', 'numeric', 'min:1'],
            'out_items.*.asset_id' => ['required', 'integer', 'distinct', new ExistsForCompany('assets', $this->company_id)],
            'out_items.*.remarks' => ['present', 'string', 'max:255'],
            'out_items.*.delete_serial_ids' => ['present', 'array'],
            'out_items.*.delete_serial_ids.*' => ['required', 'integer', 'distinct', new ExistsForCompany('asset_adjustment_out_item_serials', $this->company_id)],
            'out_items.*.serials' => ['present', 'array'],
            'out_items.*.serials.*.id' => ['present', 'nullable', 'integer', new ExistsForCompany('asset_adjustment_out_item_serials', $this->company_id)],
            'out_items.*.serials.*.serial' => ['required', 'distinct', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.asset_adjustment.company_id'),
            'branch_id' => trans('validation_attributes.asset_adjustment.branch_id'),
            'code' => trans('validation_attributes.asset_adjustment.code'),
            'date' => trans('validation_attributes.asset_adjustment.date'),
            'remarks' => trans('validation_attributes.asset_adjustment.remarks'),
            'is_posted' => trans('validation_attributes.asset_adjustment.is_posted'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
        ]);

        if (is_array($this->input('delete_in_item_ids'))) {
            $deleteInItemIds = [];
            foreach ($this->input('delete_in_item_ids') as $deleteInItemId) {
                $deleteInItemIds[] = HashidsHelper::decodeId($deleteInItemId);
            }
            $this->merge(['delete_in_item_ids' => $deleteInItemIds]);
        }

        if (is_array($this->input('in_items'))) {
            $inItems = [];
            foreach ($this->input('in_items') as $item) {
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

                $inItems[] = $item;
            }
            $this->merge(['in_items' => $inItems]);
        }

        if (is_array($this->input('delete_out_item_ids'))) {
            $deleteOutItemIds = [];
            foreach ($this->input('delete_out_item_ids') as $deleteOutItemId) {
                $deleteOutItemIds[] = HashidsHelper::decodeId($deleteOutItemId);
            }
            $this->merge(['delete_out_item_ids' => $deleteOutItemIds]);
        }

        if (is_array($this->input('out_items'))) {
            $outItems = [];
            foreach ($this->input('out_items') as $item) {
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

                $outItems[] = $item;
            }
            $this->merge(['out_items' => $outItems]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $hasInitialErrors = $validator->errors()->isNotEmpty();
            if ($hasInitialErrors) {
                return;
            }

            $assetAdjustment = $this->route('asset_adjustment');
            if (! $assetAdjustment instanceof AssetAdjustment) {
                return;
            }

            $assetAdjustment->loadMissing('inItems.serials', 'outItems.serials');

            $data = $validator->getData();
            $inItems = is_array($data['in_items'] ?? null) ? $data['in_items'] : [];
            $outItems = is_array($data['out_items'] ?? null) ? $data['out_items'] : [];

            if (empty($inItems) && empty($outItems)) {
                $validator->errors()->add('in_items', trans('validation.asset_adjustment.at_least_one_item'));

                return;
            }

            $this->validateItemSerialCounts($validator, $inItems, 'in_items', 'validation.asset_adjustment_in_item');
            $this->validateItemSerialCounts($validator, $outItems, 'out_items', 'validation.asset_adjustment_out_item');

            $this->validateDuplicateSerials($validator, $inItems, 'in_items');
            $this->validateDuplicateSerials($validator, $outItems, 'out_items');
            $this->validateCrossDirectionDuplicateSerials($validator, $inItems, $outItems);

            $allowedInItemIds = $assetAdjustment->inItems->pluck('id')->map(fn ($id) => (int) $id)->all();
            $allowedOutItemIds = $assetAdjustment->outItems->pluck('id')->map(fn ($id) => (int) $id)->all();
            $allowedInSerialIdsByItemId = $assetAdjustment->inItems
                ->mapWithKeys(fn ($item) => [
                    (int) $item->id => $item->serials->pluck('id')->map(fn ($id) => (int) $id)->all(),
                ])
                ->all();
            $allowedOutSerialIdsByItemId = $assetAdjustment->outItems
                ->mapWithKeys(fn ($item) => [
                    (int) $item->id => $item->serials->pluck('id')->map(fn ($id) => (int) $id)->all(),
                ])
                ->all();
            $allowedInSerialValuesByItemId = $assetAdjustment->inItems
                ->mapWithKeys(fn ($item) => [
                    (int) $item->id => $item->serials->pluck('serial')->map(fn ($serial) => trim((string) $serial))->all(),
                ])
                ->all();
            $allowedOutSerialValuesByItemId = $assetAdjustment->outItems
                ->mapWithKeys(fn ($item) => [
                    (int) $item->id => $item->serials->pluck('serial')->map(fn ($serial) => trim((string) $serial))->all(),
                ])
                ->all();

            foreach (($data['delete_in_item_ids'] ?? []) as $i => $deleteInItemId) {
                if (! empty($deleteInItemId) && ! in_array((int) $deleteInItemId, $allowedInItemIds, true)) {
                    $validator->errors()->add('delete_in_item_ids.'.$i, trans('rules.asset_adjustment.invalid_in_item_reference'));
                }
            }

            foreach (($data['delete_out_item_ids'] ?? []) as $i => $deleteOutItemId) {
                if (! empty($deleteOutItemId) && ! in_array((int) $deleteOutItemId, $allowedOutItemIds, true)) {
                    $validator->errors()->add('delete_out_item_ids.'.$i, trans('rules.asset_adjustment.invalid_out_item_reference'));
                }
            }

            foreach ($inItems as $i => $item) {
                if (! is_array($item)) {
                    continue;
                }

                $itemId = $item['id'] ?? null;
                $allowedSerialIds = [];

                if (! empty($itemId)) {
                    if (! in_array((int) $itemId, $allowedInItemIds, true)) {
                        $validator->errors()->add('in_items.'.$i.'.id', trans('rules.asset_adjustment.invalid_in_item_reference'));
                    } else {
                        $allowedSerialIds = $allowedInSerialIdsByItemId[(int) $itemId] ?? [];
                    }
                }

                foreach (($item['delete_serial_ids'] ?? []) as $j => $deleteSerialId) {
                    if (! empty($deleteSerialId) && ! in_array((int) $deleteSerialId, $allowedSerialIds, true)) {
                        $validator->errors()->add(
                            'in_items.'.$i.'.delete_serial_ids.'.$j,
                            trans('rules.asset_adjustment.invalid_in_item_serial_reference')
                        );
                    }
                }

                foreach (($item['serials'] ?? []) as $j => $serialItem) {
                    if (! is_array($serialItem)) {
                        continue;
                    }

                    $serialId = $serialItem['id'] ?? null;
                    if (! empty($serialId) && ! in_array((int) $serialId, $allowedSerialIds, true)) {
                        $validator->errors()->add(
                            'in_items.'.$i.'.serials.'.$j.'.id',
                            trans('rules.asset_adjustment.invalid_in_item_serial_reference')
                        );
                    }
                }
            }

            foreach ($outItems as $i => $item) {
                if (! is_array($item)) {
                    continue;
                }

                $itemId = $item['id'] ?? null;
                $allowedSerialIds = [];

                if (! empty($itemId)) {
                    if (! in_array((int) $itemId, $allowedOutItemIds, true)) {
                        $validator->errors()->add('out_items.'.$i.'.id', trans('rules.asset_adjustment.invalid_out_item_reference'));
                    } else {
                        $allowedSerialIds = $allowedOutSerialIdsByItemId[(int) $itemId] ?? [];
                    }
                }

                foreach (($item['delete_serial_ids'] ?? []) as $j => $deleteSerialId) {
                    if (! empty($deleteSerialId) && ! in_array((int) $deleteSerialId, $allowedSerialIds, true)) {
                        $validator->errors()->add(
                            'out_items.'.$i.'.delete_serial_ids.'.$j,
                            trans('rules.asset_adjustment.invalid_out_item_serial_reference')
                        );
                    }
                }

                foreach (($item['serials'] ?? []) as $j => $serialItem) {
                    if (! is_array($serialItem)) {
                        continue;
                    }

                    $serialId = $serialItem['id'] ?? null;
                    if (! empty($serialId) && ! in_array((int) $serialId, $allowedSerialIds, true)) {
                        $validator->errors()->add(
                            'out_items.'.$i.'.serials.'.$j.'.id',
                            trans('rules.asset_adjustment.invalid_out_item_serial_reference')
                        );
                    }
                }
            }

            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $this->validateIncomingSerialAvailability($validator, $inItems, $allowedInSerialValuesByItemId);
            $this->validateOutgoingSerialAvailability($validator, $outItems, $allowedOutSerialValuesByItemId);
        });
    }

    private function validateItemSerialCounts($validator, array $items, string $fieldPrefix, string $translationPrefix): void
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
                    $fieldPrefix.'.'.$i.'.qty',
                    trans($translationPrefix.'.qty_must_be_integer')
                );

                continue;
            }

            $serialCount = (string) count($item['serials'] ?? []);
            if (bccomp($serialCount, (string) $qty, 8) !== 0) {
                $validator->errors()->add(
                    $fieldPrefix.'.'.$i.'.serials',
                    trans($translationPrefix.'.serial_count_not_match_qty')
                );
            }
        }
    }

    private function validateDuplicateSerials($validator, array $items, string $fieldPrefix): void
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
                        $fieldPrefix.'.'.$i.'.serials.'.$j.'.serial',
                        trans('validation.asset_adjustment.duplicate_serial_in_transaction')
                    );
                }

                $seen[$serial] = true;
            }
        }
    }

    private function validateCrossDirectionDuplicateSerials($validator, array $inItems, array $outItems): void
    {
        $inSerials = collect($inItems)
            ->flatMap(fn ($item) => collect($item['serials'] ?? [])->pluck('serial'))
            ->filter(fn ($serial) => filled($serial))
            ->map(fn ($serial) => trim((string) $serial))
            ->all();

        $outSerials = collect($outItems)
            ->flatMap(fn ($item) => collect($item['serials'] ?? [])->pluck('serial'))
            ->filter(fn ($serial) => filled($serial))
            ->map(fn ($serial) => trim((string) $serial))
            ->all();

        $duplicates = array_unique(array_intersect($inSerials, $outSerials));
        if (empty($duplicates)) {
            return;
        }

        foreach ($inItems as $i => $item) {
            foreach (($item['serials'] ?? []) as $j => $serialItem) {
                $serial = trim((string) ($serialItem['serial'] ?? ''));
                if (in_array($serial, $duplicates, true)) {
                    $validator->errors()->add(
                        'in_items.'.$i.'.serials.'.$j.'.serial',
                        trans('validation.asset_adjustment.duplicate_serial_in_transaction')
                    );
                }
            }
        }

        foreach ($outItems as $i => $item) {
            foreach (($item['serials'] ?? []) as $j => $serialItem) {
                $serial = trim((string) ($serialItem['serial'] ?? ''));
                if (in_array($serial, $duplicates, true)) {
                    $validator->errors()->add(
                        'out_items.'.$i.'.serials.'.$j.'.serial',
                        trans('validation.asset_adjustment.duplicate_serial_in_transaction')
                    );
                }
            }
        }
    }

    private function validateIncomingSerialAvailability($validator, array $inItems, array $allowedSerialValuesByItemId): void
    {

    }

    private function validateOutgoingSerialAvailability($validator, array $outItems, array $allowedSerialValuesByItemId): void
    {

    }
}
