<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomeImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'income_id',
        'path',
        'hash',
        'is_main',
    ];

    protected function casts(): array
    {
        return [
            'is_main' => 'boolean',
        ];
    }

    public function income()
    {
        return $this->belongsTo(Income::class)->withTrashed();
    }
}
