<?php

namespace App\Models;

use App\Enums\JournalEntryTypeEnum;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class JournalEntry extends Model
{
    use HasFactory;
    use ScopeableByBranch;
    use ScopeableByCompany;

    protected $fillable = [
        'company_id',
        'branch_id',
        'code',
        'date',
        'journal_type',
        'source_type',
        'source_id',
        'reference_no',
        'total_debit',
        'total_credit',
        'remarks',
    ];

    protected $casts = [
        'date' => 'datetime',
        'journal_type' => JournalEntryTypeEnum::class,
        'total_debit' => 'decimal:8',
        'total_credit' => 'decimal:8',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $journalEntry) {
            $journalEntry->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $journalEntry->created_by = auth()->id();
                $journalEntry->updated_by = auth()->id();
            }
        });

        static::updating(function (self $journalEntry) {
            if (auth()->check()) {
                $journalEntry->updated_by = auth()->id();
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }

    public function items()
    {
        return $this->hasMany(JournalEntryItem::class)
            ->orderBy('sequence')
            ->orderBy('id');
    }
}
