<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class WarehouseTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($warehousePerCompanies = 3, $onlyThisCompanyId = 0)
    {
        if ($onlyThisCompanyId != 0) {
            $c = Company::find($onlyThisCompanyId);

            if ($c) {
                $companies = (new Collection())->push($c->id);
            } else {
                $companies = Company::get()->pluck('id');
            }
        } else {
            $companies = Company::get()->pluck('id');
        }

        foreach ($companies as $c) {
            for ($i = 0; $i < $warehousePerCompanies; $i++) {
                $branchId = Branch::whereRelation('company', 'id', '=', $c)->inRandomOrder()->first()->id;
                Warehouse::factory()->create([
                    'company_id' => $c,
                    'branch_id' => $branchId,
                ]);
            }
        }
    }
}
