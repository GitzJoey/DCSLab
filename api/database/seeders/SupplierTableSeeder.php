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
            $company = Company::find($onlyThisCompanyId);

            if ($company) {
                $companyIds = (new Collection())->push($company->id);
            } else {
                $companyIds = Company::get()->pluck('id');
            }
        } else {
            $companyIds = Company::get()->pluck('id');
        }

        $instances = Container::getInstance();
        $setting = $instances->make(UserService::class)->createDefaultSetting();
        $roles = $instances->make(RoleService::class)->readBy('NAME', UserRoles::USER->value);

        $faker = \Faker\Factory::create('id_ID');

        foreach ($companyIds as $companyId) {
            $productIds = Product::whereCompanyId($companyId)->get()->pluck('id');

            for ($i = 0; $i < $supplierPerCompany; $i++) {
                $name = $faker->name;

                $user = User::factory()->make();
                $user->created_at = Carbon::now();
                $user->updated_at = Carbon::now();
                $user->save();

                $profile = Profile::factory()->setFirstName($name);
                $user->profile()->save($profile);

                $user->companies()->attach($companyId);

                $user->attachRoles([$roles->id]);
                $user->settings()->saveMany($setting);

                $supplier = Supplier::factory()->create([
                    'company_id' => $companyId,
                    'user_id' => $user->id,
                ]);

                $productCount = $productIds->count();
                $productCount = $productCount > 6 ? 6 : $productCount - 1;
                $supplierProductCount = (new RandomGenerator())->generateNumber(1, $productCount);
                $ProductIds = $productIds->shuffle()->take($supplierProductCount);

                $supplierProducts = [];

                foreach ($ProductIds as $productId) {
                    $supplierProduct = new SupplierProduct();
                    $supplierProduct->company_id = $companyId;
                    $supplierProduct->supplier_id = $supplier->id;
                    $supplierProduct->product_id = $productId;
                    $supplierProduct->main_product = (new RandomGenerator())->randomTrueOrFalse();

                    array_push($supplierProducts, $supplierProduct);
                }

                $supplier->supplierProducts()->saveMany($supplierProducts);
            }
        }
    }
}
