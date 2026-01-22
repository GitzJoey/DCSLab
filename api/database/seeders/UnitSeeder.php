<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(?int $unitsPerCompany = null, ?int $companyId = null)
    {
        $unitsPerCompany = $unitsPerCompany ?? 5;

        $companies = $companyId ? Company::where('id', $companyId)->get() : Company::all();

        $requiredUnits = ['PCS', 'GRAM', 'METER', 'PACK', 'LSN', 'KRT', 'BKS', 'SLOP'];

        foreach ($companies as $company) {
            foreach ($requiredUnits as $unitName) {
                $exists = Unit::where('company_id', $company->id)->where('name', $unitName)->exists();
                if (! $exists) {
                    Unit::factory()
                        ->for($company)
                        ->create([
                            'name' => $unitName,
                            'code' => $unitName,
                        ]);
                }
            }

            $currentCount = Unit::where('company_id', $company->id)->count();
            if ($currentCount < $unitsPerCompany) {
                Unit::factory()
                    ->count($unitsPerCompany - $currentCount)
                    ->for($company)
                    ->create();
            }
        }
    }
}
