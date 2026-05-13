<?php

namespace App\Http\Controllers;

use App\Actions\JournalEntry\JournalEntryActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\DTOs\JournalEntryCreateDTO;
use App\DTOs\JournalEntryItemDTO;
use App\DTOs\JournalEntryUpdateDTO;
use App\Enums\JournalEntryTypeEnum;
use App\Helpers\HashidsHelper;
use App\Http\Requests\JournalEntry\JournalEntryStoreRequest;
use App\Http\Requests\JournalEntry\JournalEntryUpdateRequest;
use App\Http\Resources\JournalEntryResource;
use App\Models\JournalEntry;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidDate;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JournalEntryController extends BaseController
{
    public function __construct(
        private readonly JournalEntryActions $journalEntryActions,
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
            'journal_type' => JournalEntryTypeEnum::isValid($request->journal_type) ? JournalEntryTypeEnum::resolveToEnum($request->journal_type)->value : null,
            'source_id' => $request->filled('source_id') ? (int) $request->source_id : null,
        ]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['nullable', 'integer', 'bail', new ExistsForCompany('branches', $request->company_id), new IsValidBranch($request->company_id, false)],
            'search' => ['nullable', 'string'],
            'start_date' => ['nullable', 'string', new IsValidDate('Y-m-d H:i:s')],
            'end_date' => ['nullable', 'string', new IsValidDate('Y-m-d H:i:s')],
            'journal_type' => ['nullable', new \Illuminate\Validation\Rules\Enum(JournalEntryTypeEnum::class)],
            'source_type' => ['nullable', 'string', 'max:255'],
            'source_id' => ['nullable', 'integer', 'min:1'],
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
            $result = $this->journalEntryActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'] ?? null,
                search: $validatedRequest['search'] ?? null,
                startDate: $validatedRequest['start_date'] ?? null,
                endDate: $validatedRequest['end_date'] ?? null,
                journalType: $validatedRequest['journal_type'] ?? null,
                sourceType: $validatedRequest['source_type'] ?? null,
                sourceId: $validatedRequest['source_id'] ?? null,
                execute: new ExecuteDTO(
                    useCache: ! $validatedRequest['refresh'],
                    pagination: isset($validatedRequest['paginate'])
                        ? new ExecutePaginationDTO(
                            page: $validatedRequest['paginate']['page'],
                            perPage: $validatedRequest['paginate']['per_page'],
                        )
                        : null,
                    get: isset($validatedRequest['get'])
                        ? new ExecuteGetDTO(limit: $validatedRequest['get']['limit'])
                        : null,
                ),
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : JournalEntryResource::collection($result);
    }

    public function read(JournalEntry $journalEntry)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $journalEntry);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->journalEntryActions->read($journalEntry);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : new JournalEntryResource($result);
    }

    public function store(JournalEntryStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueCode = $this->journalEntryActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    null,
                );
                if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            DB::beginTransaction();

            $dto = new JournalEntryCreateDTO(
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'],
                code: $validatedRequest['code'],
                date: $validatedRequest['date'],
                journalType: $validatedRequest['journal_type'],
                sourceType: $validatedRequest['source_type'],
                sourceId: $validatedRequest['source_id'],
                referenceNo: $validatedRequest['reference_no'],
                remarks: $validatedRequest['remarks'],
                items: collect($validatedRequest['items'])
                    ->map(fn (array $item, int $index) => new JournalEntryItemDTO(
                        sequence: $index + 1,
                        chartOfAccountId: (int) $item['chart_of_account_id'],
                        debit: max((float) ($item['debit'] ?? 0), 0),
                        credit: max((float) ($item['credit'] ?? 0), 0),
                        remarks: $item['remarks'] ?? null,
                    ))
                    ->all(),
            );
            $result = $this->journalEntryActions->create($dto);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(JournalEntry $journalEntry, JournalEntryUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueCode = $this->journalEntryActions->isUniqueCode(
                    $journalEntry->company_id,
                    $validatedRequest['code'],
                    $journalEntry->id,
                );
                if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            DB::beginTransaction();

            $result = $this->journalEntryActions->update($journalEntry, new JournalEntryUpdateDTO(
                branchId: $validatedRequest['branch_id'],
                code: $validatedRequest['code'],
                date: $validatedRequest['date'],
                journalType: $validatedRequest['journal_type'],
                referenceNo: $validatedRequest['reference_no'],
                remarks: $validatedRequest['remarks'],
                items: collect($validatedRequest['items'])
                    ->map(fn (array $item, int $index) => new JournalEntryItemDTO(
                        sequence: $index + 1,
                        chartOfAccountId: (int) $item['chart_of_account_id'],
                        debit: max((float) ($item['debit'] ?? 0), 0),
                        credit: max((float) ($item['credit'] ?? 0), 0),
                        remarks: $item['remarks'] ?? null,
                    ))
                    ->all(),
            ));
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(JournalEntry $journalEntry)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $journalEntry);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->journalEntryActions->delete($journalEntry);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
