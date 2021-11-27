<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CustomerGroup;
use App\Models\Company;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Config;

class Customer extends Model
{
    use HasFactory, LogsActivity;
    use SoftDeletes;
    
    protected $fillable = [
        'code',
        'name',
        'customer_group_id',
        'sales_territory',
        'use_limit_outstanding_notes',
        'limit_outstanding_notes',
        'use_limit_payable_nominal',
        'limit_payable_nominal',
        'use_limit_age_notes',
        'limit_age_notes',
        'term',
        'address',
        'city',
        'contact',
        'tax_id',
        'remarks',
        'status',
    ];

    protected static $logAttributes = [
        'code',
        'name',
        'customer_group_id',
        'sales_territory',
        'use_limit_outstanding_notes',
        'limit_outstanding_notes',
        'use_limit_payable_nominal',
        'limit_payable_nominal',
        'use_limit_age_notes',
        'limit_age_notes',
        'term',
        'address',
        'city',
        'contact',
        'tax_id',
        'remarks',
        'status'
    ];

    protected static $logOnlyDirty = true;

    protected $hidden = [
        'id',
        'customer_group_id',
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

    public function customer_group()
    {
        return $this->belongsTo(CustomerGroup::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
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