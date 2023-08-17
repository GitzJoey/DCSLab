<?php

namespace App\Models;

use App\Enums\RecordStatus;
use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory;
    use SoftDeletes;
    use BootableModel;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'address',
        'city',
        'contact',
        'is_main',
        'remarks',
        'status',
    ];

    protected $casts = [
        'is_main' => 'boolean',
        'status' => RecordStatus::class,
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
