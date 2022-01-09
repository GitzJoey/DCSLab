<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cash;
use App\Models\Customer;
use App\Models\Company;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Config;

class CustomerGroup extends Model
{
    use HasFactory, LogsActivity;
    use SoftDeletes;
    
    protected $fillable = [
        'code',
        'name',
        'is_member_card',
        'max_open_invoice',
        'max_outstanding_invoice',
        'max_invoice_age',
        'payment_term',
        'selling_point',
        'selling_point_multiple',
        'sell_at_cost',
        'global_markup_percent',
        'global_markup_nominal',
        'global_discount_percent',
        'global_discount_nominal',
        'is_rounding',
        'round_on',
        'round_digit',
        'remarks',
        'cash_id'
    ];

    protected static $logAttributes = [
        'code',
        'name',
        'is_member_card',
        'max_open_invoice',
        'max_outstanding_invoice',
        'max_invoice_age',
        'payment_term',
        'selling_point',
        'selling_point_multiple',
        'sell_at_cost',
        'global_markup_percent',
        'global_markup_nominal',
        'global_discount_percent',
        'global_discount_nominal',
        'is_rounding',
        'round_on',
        'round_digit',
        'remarks',
        'cash_id'
    ];

    protected static $logOnlyDirty = true;

    protected $hidden = [
        'id',
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

    public function cash()
    {
        return $this->belongsTo(Cash::class);
    }

    public function customerGroup()
    {
        return $this->hasMany(Customer::class);
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
