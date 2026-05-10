<?php

namespace App\Http\Requests\JournalEntry;

use App\Helpers\HashidsHelper;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class JournalEntryUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User $user */
        $user = Auth::user();
        /** @var JournalEntry $journalEntry */
        $journalEntry = $this->route('journal_entry');

        return $user->can('update', $journalEntry);
    }

    public function prepareForValidation()
    {
        /** @var JournalEntry|null $journalEntry */
        $journalEntry = $this->route('journal_entry');

        $this->merge([
            'company_id' => $journalEntry?->company_id,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'reference_no' => $this->filled('reference_no') ? $this->reference_no : null,
            'remarks' => $this->filled('remarks') ? $this->remarks : null,
            'lines' => collect($this->lines ?? [])->map(function ($line) {
                $line['chart_of_account_id'] = ! empty($line['chart_of_account_id'])
                    ? HashidsHelper::decodeId($line['chart_of_account_id'])
                    : null;
                $line['remarks'] = ! empty($line['remarks']) ? $line['remarks'] : null;

                return $line;
            })->all(),
        ]);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer'],
            'branch_id' => ['required', 'integer', 'bail', new IsValidBranch($this->company_id, true)],
            'code' => ['required', 'string', 'max:255'],
            'date' => ['required', 'string', new IsValidDate('Y-m-d H:i:s')],
            'reference_no' => ['present', 'nullable', 'string', 'max:255'],
            'remarks' => ['present', 'nullable', 'string', 'max:255'],

            'lines' => ['required', 'array', 'min:2'],
            'lines.*.chart_of_account_id' => ['required', 'integer', new ExistsForCompany('chart_of_accounts', $this->company_id)],
            'lines.*.debit' => ['required', 'numeric', 'min:0'],
            'lines.*.credit' => ['required', 'numeric', 'min:0'],
            'lines.*.remarks' => ['present', 'nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $lines = collect($this->input('lines', []));
            $totalDebit = 0.0;
            $totalCredit = 0.0;

            $chartOfAccounts = ChartOfAccount::query()
                ->where('company_id', $this->company_id)
                ->whereIn('id', $lines->pluck('chart_of_account_id')->filter()->all())
                ->get()
                ->keyBy('id');

            foreach ($lines as $index => $line) {
                $debit = max((float) ($line['debit'] ?? 0), 0);
                $credit = max((float) ($line['credit'] ?? 0), 0);

                if (($debit > 0 && $credit > 0) || ($debit <= 0 && $credit <= 0)) {
                    $validator->errors()->add("lines.$index.debit", trans('rules.journal_entry.line_must_have_single_side_amount'));
                    $validator->errors()->add("lines.$index.credit", trans('rules.journal_entry.line_must_have_single_side_amount'));
                }

                $chartOfAccountId = $line['chart_of_account_id'] ?? null;
                $chartOfAccount = $chartOfAccounts->get($chartOfAccountId);
                if ($chartOfAccount) {
                    if ($chartOfAccount->is_group) {
                        $validator->errors()->add("lines.$index.chart_of_account_id", trans('rules.journal_entry.account_must_not_be_group'));
                    }

                    if (! $chartOfAccount->is_active) {
                        $validator->errors()->add("lines.$index.chart_of_account_id", trans('rules.journal_entry.account_must_be_active'));
                    }
                }

                $totalDebit += $debit;
                $totalCredit += $credit;
            }

            if ($totalDebit <= 0 || $totalCredit <= 0) {
                $validator->errors()->add('lines', trans('rules.journal_entry.total_must_be_positive'));
            }

            if (round($totalDebit, 8) !== round($totalCredit, 8)) {
                $validator->errors()->add('lines', trans('rules.journal_entry.total_debit_and_credit_must_balance'));
            }
        });
    }

    public function attributes()
    {
        return array_merge(
            trans('validation_attributes.journal_entry'),
            [
                'lines.*.chart_of_account_id' => trans('validation_attributes.journal_entry_line.chart_of_account_id'),
                'lines.*.debit' => trans('validation_attributes.journal_entry_line.debit'),
                'lines.*.credit' => trans('validation_attributes.journal_entry_line.credit'),
                'lines.*.remarks' => trans('validation_attributes.journal_entry_line.remarks'),
            ],
        );
    }
}
