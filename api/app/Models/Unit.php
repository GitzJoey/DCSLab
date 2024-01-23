<?php

namespace App\Models;

use App\Enums\UnitCategory;
use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use BootableModel;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'description',
        'category',
    ];

    protected $casts = [
        'category' => UnitCategory::class,
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
