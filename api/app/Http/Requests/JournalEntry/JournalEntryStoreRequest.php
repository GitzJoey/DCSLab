<?php

namespace App\Http\Requests\JournalEntry;

use App\Helpers\HashidsHelper;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class JournalEntryStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User $user */
        $user = Auth::user();

        return $user->can('create', JournalEntry::class);
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'source_type' => $this->filled('source_type') ? $this->source_type : null,
            'source_id' => $this->filled('source_id') ? (int) $this->source_id : null,
            'reference_no' => $this->filled('reference_no') ? $this->reference_no : null,
            'remarks' => $this->filled('remarks') ? $this->remarks : null,
            'items' => collect($this->items ?? [])->map(function ($item) {
                $item['chart_of_account_id'] = ! empty($item['chart_of_account_id'])
                    ? HashidsHelper::decodeId($item['chart_of_account_id'])
                    : null;
                $item['remarks'] = ! empty($item['remarks']) ? $item['remarks'] : null;

                return $item;
            })->all(),
        ]);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', 'bail', new IsValidBranch($this->company_id, true)],
            'code' => ['required', 'string', 'max:255'],
            'date' => ['required', 'string', new IsValidDate('Y-m-d H:i:s')],
            'source_type' => ['present', 'nullable', 'string', 'max:255'],
            'source_id' => ['present', 'nullable', 'integer', 'min:1'],
            'reference_no' => ['present', 'nullable', 'string', 'max:255'],
            'remarks' => ['present', 'nullable', 'string', 'max:255'],

            'items' => ['required', 'array', 'min:2'],
            'items.*.chart_of_account_id' => ['required', 'integer', new ExistsForCompany('chart_of_accounts', $this->company_id)],
            'items.*.debit' => ['required', 'numeric', 'min:0'],
            'items.*.credit' => ['required', 'numeric', 'min:0'],
            'items.*.remarks' => ['present', 'nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (is_null($this->source_type) xor is_null($this->source_id)) {
                $validator->errors()->add('source_type', trans('rules.journal_entry.source_type_and_source_id_must_be_paired'));
                $validator->errors()->add('source_id', trans('rules.journal_entry.source_type_and_source_id_must_be_paired'));
            }

            $items = collect($this->input('items', []));
            $totalDebit = 0.0;
            $totalCredit = 0.0;

            $chartOfAccounts = ChartOfAccount::query()
                ->where('company_id', $this->company_id)
                ->whereIn('id', $items->pluck('chart_of_account_id')->filter()->all())
                ->get()
                ->keyBy('id');

            foreach ($items as $index => $item) {
                $debit = max((float) ($item['debit'] ?? 0), 0);
                $credit = max((float) ($item['credit'] ?? 0), 0);

                if (($debit > 0 && $credit > 0) || ($debit <= 0 && $credit <= 0)) {
                    $validator->errors()->add("items.$index.debit", trans('rules.journal_entry.item_must_have_single_side_amount'));
                    $validator->errors()->add("items.$index.credit", trans('rules.journal_entry.item_must_have_single_side_amount'));
                }

                $chartOfAccountId = $item['chart_of_account_id'] ?? null;
                $chartOfAccount = $chartOfAccounts->get($chartOfAccountId);
                if ($chartOfAccount) {
                    if ($chartOfAccount->is_group) {
                        $validator->errors()->add("items.$index.chart_of_account_id", trans('rules.journal_entry.account_must_not_be_group'));
                    }

                    if (! $chartOfAccount->is_active) {
                        $validator->errors()->add("items.$index.chart_of_account_id", trans('rules.journal_entry.account_must_be_active'));
                    }
                }

                $totalDebit += $debit;
                $totalCredit += $credit;
            }

            if ($totalDebit <= 0 || $totalCredit <= 0) {
                $validator->errors()->add('items', trans('rules.journal_entry.total_must_be_positive'));
            }

            if (round($totalDebit, 8) !== round($totalCredit, 8)) {
                $validator->errors()->add('items', trans('rules.journal_entry.total_debit_and_credit_must_balance'));
            }
        });
    }

    public function attributes()
    {
        return array_merge(
            trans('validation_attributes.journal_entry'),
            [
                'items.*.chart_of_account_id' => trans('validation_attributes.journal_entry_item.chart_of_account_id'),
                'items.*.debit' => trans('validation_attributes.journal_entry_item.debit'),
                'items.*.credit' => trans('validation_attributes.journal_entry_item.credit'),
                'items.*.remarks' => trans('validation_attributes.journal_entry_item.remarks'),
            ],
        );
    }
}
