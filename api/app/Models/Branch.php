<?php

namespace App\Models;

use App\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('branches')]
#[Fillable(['company_id', 'code', 'name', 'address', 'city', 'contact', 'is_main', 'remarks', 'status'])]
class Branch extends Model
{
    protected function casts(): array
    {
        return [
            'is_main' => 'boolean',
            'status' => RecordStatus::class,
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
