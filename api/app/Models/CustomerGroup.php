<?php

namespace App\Models;

use App\Models\Company;
use App\Models\Customer;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomerGroup extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'max_open_invoice',
        'max_outstanding_invoice',
        'max_invoice_age',
        'payment_term_type',
        'payment_term',
        'selling_point',
        'selling_point_multiple',
        'sell_at_cost',
        'price_markup_percent',
        'price_markup_nominal',
        'price_markdown_percent',
        'price_markdown_nominal',
        'round_on',
        'round_digit',
        'remarks',
    ];
    
    public function hId(): Attribute
    {
        return Attribute::make(
            get: fn () => HashIds::encode($this->attributes['id'])
        );
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}
