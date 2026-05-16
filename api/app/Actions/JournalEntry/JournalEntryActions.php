<?php

namespace App\Actions\JournalEntry;

use App\Actions\JournalEntryItem\JournalEntryItemActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\JournalEntryCreateDTO;
use App\DTOs\JournalEntryItemDTO;
use App\DTOs\JournalEntryUpdateDTO;
use App\Enums\JournalEntryTypeEnum;
use App\Helpers\TimezoneHelper;
use App\Models\JournalEntry;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class JournalEntryActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
    ];

    private const DETAIL_EAGER_LOADS = [
        'company',
        'branch',
        'items.chartOfAccount',
    ];

    public function __construct(
        private readonly JournalEntryItemActions $journalEntryItemActions,
    ) {
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,
        ?string $startDate,
        ?string $endDate,
        ?string $journalType,
        ?string $sourceType,
        ?int $sourceId,
        ?ExecuteDTO $execute,
    ) {
        $query = JournalEntry::with(self::LIST_EAGER_LOADS)
            ->select('journal_entries.*')
            ->whereCompanyId('journal_entries', $companyId);

        if ($branchId) {
            $query->where('journal_entries.branch_id', $branchId);
        }

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('journal_entries.code', 'like', '%'.$search.'%')
                    ->orWhere('journal_entries.reference_no', 'like', '%'.$search.'%')
                    ->orWhere('journal_entries.journal_type', 'like', '%'.$search.'%')
                    ->orWhere('journal_entries.source_type', 'like', '%'.$search.'%')
                    ->orWhere('journal_entries.remarks', 'like', '%'.$search.'%')
                    ->orWhereHas('items.chartOfAccount', function ($itemQuery) use ($search) {
                        $itemQuery->where('chart_of_accounts.code', 'like', '%'.$search.'%')
                            ->orWhere('chart_of_accounts.name', 'like', '%'.$search.'%');
                    });
            });
        }

        if ($startDate) {
            $query->where('journal_entries.date', '>=', TimezoneHelper::convertToUTC($startDate));
        }

        if ($endDate) {
            $query->where('journal_entries.date', '<=', TimezoneHelper::convertToUTC($endDate));
        }

        if ($journalType) {
            $query->where('journal_entries.journal_type', $journalType);
        }

        if ($sourceType) {
            $query->where('journal_entries.source_type', $sourceType);
        }

        if ($sourceId) {
            $query->where('journal_entries.source_id', $sourceId);
        }

        $query->orderBy('journal_entries.date', 'desc')
            ->orderBy('journal_entries.id', 'asc');

        if ($execute) {
            $timerStart = microtime(true);
            $recordsCount = 0;

            try {
                $cacheKey = 'readAny_journal_entry_'.implode('-', [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    $startDate ?? '[null]',
                    $endDate ?? '[null]',
                    $journalType ?? '[null]',
                    $sourceType ?? '[null]',
                    $sourceId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ]);

                if ($execute->useCache) {
                    $cacheData = $this->readFromCache($cacheKey);
                    if ($cacheData !== Config::get('dcslab.ERROR_RETURN_VALUE')) {
                        return $cacheData;
                    }
                }

                if ($execute->pagination) {
                    $result = $query->paginate(
                        perPage: $execute->pagination->perPage,
                        columns: ['*'],
                        pageName: 'page',
                        page: $execute->pagination->page,
                    );
                } else {
                    if ($execute->get?->limit) {
                        $query->limit($execute->get->limit);
                    }

                    $result = $query->get();
                }

                $recordsCount = $result->count();

                if ($execute->useCache) {
                    $this->saveToCache($cacheKey, $result);
                }

                return $result;
            } catch (Exception $e) {
                $this->loggerDebug(__METHOD__, $e);
                throw $e;
            } finally {
                $executionTime = microtime(true) - $timerStart;
                $this->loggerPerformance(__METHOD__, $executionTime, $recordsCount);
            }
        }

        return $query;
    }

    public function read(JournalEntry $journalEntry): JournalEntry
    {
        return $journalEntry->load(self::DETAIL_EAGER_LOADS);
    }

    public function create(JournalEntryCreateDTO $data): JournalEntry
    {
        $timerStart = microtime(true);

        try {
            $journalEntry = new JournalEntry();
            $journalEntry->company_id = $data->companyId;
            $journalEntry->branch_id = $data->branchId;
            $journalEntry->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $journalEntry->date = $this->resolveDate($data->date);
            $journalEntry->journal_type = (function () use ($data): ?string {
                if ($data->journalType) return $data->journalType;
                if ($data->sourceType && $data->sourceId) return JournalEntryTypeEnum::TRANSACTION->value;

                return null;
            })();
            $journalEntry->source_type = $data->sourceType;
            $journalEntry->source_id = $data->sourceId;
            $journalEntry->reference_no = $data->referenceNo;
            $journalEntry->total_debit = collect($data->items)->sum(fn (JournalEntryItemDTO $item) => $item->debit);
            $journalEntry->total_credit = collect($data->items)->sum(fn (JournalEntryItemDTO $item) => $item->credit);
            $journalEntry->remarks = $data->remarks;
            $journalEntry->save();

            foreach ($data->items as $item) {
                $this->journalEntryItemActions->create($journalEntry, $item);
            }
            $this->flushCache();

            return $journalEntry->refresh()->load(self::DETAIL_EAGER_LOADS);
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $executionTime = microtime(true) - $timerStart;
            $this->loggerPerformance(__METHOD__, $executionTime);
        }
    }

    public function update(JournalEntry $journalEntry, JournalEntryUpdateDTO $data): JournalEntry
    {
        $timerStart = microtime(true);

        try {
            $journalEntry->branch_id = $data->branchId;
            $journalEntry->code = $this->generateUniqueCode($journalEntry->company_id, $data->code, $journalEntry->id);
            $journalEntry->date = $this->resolveDate($data->date);
            $journalEntry->journal_type = (function () use ($data, $journalEntry): ?string {
                if ($data->journalType) return $data->journalType;
                if ($journalEntry->journal_type) return $journalEntry->journal_type->value;
                if ($journalEntry->source_type && $journalEntry->source_id) return JournalEntryTypeEnum::TRANSACTION->value;

                return null;
            })();
            $journalEntry->reference_no = $data->referenceNo;
            $journalEntry->total_debit = collect($data->items)->sum(fn (JournalEntryItemDTO $item) => $item->debit);
            $journalEntry->total_credit = collect($data->items)->sum(fn (JournalEntryItemDTO $item) => $item->credit);
            $journalEntry->remarks = $data->remarks;
            $journalEntry->save();

            foreach ($journalEntry->items as $journalEntryItem) {
                $this->journalEntryItemActions->delete($journalEntryItem);
            }
            foreach ($data->items as $item) {
                $this->journalEntryItemActions->create($journalEntry, $item);
            }
            $this->flushCache();

            return $journalEntry->refresh()->load(self::DETAIL_EAGER_LOADS);
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $executionTime = microtime(true) - $timerStart;
            $this->loggerPerformance(__METHOD__, $executionTime);
        }
    }

    public function delete(JournalEntry $journalEntry): bool
    {
        $timerStart = microtime(true);

        $retval = false;

        try {
            $retval = $journalEntry->delete();
            $this->flushCache();

            return $retval;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $executionTime = microtime(true) - $timerStart;
            $this->loggerPerformance(__METHOD__, $executionTime);
        }
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $query = JournalEntry::whereCompanyId('journal_entries', $companyId)
            ->where('code', '=', $code);

        if ($exceptId) {
            $query->where('journal_entries.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }

    public function generateUniqueCode(int $companyId, string $code, ?int $exceptId): string
    {
        if ($code != config('dcslab.KEYWORDS.AUTO')) {
            return $code;
        }

        $tryCount = 0;

        do {
            $count = JournalEntry::whereCompanyId('journal_entries', $companyId)->count() + 1 + $tryCount;
            $code = 'JRN'.str_pad($count, 4, '0', STR_PAD_LEFT);
            $tryCount++;
        } while (! $this->isUniqueCode($companyId, $code, $exceptId));

        return $code;
    }

    private function resolveDate(string $date): string
    {
        if ($date === config('dcslab.KEYWORDS.AUTO')) {
            return now('UTC')->format('Y-m-d H:i:s');
        }

        return TimezoneHelper::convertToUTC($date);
    }
}
