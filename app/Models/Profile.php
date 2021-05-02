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
        'company_name',
        'address',
        'city',
        'postal_code',
        'country',
        'status',
        'tax_id',
        'ic_number',
        'img_path',
        'remarks',
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
