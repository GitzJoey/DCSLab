<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Company;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Vinkla\Hashids\Facades\Hashids;

class Branch extends Model
{
    use HasFactory, LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'address',
        'city',
        'contact',
        'remarks',
        'status'
    ];

    protected static $logAttributes = ['company_id', 'code', 'name', 'address', 'city', 'contact', 'remarks', 'status'];

    protected static $logOnlyDirty = true;


    protected $hidden = [
        'id',
        'company_id',
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
}