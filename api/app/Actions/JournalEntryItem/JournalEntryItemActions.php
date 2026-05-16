<?php

namespace App\Actions\JournalEntryItem;

use App\DTOs\ExecuteDTO;
use App\DTOs\JournalEntryItemDTO;
use App\Helpers\TimezoneHelper;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryItem;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;

class JournalEntryItemActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'journalEntry',
        'chartOfAccount',
    ];

    public function __construct()
    {
    }

    public function readAny(
        int $companyId,
        ?int $branchId,
        ?string $search,

        ?string $startDate,
        ?string $endDate,
        ?int $journalEntryId,
        ?int $chartOfAccountId,
        ?int $includeId,

        ?ExecuteDTO $execute
    ) {
        $query = JournalEntryItem::with(self::LIST_EAGER_LOADS)
            ->select('journal_entry_items.*')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_items.journal_entry_id');

        $query->whereCompanyId('journal_entry_items', $companyId);

        $query->where(function ($query) use (
            $branchId,
            $search,
            $startDate,
            $endDate,
            $journalEntryId,
            $chartOfAccountId,
            $includeId,
        ) {
            $query->where(function ($query) use (
                $branchId,
                $search,
                $startDate,
                $endDate,
                $journalEntryId,
                $chartOfAccountId,
            ) {
                if ($branchId) {
                    $query->where('journal_entries.branch_id', $branchId);
                }

                if ($startDate) {
                    $query->where('journal_entries.date', '>=', TimezoneHelper::convertToUTC($startDate));
                }

                if ($endDate) {
                    $query->where('journal_entries.date', '<=', TimezoneHelper::convertToUTC($endDate));
                }

                if ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('journal_entry_items.remarks', 'like', '%'.$search.'%')
                            ->orWhere('journal_entries.code', 'like', '%'.$search.'%')
                            ->orWhere('journal_entries.reference_no', 'like', '%'.$search.'%')
                            ->orWhereHas('chartOfAccount', function ($chartOfAccountQuery) use ($search) {
                                $chartOfAccountQuery->where('chart_of_accounts.code', 'like', '%'.$search.'%')
                                    ->orWhere('chart_of_accounts.name', 'like', '%'.$search.'%');
                            });
                    });
                }

                if ($journalEntryId) {
                    $query->where('journal_entry_items.journal_entry_id', $journalEntryId);
                }

                if ($chartOfAccountId) {
                    $query->where('journal_entry_items.chart_of_account_id', $chartOfAccountId);
                }
            });

            if ($includeId) {
                $query->orWhere('journal_entry_items.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(journal_entry_items.id, '.$includeId.') desc');
        }
        $query->orderBy('journal_entries.date', 'desc')
            ->orderBy('journal_entries.id', 'asc')
            ->orderBy('journal_entry_items.sequence', 'asc');

        if ($execute) {
            $timerStart = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    $startDate ?? '[null]',
                    $endDate ?? '[null]',
                    $journalEntryId ?? '[null]',
                    $chartOfAccountId ?? '[null]',
                    $includeId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_journal_entry_item_'.implode('_', $cacheParams);

                if ($execute->useCache) {
                    $cacheResult = $this->readFromCache($cacheKey);
                    if ($cacheResult !== Config::get('dcslab.ERROR_RETURN_VALUE')) {
                        return $cacheResult;
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

    public function read(JournalEntryItem $journalEntryItem): JournalEntryItem
    {
        return $journalEntryItem->load(self::LIST_EAGER_LOADS);
    }

    public function create(JournalEntry $journalEntry, JournalEntryItemDTO $data): JournalEntryItem
    {
        $timerStart = microtime(true);

        try {
            if ($data->sequence < 1) {
                throw new InvalidArgumentException('Journal entry item sequence must be at least 1.');
            }

            $chartOfAccountId = $data->chartOfAccountId;
            if (! $chartOfAccountId) {
                throw new InvalidArgumentException('Journal entry item chart of account must exist.');
            }

            $chartOfAccount = ChartOfAccount::query()
                ->where('id', $chartOfAccountId)
                ->where('company_id', $journalEntry->company_id)
                ->first();
            if (! $chartOfAccount) {
                throw new InvalidArgumentException('Journal entry item chart of account must exist.');
            }

            $journalEntryItem = new JournalEntryItem();
            $journalEntryItem->company_id = $journalEntry->company_id;
            $journalEntryItem->journal_entry_id = $journalEntry->id;
            $journalEntryItem->chart_of_account_id = $chartOfAccount->id;
            $journalEntryItem->sequence = $data->sequence;
            $journalEntryItem->debit = $data->debit;
            $journalEntryItem->credit = $data->credit;
            $journalEntryItem->remarks = $data->remarks;
            if (auth()->check()) {
                $journalEntryItem->created_by = auth()->id();
                $journalEntryItem->updated_by = auth()->id();
            }
            $journalEntryItem->save();

            $this->flushCache();

            return $journalEntryItem;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $executionTime = microtime(true) - $timerStart;
            $this->loggerPerformance(__METHOD__, $executionTime);
        }
    }

    public function update(JournalEntryItem $journalEntryItem, JournalEntryItemDTO $data): JournalEntryItem
    {
        $timerStart = microtime(true);

        try {
            if ($data->sequence < 1) {
                throw new InvalidArgumentException('Journal entry item sequence must be at least 1.');
            }

            $chartOfAccountId = $data->chartOfAccountId;
            if (! $chartOfAccountId) {
                throw new InvalidArgumentException('Journal entry item chart of account must exist.');
            }

            $chartOfAccount = ChartOfAccount::query()
                ->where('id', $chartOfAccountId)
                ->where('company_id', $journalEntryItem->company_id)
                ->first();
            if (! $chartOfAccount) {
                throw new InvalidArgumentException('Journal entry item chart of account must exist.');
            }

            $journalEntryItem->chart_of_account_id = $chartOfAccount->id;
            $journalEntryItem->sequence = $data->sequence;
            $journalEntryItem->debit = $data->debit;
            $journalEntryItem->credit = $data->credit;
            $journalEntryItem->remarks = $data->remarks;
            $journalEntryItem->save();

            $this->flushCache();

            return $journalEntryItem->refresh()->load(self::LIST_EAGER_LOADS);
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $executionTime = microtime(true) - $timerStart;
            $this->loggerPerformance(__METHOD__, $executionTime);
        }
    }

    public function delete(JournalEntryItem $journalEntryItem): bool
    {
        $timerStart = microtime(true);

        $retval = false;

        try {
            $retval = $journalEntryItem->delete();

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
}
