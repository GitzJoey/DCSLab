<?php

namespace App\Http\Controllers;

use App\Actions\JournalEntryItem\JournalEntryItemActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Helpers\HashidsHelper;
use App\Http\Resources\JournalEntryItemResource;
use App\Models\JournalEntry;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidDate;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JournalEntryItemController extends BaseController
{
    public function __construct(
        private readonly JournalEntryItemActions $journalEntryItemActions,
    ) {
        parent::__construct();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', JournalEntry::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'branch_id' => $request->filled('branch_id') ? HashidsHelper::decodeId($request->branch_id) : null,
            'journal_entry_id' => $request->filled('journal_entry_id') ? HashidsHelper::decodeId($request->journal_entry_id) : null,
            'chart_of_account_id' => $request->filled('chart_of_account_id') ? HashidsHelper::decodeId($request->chart_of_account_id) : null,
            'include_id' => $request->filled('include_id') ? HashidsHelper::decodeId($request->include_id) : null,
        ]);

        $validated = $request->validate([
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['nullable', 'integer', 'bail', new ExistsForCompany('branches', $request->company_id), new IsValidBranch($request->company_id, false)],
            'search' => ['nullable', 'string'],
            'start_date' => ['nullable', 'string', new IsValidDate('Y-m-d H:i:s')],
            'end_date' => ['nullable', 'string', new IsValidDate('Y-m-d H:i:s')],
            'journal_entry_id' => ['nullable', 'integer', new ExistsForCompany('journal_entries', $request->company_id)],
            'chart_of_account_id' => ['nullable', 'integer', new ExistsForCompany('chart_of_accounts', $request->company_id)],
            'include_id' => ['nullable', 'integer', new ExistsForCompany('journal_entry_items', $request->company_id)],
            'refresh' => ['required', 'boolean'],
            'paginate' => ['nullable', 'array', 'required_without:get', 'prohibits:get'],
            'paginate.page' => ['required_with:paginate', 'integer', 'min:1'],
            'paginate.per_page' => ['required_with:paginate', 'integer', 'min:1'],
            'get' => ['nullable', 'array', 'required_without:paginate', 'prohibits:paginate'],
            'get.limit' => ['required_with:get', 'integer', 'min:1'],
        ]);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->journalEntryItemActions->readAny(
                companyId: $validated['company_id'],
                branchId: $validated['branch_id'] ?? null,
                search: $validated['search'] ?? null,
                startDate: $validated['start_date'] ?? null,
                endDate: $validated['end_date'] ?? null,
                journalEntryId: $validated['journal_entry_id'] ?? null,
                chartOfAccountId: $validated['chart_of_account_id'] ?? null,
                includeId: $validated['include_id'] ?? null,
                execute: new ExecuteDTO(
                    useCache: ! $validated['refresh'],
                    pagination: (function () use ($validated) {
                        if (! isset($validated['paginate'])) {
                            return null;
                        }

                        return new ExecutePaginationDTO(
                            page: $validated['paginate']['page'],
                            perPage: $validated['paginate']['per_page'],
                        );
                    })(),
                    get: (function () use ($validated) {
                        if (! isset($validated['get'])) {
                            return null;
                        }

                        return new ExecuteGetDTO(
                            limit: $validated['get']['limit'],
                        );
                    })(),
                ),
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return JournalEntryItemResource::collection($result);
    }
}
