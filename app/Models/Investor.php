<?php

namespace App\Models;

use App\Models\Capital;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Vinkla\Hashids\Facades\Hashids;

class Investor extends Model
{
    use HasFactory, LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'contact',
        'address',
        'city',
        'tax_number',
        'remarks',
        'status'
    ];

    protected static $logAttributes = ['code', 'name', 'contact', 'address', 'city', 'tax_number', 'remarks', 'status'];

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

    public function capitals()
    {
        // return $this->belongsTo(Capital::class);
        return $this->hasMany(Capital::class);
    }
}
