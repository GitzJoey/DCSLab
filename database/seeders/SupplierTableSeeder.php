<?php

namespace Database\Seeders;

use App\Actions\RandomGenerator;
use App\Enums\UserRoles;
use App\Models\Company;
use App\Models\Product;
use App\Models\Profile;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Models\User;
use App\Services\RoleService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;

class SupplierTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($supplierPerCompany = 10, $onlyThisCompanyId = 0)
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

        $instances = Container::getInstance();
        $setting = $instances->make(UserService::class)->createDefaultSetting();
        $roles = $instances->make(RoleService::class)->readBy('NAME', UserRoles::USER->value);

        $faker = \Faker\Factory::create('id_ID');

        foreach ($companies as $c) {
            $products = Product::whereCompanyId($c)->get()->pluck('id');

            for ($i = 0; $i < $supplierPerCompany; $i++) {
                $name = $faker->name;
                $usr = User::factory()->make();

                $usr->created_at = Carbon::now();
                $usr->updated_at = Carbon::now();

                $usr->save();

                $profile = Profile::factory()->setFirstName($name);
                $usr->profile()->save($profile);

                $usr->companies()->attach($c);

                $usr->attachRoles([$roles->id]);
                $usr->settings()->saveMany($setting);

                $supplier = Supplier::factory()->create([
                    'company_id' => $c,
                    'user_id' => $usr->id,
                ]);

                $some_prods = $products->shuffle()
                                ->take((new RandomGenerator())
                                            ->generateNumber(1, $products->count() > 6 ? 6 : $products->count() - 1));

                $suppProd = [];

                foreach ($some_prods as $p) {
                    $sp = new SupplierProduct();
                    $sp->company_id = $c;
                    $sp->supplier_id = $supplier->id;
                    $sp->product_id = $p;
                    $sp->main_product = (new RandomGenerator())->randomTrueOrFalse();

                    array_push($suppProd, $sp);
                }

                $supplier->supplierProducts()->saveMany($suppProd);
            }
        }
    }
}
