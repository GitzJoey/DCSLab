<?php

namespace App\Models;

use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Company;
use App\Models\User;
use App\Models\EmployeeAccess;

class Employee extends Model
{
    use HasFactory;
    use BootableModel;

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
