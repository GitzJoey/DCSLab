<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CustomerAddress extends Model
{
    use HasFactory;
    use SoftDeletes;

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

    protected static function booted(): void
    {
        $validateRelations = static function (self $customerAddress): void {
            if (is_null($customerAddress->customer_id)) {
                return;
            }

            $customer = Customer::find($customerAddress->customer_id);

            if (! $customer || (int) $customer->company_id !== (int) $customerAddress->company_id) {
                throw new InvalidArgumentException('Customer address customer must exist in the same company.');
            }
        };

        static::creating(function (self $customerAddress) use ($validateRelations) {
            $customerAddress->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $customerAddress->created_by = auth()->id();
                $customerAddress->updated_by = auth()->id();
            }

            $validateRelations($customerAddress);
        });

        static::updating(function (self $customerAddress) use ($validateRelations) {
            if (auth()->check()) {
                $customerAddress->updated_by = auth()->id();
            }

            $validateRelations($customerAddress);
        });

        static::deleting(function (self $customerAddress) {
            if (! auth()->check()) {
                return;
            }

            $customerAddress->deleted_by = auth()->id();
            $customerAddress->save();
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
