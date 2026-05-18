<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use App\Enums\RecordStatus;
use App\Observers\ModelLifecycleObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('profiles')]
#[Fillable([
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
])]
#[ObservedBy(ModelLifecycleObserver::class)]
class Profile extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => RecordStatus::class,
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
