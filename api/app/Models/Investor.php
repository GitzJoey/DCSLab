<?php

namespace App\Models;

use App\Enums\ChartOfAccountSystemKeyEnum;
use App\Traits\BootableModel;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Investor extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function chartOfAccounts()
    {
        return $this->morphMany(ChartOfAccount::class, 'source');
    }

    public function openingCapitalChartOfAccount()
    {
        return $this->morphOne(ChartOfAccount::class, 'source')
            ->whereHas('parent', fn ($query) => $query->where('system_key', ChartOfAccountSystemKeyEnum::EQUITY_CAPITAL_OPENING_CAPITAL));
    }

    public function additionalCapitalChartOfAccount()
    {
        return $this->morphOne(ChartOfAccount::class, 'source')
            ->whereHas('parent', fn ($query) => $query->where('system_key', ChartOfAccountSystemKeyEnum::EQUITY_CAPITAL_ADDITIONAL_CAPITAL));
    }

    public function drawingChartOfAccount()
    {
        return $this->morphOne(ChartOfAccount::class, 'source')
            ->whereHas('parent', fn ($query) => $query->where('system_key', ChartOfAccountSystemKeyEnum::EQUITY_CAPITAL_DRAWING));
    }
}
