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

class AssetAdjustmentStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', AssetAdjustment::class) ? true : false;
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

            'in_items' => ['present', 'array'],
            'in_items.*.qty' => ['required', 'numeric', 'min:1'],
            'in_items.*.asset_id' => ['required', 'integer', 'distinct', new ExistsForCompany('assets', $this->company_id)],
            'in_items.*.remarks' => ['present', 'string', 'max:255'],
            'in_items.*.serials' => ['present', 'array'],
            'in_items.*.serials.*.serial' => ['required', 'distinct', 'string', 'max:255'],

            'out_items' => ['present', 'array'],
            'out_items.*.qty' => ['required', 'numeric', 'min:1'],
            'out_items.*.asset_id' => ['required', 'integer', 'distinct', new ExistsForCompany('assets', $this->company_id)],
            'out_items.*.remarks' => ['present', 'string', 'max:255'],
            'out_items.*.serials' => ['present', 'array'],
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

        if (is_array($this->input('in_items'))) {
            $inItems = [];
            foreach ($this->input('in_items') as $item) {
                if (isset($item['asset_id'])) {
                    $item['asset_id'] = HashidsHelper::decodeId($item['asset_id']);
                }
                $inItems[] = $item;
            }
            $this->merge(['in_items' => $inItems]);
        }

        if (is_array($this->input('out_items'))) {
            $outItems = [];
            foreach ($this->input('out_items') as $item) {
                if (isset($item['asset_id'])) {
                    $item['asset_id'] = HashidsHelper::decodeId($item['asset_id']);
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

            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $this->validateIncomingSerialAvailability($validator, $inItems);
            $this->validateOutgoingSerialAvailability($validator, $outItems);
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

    private function validateIncomingSerialAvailability($validator, array $inItems): void
    {

    }

    private function validateOutgoingSerialAvailability($validator, array $outItems): void
    {

    }
}
