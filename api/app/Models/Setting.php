<?php

namespace App\Models;

use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use BootableModel;
    use HasFactory;

    protected $fillable = [
        'type',
        'key',
        'value',
    ];
}
