<?php

namespace App\Models;

use App\Enums\RecordStatus;
use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory;
    use SoftDeletes;
    use BootableModel;

    protected $fillable = [
        'code',
        'name',
        'address',
        'default',
        'status',
    ];

    protected $casts = [
        'default' => 'boolean',
        'status' => RecordStatus::class,
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }
}
