<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class Profile extends Model
{
    use HasFactory;

    protected $table="profiles";

    protected $fillable = [
        'first_name',
        'last_name',
        'ic_number',
        'img_path',
    ];

    protected $hidden = [
        'id',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function hId() {
        return HashIds::encode($this->attributes['id']);
    }
}
