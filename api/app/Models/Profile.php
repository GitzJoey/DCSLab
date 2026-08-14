<?php

namespace App\Models;

use App\Enums\RecordStatusEnum;
use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use BootableModel;
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'address',
        'city',
        'postal_code',
        'country',
        'status',
        'tax_id',
        'ic_num',
        'img_path',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'status' => RecordStatusEnum::class,
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
