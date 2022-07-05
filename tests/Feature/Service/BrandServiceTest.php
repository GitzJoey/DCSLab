<?php

namespace Tests\Feature\Service;

use Exception;
use App\Models\User;
use App\Models\Brand;
use App\Models\Company;
use Tests\ServiceTestCase;
use App\Services\BrandService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Contracts\Pagination\Paginator;

class BrandServiceTest extends ServiceTestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->brandService = app(BrandService::class);
    }

    #region create
    // ngetes brand service manggil create mengharapkan terekam di database
    public function test_brand_service_call_create_expect_db_has_record()
    {
        // ngegantiin acting as
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();
        
        // ngambil data dari Brand Factory trs di tambahin company id trus dimasukin ke brandArr
        $brandArr = Brand::factory()->make([
            'company_id' => $user->companies->first()->id
        ])->toArray();
        
        // hasilnya brand service bikin brandArr
        $result = $this->brandService->create($brandArr);

        $this->assertDatabaseHas('brands', [
            'id' => $result->id,
            'company_id' => $brandArr['company_id'],
            'code' => $brandArr['code'],
            'name' => $brandArr['name'],
        ]);
    }

    // ngetest brand service create dengan parameter kosong harapannya kesalahan(error)
    public function test_brand_service_call_create_with_empty_array_parameters_expect_exception()
    {
        // mengharapkan pengecualian
        $this->expectException(Exception::class);
        // bikin brand service dengan array kosong [] nanti nge thrown exception
        $this->brandService->create([]);
    }

    #endregion

    #region list
    // ngetes brand service manggil list dengan paginate true mengharapkan hasilnya adalah objek paginator
    public function test_brand_service_call_list_with_paginate_true_expect_paginator_object()
    {
        // gantinya acting as
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                            ->has(Brand::factory()->count(20), 'brands'), 'companies')
                    ->create();
        // manggil list di brand service
        $result = $this->brandService->list(
            companyId: $user->companies->first()->id,
            search: '',
            paginate: true,
            page: 1,
            perPage: 10
        );
        // memastikan resultnya itu berbentuk paginator
        $this->assertInstanceOf(Paginator::class, $result);
    }

    // ngetes brand service manggil list dengan paginate false yang mengharapkan objek collection
    public function test_brand_service_call_list_with_paginate_false_expect_collection_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                            ->has(Brand::factory()->count(20), 'brands'), 'companies')
                    ->create();
        $result = $this->brandService->list(
            companyId: $user->companies->first()->id,
            search: '',
            // hasil paginate dibikin false
            paginate: false,
            page: 1,
            perPage: 10
        );

        // memastikan resultnya itu berbentuk collection
        $this->assertInstanceOf(Collection::class, $result);
    }

    // ngetes brand service manggil list dengan company yang ga ada mengharapkan collection kosong
    public function test_brand_service_call_list_with_nonexistance_companyId_expect_empty_collection()
    {
        // max id = model company nyari max id dari company ditambah 1. Contohnya company id ada 1-19 trs di ambil yang 19 di tambah 1 jadi 20
        $maxId = Company::max('id') + 1;
        // list yang company id hasilnya ngambil dari maxId yang udah di tambah 1 search kosong, paginate false
        $result = $this->brandService->list(companyId: $maxId, search: '', paginate: false);

        // memastikan resultnya itu berbentuk Collection
        $this->assertInstanceOf(Collection::class, $result);
        // memastikan resultnya kosong
        $this->assertEmpty($result);
    }

    //  ngetes brand service manggil list dengan mengharapkan hasil yang sudah terfilter
    public function test_brand_service_call_list_with_search_parameter_expect_filtered_results()
    {
        // ngegantiin acting as
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        // companyId = ambil id company pertama dari user yg lagi kita test
        $companyId = $user->companies->first()->id;
        
        // factory brand bikin 25 data, ditambahin kata testing di blkgnya
        Brand::factory()->insertStringInName(' testing')->count(25)->create([
            'company_id' => $companyId
        ]);

        // bikin data lagi 10 yang namanya ga di ganti
        Brand::factory()->count(10)->create([
            'company_id' => $companyId,
        ]);

        $result = $this->brandService->list(
            companyId: $companyId,
            // search nya nyari kata testing dari data yang udah di bikin
            search: 'testing',
            paginate: true,
            page: 1,
            perPage: 10
        );

        // memastikan resultnya itu berbentuk paginator
        $this->assertInstanceOf(Paginator::class, $result);
        // benar ketika jumlah dari result 25   
        $this->assertTrue($result->total() == 25);
    }

    // ngetest brand service manggil list dengan parameter page negatif mengarapkan ada hasilnya
    public function test_brand_service_call_list_with_page_parameter_negative_expect_results()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;
        
        // factory brand bikin 25 data
        Brand::factory()->count(25)->create([
            'company_id' => $companyId,
        ]);

        $result = $this->brandService->list(
            companyId: $companyId, 
            search: '',
            paginate: true,
            // page nya dibikin jadi -1
            page: -1,
            perPage: 10
        );

        // memastikan resultnya itu berbentuk paginator
        $this->assertInstanceOf(Paginator::class, $result);
        // menghasilkan benar apabila jumlah resultnya > 25  
        $this->assertTrue($result->total() == 25);
    }

    // ngetest brand service manggil list dengan parameter perpage negatif mengarapkan ada hasilnya
    public function test_brand_service_call_list_with_perpage_parameter_negative_expect_results()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;
        
        // factory brand bikin 25 data
        Brand::factory()->count(25)->create([
            'company_id' => $companyId,
        ]);

        $result = $this->brandService->list(
            companyId: $companyId, 
            search: '',
            paginate: true,
            page: 1,
            // perPagenya dibikin jadi -10
            perPage: -10
        );

        // memastikan resultnya itu berbentuk paginator
        $this->assertInstanceOf(Paginator::class, $result);
        // menghasilkan benar apabila jumlah resultnya > 1
        $this->assertTrue($result->total() > 1);
    }

    #endregion

    #region read

    // test brand service manggil real mengharapkan returnnya object
    public function test_brand_service_call_read_expect_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                        ->has(Brand::factory()->count(20), 'brands'), 'companies')
                    ->create();
        // ngambil satu random brand dari company milik user yg lagi ditest
        $brand = $user->companies->first()->brands()->inRandomOrder()->first();

        // result = hasil dari read dengan nenggunakan brand yg tadi kita cari
        $result = $this->brandService->read($brand);

        // memastikan resultnya itu berbentuk modelnya brand
        $this->assertInstanceOf(Brand::class, $result);
    }

    #endregion

    #region update

    // test brand service manggil update mengaharapkan datanya ter update
    public function test_brand_service_call_update_expect_db_updated()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                        ->has(Brand::factory(), 'brands'), 'companies')
                    ->create();
        // brands ngambil dari companies yang ada di $user
        $brand = $user->companies->first()->brands->first();
        // factory brand dimasukin ke $brandArr
        $brandArr = Brand::factory()->make()->toArray();

        // hasilnya brand service update $brand sama $brandArr dijadiin array
        $result = $this->brandService->update($brand, $brandArr);
        
        $this->assertInstanceOf(Brand::class, $result);
        $this->assertDatabaseHas('brands', [
            'id' => $brand->id,
            'company_id' => $brand->company_id,
            'code' => $brandArr['code'],
            'name' => $brandArr['name'],
        ]);
    }

    // ngetes brand service manggil update dengan array kosong mengharapkan kesalahan (error)
    public function test_brand_service_call_update_with_empty_array_parameters_expect_exception()
    {
        // this->mengharapkanException(Exception) / mengharapkan error
        $this->expectException(Exception::class);

        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                        ->has(Brand::factory(), 'brands'), 'companies')
                    ->create();

        // brand ngambil dari brand pertama di company yg ada di $user
        $brand = $user->companies->first()->brands->first();
        // brandArr dibikin array kosong []
        $brandArr = [];
        
        // brand service ngeupdate
        $this->brandService->update($brand, $brandArr);
    }

    #endregion

    #region delete

    // test brand service manggil delete mengharapkan boolean
    public function test_brand_service_call_delete_expect_bool()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                        ->has(Brand::factory()->count(5), 'brands'), 'companies')
                    ->create();

        $brand = $user->companies->first()->brands->first();
        
        // $result = hasil dari manggil delete dengan parameter $brand yg tadi kita bikin
        $result = $this->brandService->delete($brand);
        
        // menghasilkan benar apabila hasilnya itu boolean
        $this->assertIsBool($result);
        // menghasilkan benar apabila hasilnya itu true
        $this->assertTrue($result);
        // pastikan brand yg tadi kita delete sudah ter soft delete
        $this->assertSoftDeleted('brands', [
            'id' => $brand->id
        ]);
    }

    #endregion

    #region others

    #endregion
}