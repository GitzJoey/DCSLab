<?php

namespace App\Models;

use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory;
    use SoftDeletes;
    use BootableModel;

    protected $fillable = [
        'code',
        'name',
        'contact',
        'address',
        'city',
        'payment_term_type',
        'payment_term',
        'taxable_enterprise',
        'tax_id',
        'status',
        'remarks',
        'user_id',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function supplierProducts()
    {
        return $this->hasMany(SupplierProduct::class);
    }
}
