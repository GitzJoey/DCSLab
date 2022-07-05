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
        
        $brands = [
            'Apple','Samsung Group','Google','Microsoft','Verizon','General Electric','AT&T','Amazon','Walmart','IBM','Toyota','Coca Cola','China Mobile','T (Deutsche Telekom)','Wells Fargo','Vodafone','BMW','Shell','Volkswagen','HSBC','Bank of America','Mitsubishi Group','McDonalds','Citi','Home Depot','Mercedes-Benz','Walt Disney','Chase','Intel','ICBC','Honda','Nissan','American Express','Ford','Nike','Cisco','Oracle','Allianz','Siemens','Nestle','BNP Paribas','Santander','Orange','Mitsui Group','HP','Pepsi','UPS','Chevron','Axa','China Construction Bank','Hyundai Group','IKEA','CVS Caremark','Hitachi Group','Target','SoftBank','Agricultural Bank Of China','Tesco','MUFG','Bank of China','ExxonMobil','PWC','PetroChina','GDF Suez','China Unicom','NTT','Walgreens','Comcast','BT','Tata','Airbus','Total','Barclays','JP Morgan','China Telecom','Deloitte','Toshiba','ING (Group)','Sams Club','Deutsche Bank','Marlboro','FedEx','eBay','SAP','Fox','Generali Group','ALDI','Movistar','BP','Lowes','Sinopec','Sony','3M','China Life','LG Group','H&M','KPMG','DHL','Panasonic','RBC','Philips','Accenture','EY','Sberbank','Boeing','TD Bank','Woolworths Group','L\'Oreal','UBS','Bradesco','Renault','au','Costco','EDF','Goldman Sachs','Credit Suisse','Carrefour','Time Warner Cable','Starbucks','ItaÃº','UnitedHealth Group','Facebook','TimeWarner','E.ON','Ping An','Petronas','Bosch','China State Construction','Gazprom','Capital One','Visa','Honeywell','Sumitomo Group','Huawei','Subway','BBVA','NBC','Gillette','SK Group','Telstra','Dell','Macy\'s','TCS','DirecTV','Metlife','Canon','Sky','Morgan Stanley','TIM','Sainsbury','Kellogg\'s','ASDA','SMFG','Societe Generale','Adidas','Eni','Caterpillar','Johnson & Johnson','Scotiabank','Mizuho','Prudential (UK)','Danone','QQ','O2','Enel','Ericsson','Vinci','Nordea','Cartier','Zara','Centurylink','Swiss Re','Paypal','Standard Chartered','Peugeot','Bank of Montreal','Audi','Bell','Banco do Brasil','Bank of Communications','Zurich','Hermes','Rabobank','Sprint','BHP Billiton','UniCredit','Telenor','AIG','Xbox','Medtronic','Chevrolet','Morrison','Avon','BASF','Coles','Mastercard','Cadbury','Union Pacific','EMC','Gucci','Red Bull','Pantene','Warner Bros.','Jardines','Nivea','Kroger','Glencore Xstrata','Alibaba','CBS','Aetna','Statoil','Thomson Reuters','ANZ','Dove','Heinz','Nescafe','ESPN','Mclane Company','Louis Vuitton','Conocophillips','Groupe Casino','Munich Re','Iberdrola','Aeon','Purina','Prudential (US)','Pampers','Bridgestone','Petrobras','Nordstrom','Nomura','U.S. Bank','Emirates','Commonwealth Bank','Royal Mail','E.Leclerc','Shinhan Financial Group','Unilever','China Merchants Bank','MTN','Kia Motors','EE','Aegon','Suzuki','Polo Ralph Lauren','Yahoo!','WellPoint','ABB','Publix','Esso','CNOOC','Bayer','Fiat','Randstad','Mobil','Bud Light','Swisscom','Johnson Controls','Baidu','Daimler','Heineken','BBC','Rolex','CIBC','Marks & Spencer','Prada','National Australia Bank','STC','Johnnie Walker','DZ Bank','CNP Assurances','KT','Westpac','Fujitsu','Allstate','Sharp','Uniqlo','General Motors','Garnier','Dish Network','Arcelormittal','Caixa','Rogers','Delta','Virgin Media','MTV','Saint-Gobain','Michelin','British Gas','7-Eleven','Aviva','BNY Mellon','Estee Lauder','Playstation','Rio Tinto','Dai-Ichi Life','Metro','SK Telecom','SFR','Chow Tai Fook','RWE','Mazda','Chanel','Rolls-Royce','John Deere','Exxon','La Poste','Lukoil','Wrigley\'s','PNC','China Minsheng Bank','Safeway','Emerson Electric','Land Rover','Qualcomm','Alcatel-Lucent','GMC','Magnit','Antarchile','Telus','Citroen','3','Sprite','Budweiser','QVC','Burberry','MINI','Subaru','Coach','Auchan','Asahi','Lexus','Moutai','Lufthansa','Telcel','CPIC','Pfizer','Omega','Berkshire Hathaway','Schneider Electric','Continental','Kyocera','Lotte Group','LIC','Southern Company','Xerox','Enbridge','Bombardier','LancÃ´me','Olay','State Bank of India','Kohl\'s','Marubeni','Ferrari','Discovery','DBS','Express Script','Huggies','Whole Foods','Mountain Dew','Sherwin-Williams','Best Buy','TEPCO','TUI Travel','Winston','Holcim','Ergo','Harley-Davidson','Royal Bank of Scotland','Victoria\'s Secret','Lenovo','McKinsey','NatWest','Maersk','Lockheed Martin','PICC','National Grid','Lay\'s Potato Chips','SYSCO','Travelers','United','MAN','Airtel','Western Digital','Bank of America Merrill Lynch','Chunghwa','MCC','Activision Blizzard','Fluor','Nippon Steel','Duracell','Nec','Dollar General','AIA','Gas Natural','Capgemini','PTT','Lloyds','Claro','The Co-operative Group','AutoZone','Goodyear','Tiffany & Co.','KPN','Geico','Michael Kors','Endesa','MTS','Dongfeng','Commerzbank','J.C. Penney','Johnson\'s','KFC','Optus','Skol','Mapfre','Reliance','Volvo','Adobe','Media Markt & Saturn','KEPCO','Porsche','ZTE','Deutsche Post','KOGAS','Raytheon','Colgate','Beeline','Fujifilm Group','ADP','Ageas','Staples','BAE Systems','KBC','KB Financial Group','UPC','Roche','Etisalat','Otis','Isuzu','Natixis','Ecopetrol','Carmax','LAFARGE','Schlumberger','Mckesson','Novartis','Erste Bank','Rosneft','Arla','Christian Dior','SSE','Daiwa House Industry','Gatorade','ONGC','General Dynamics','21st Century Fox','Next','Industrial Bank','DNB','Halifax','BG','Wolseley','VTB','Singapore Airlines','Mercadona','Progressive','Veolia','United Technologies','Discover','Bed Bath & Beyond','ABN AMRO','Skanska','Toys R Us','NETFLIX','Megafon','Ace','Canadian National Railway','Svenska Handelsbanken','Indian Oil','State Street','EDP','Blackrock','Credit Agricole','Halliburton','CSX','Falabella','Safran','Procter & Gamble','Unicharm Corp','Fanta','Aflac','WeChat','GS Group','Eiffage','Sodexo','Kraft','Glaxosmithkline','China CITIC Bank','McCain'
        ];
        
        // factory brand bikin 10 data
        Brand::factory()->count(10)->create([
            'company_id' => $companyId,
            // name ngambil namanya acak dari $brands ditambah 'testing'
            'name' => $this->faker->randomElement($brands).' '.'testing'
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
        // benar ketika jumlah dari result 10   
        $this->assertTrue($result->total() == 10);
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
        // menghasilkan benar apabila jumlah resultnya > 1  
        $this->assertTrue($result->total() > 1);
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