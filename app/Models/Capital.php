<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

use App\Models\Investor;
use App\Models\CapitalGroup;
use App\Models\Cash;

use Spatie\Activitylog\Traits\LogsActivity;
use Vinkla\Hashids\Facades\Hashids;

class Capital extends Model
{
    use HasFactory, LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'investor',
        'capital_group',
        'cash_id',
        'ref_number',
        'date',
        'amount',
        'remarks',
    ];

    protected static $logAttributes = [
        'investor',
        'capital_group',
        'cash_id',
        'ref_number',
        'date',
        'amount',
        'remarks',
    ];

    protected static $logOnlyDirty = true;

    protected $hidden = [
        'id',
        'investor',
        'capital_group',
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

    public function investors()
    {
        return $this->hasMany(Investor::class);
    }

    public function capital_groups()
    {
        return $this->hasMany(CapitalGroup::class);
    }

    public function cashes()
    {
        return $this->hasMany(Cash::class);
    }
}
