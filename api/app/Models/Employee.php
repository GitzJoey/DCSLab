<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'company_id',
        'code',
        'join_date',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function employeeAccesses()
    {
        return $this->hasMany(EmployeeAccess::class);
    }
}
