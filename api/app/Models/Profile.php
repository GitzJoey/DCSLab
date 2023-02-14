<?php

namespace App\Models;

use App\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $table = 'profiles';

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

    protected $hidden = [];

    protected $casts = [
        'status' => RecordStatus::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
