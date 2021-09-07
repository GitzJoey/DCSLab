<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CustomerGroup;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Auth;

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
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $user = Auth::user();
            if ($user) {
                $model->created_by = $user->id;
                $model->updated_by = $user->id;
            }
        });

        static::updating(function ($model) {
            $user = Auth::user();
            if ($user) {
                $model->updated_by = $user->id;
            }
        });

        static::deleting(function ($model) {
            $user = Auth::user();
            if ($user) {
                $model->deleted_by = $user->id;
                $model->save();
            }
        });
    }
}
