<?php

namespace App\Models;

use App\Enums\AccountType;
use Illuminate\Support\Str;
use App\Traits\ScopeableByCompany;
use Spatie\Activitylog\LogOptions;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChartOfAccount extends Model
{
    use HasFactory, LogsActivity;
    use SoftDeletes;
    use ScopeableByCompany;

    protected $fillable = [
        'company_id',
        'parent_id',
        'code',
        'name',
        'account_type',
        'remarks',
    ];

    protected static $logAttributes = [
        'company_id',
        'parent_id',
        'code',
        'name',
        'account_type',
        'remarks',
    ];

    protected static $logOnlyDirty = true;

    protected $hidden = [];

    protected $casts = [
        'account_type' => AccountType::class,
    ];

    public function hId(): Attribute
    {
        return Attribute::make(
            get: fn () => HashIds::encode($this->attributes['id'])
        );
    }

    public function hParentId(): Attribute
    {
        return Attribute::make(
            get: fn () => HashIds::encode($this->attributes['parent_id'])
        );
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function parentAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    public function childAccounts()
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id', 'id');
    }  

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Str::uuid();

            $user = Auth::check();
            if ($user) {
                $model->created_by = Auth::id();
                $model->updated_by = Auth::id();
            }
        });

        static::updating(function ($model) {
            $user = Auth::check();
            if ($user) {
                $model->updated_by = Auth::id();
            }
        });

        static::deleting(function ($model) {
            $user = Auth::check();
            if ($user) {
                $model->deleted_by = Auth::id();
                $model->save();
            }
        });
    }
}
