<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

use App\Models\Investor;
use App\Models\CapitalGroup;
use App\Models\Cash;
use App\Models\Company;

use Spatie\Activitylog\Traits\LogsActivity;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Config;

class Capital extends Model
{
    use HasFactory, LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'investor_id',
        'group_id',
        'cash_id',
        'ref_number',
        'date',
        'capital_status',
        'amount',
        'remarks',
    ];

    protected static $logAttributes = [
        'investor_id',
        'group_id',
        'cash_id',
        'ref_number',
        'date',
        'capital_status',
        'amount',
        'remarks',
    ];

    protected static $logOnlyDirty = true;

    protected $hidden = [
        'id',
        'investor_id',
        'group_id',
        'cash_id',
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $appends = ['hId'];

    public function getHIdAttribute() : string
    {
        return HashIds::encode($this->attributes['id']);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function investor()
    {
        return $this->belongsTo(Investor::class);
    }

    public function group()
    {
        return $this->belongsTo(CapitalGroup::class);
    }

    public function cash()
    {
        return $this->belongsTo(Cash::class);
    }

    public function scopeBySelectedCompany($query, $overrideCompanyId = '')
    {
        return $query->where('company_id', '=', empty($overrideCompanyId) ? Hashids::decode(session(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY')))[0]:$overrideCompanyId);
    }

    public function scopeStatusActive($query, $overrideStatus = '')
    {
        return $query->where('status', '=', empty($overrideStatus) ? 1:$overrideStatus);
    }
}