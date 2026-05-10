<?php

namespace App\Models;

use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalEntryLine extends Model
{
    use HasFactory;
    use ScopeableByCompany;

    protected $fillable = [
        'company_id',
        'journal_entry_id',
        'chart_of_account_id',
        'sequence',
        'debit',
        'credit',
        'remarks',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'debit' => 'decimal:8',
        'credit' => 'decimal:8',
    ];

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class);
    }
}
