<?php

namespace App\Models;

use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerAddress extends Model
{
    use BootableModel;
    use HasFactory;
    use SoftDeletes;

    protected $table = 'customer_addresses';

    protected $fillable = [
        'company_id',
        'customer_id',
        'address',
        'city',
        'contact',
        'is_main',
        'remarks',
    ];

    protected $casts = [
        'is_main' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
