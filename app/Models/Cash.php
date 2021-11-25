<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CustomerGroup;
use App\Models\Capital;
use App\Models\Company;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Config;

class Cash extends Model
{
    use HasFactory, LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'is_bank',
        'status'
    ];

    protected static $logAttributes = ['code', 'name', 'is_bank', 'status'];

    protected static $logOnlyDirty = true;

    protected $hidden = [
        'id',
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

    public function customergroups()
    {
        return $this->hasMany(CustomerGroup::class);
    } 

    public function capitals()
    {
        return $this->hasMany(Capital::class);
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
