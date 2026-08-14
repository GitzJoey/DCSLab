---
alwaysApply: false
description: 
---
## PROMPT RULE SEEDER

Saat membuat atau mengubah seeder di project ini (`database/seeders/*.php`), gunakan standar berikut:

1. **Struktur dasar seeder**

- Namespace dan import:

  - Namespace selalu `Database\Seeders;`.
  - Import hanya model dan class yang benar‑benar dipakai (`use App\Models\X;`, `use Illuminate\Database\Seeder;`).
  - Class selalu `extends Seeder`.

- Signature method `run`:
  - Untuk seeder yang bekerja **per user**: 

    ```php
    public function run(?int $entitiesPerUser = null, ?int $userId = null)
    ```

    Contoh: `CompanySeeder::run(?int $companiesPerUser = null, ?int $userId = null)`.

  - Untuk seeder yang bekerja **per company**:

    ```php
    public function run(?int $entitiesPerCompany = null, ?int $companyId = null)
    ```

    Contoh: `BranchSeeder`, `WarehouseSeeder`, `InvestorSeeder`, `CustomerSeeder`, `CustomerGroupSeeder`, `ProductCategorySeeder`, `BrandSeeder`, `CashAccountSeeder`, `UnitSeeder`.

  - Di awal method tetapkan default jika parameter `null`:

    ```php
    $entitiesPerCompany = $entitiesPerCompany ?? 5; // atau angka default lain
    ```

2. **Pattern pemilihan parent (User / Company)**

- Untuk seeder per user (`CompanySeeder`):

  ```php
  $companiesPerUser = $companiesPerUser ?? 1;

  $users = $userId ? User::where('id', $userId)->get() : User::all();
  ```

- Untuk seeder per company:

  Boleh pakai salah satu pola berikut (pilih satu dan konsisten dalam seeder itu):

  - Ternary sederhana (dipakai banyak seeder):

    ```php
    $companies = $companyId ? Company::where('id', $companyId)->get() : Company::all();
    ```

  - Atau query builder eksplisit (seperti `CustomerSeeder`):

    ```php
    $query = Company::query();
    if ($companyId) {
        $query->where('id', $companyId);
    }
    $companies = $query->get();
    ```

- Selalu loop:

  ```php
  foreach ($companies as $company) {
      // isi per company
  }
  ```

3. **Cara memakai factory di seeder**

- Jangan set field detail di seeder; gunakan factory untuk mengisi data, dan seeder hanya:
  - Mengatur parent (`->for($company)`, `->for($branch)`).
  - Menghubungkan relasi khusus (`->hasAttached($user)`).
  - Menggunakan state (status, default, dsb).

- Contoh pola umum per company:

  ```php
  for ($i = 0; $i < $entitiesPerCompany; $i++) {
      ModelX::factory()
          ->for($company)
          ->create();
  }
  ```

- Untuk model yang butuh **company + branch** (Warehouse, CashAccount):
  - Ambil branch untuk company dulu:

    ```php
    $branch = Branch::where('company_id', $company->id)->inRandomOrder()->first();
    if (! $branch) {
        continue; // atau lewati company ini
    }
    ```

  - Baru gunakan:

    ```php
    ModelX::factory()
        ->for($company)
        ->for($branch)
        ->create();
    ```

4. **Pola khusus seeder “utama”**

- **CompanySeeder**:
  - Per user, selalu buat minimal satu company default & aktif:

    ```php
    Company::factory()
        ->hasAttached($user)
        ->setIsDefault()
        ->setStatusActive()
        ->create();
    ```

  - Jika `companiesPerUser > 1`, buat sisa company dengan status random active/inactive:

    ```php
    $remaining = max(0, $companiesPerUser - 1);

    for ($i = 0; $i < $remaining; $i++) {
        $company = Company::factory()->hasAttached($user);

        random_int(0, 1) ? $company->setStatusActive() : $company->setStatusInactive();

        $company->create();
    }
    ```

- **BranchSeeder**:
  - Per company, selalu buat satu branch **main & active**:

    ```php
    Branch::factory()
        ->for($company)
        ->setIsMainBranch()
        ->setStatusActive()
        ->create();
    ```

  - Branch sisanya: status random active/inactive, tapi tetap `for($company)`:

    ```php
    $remaining = max(0, $branchesPerCompany - 1);

    for ($i = 0; $i < $remaining; $i++) {
        $branch = Branch::factory()->for($company);

        random_int(0, 1) ? $branch->setStatusActive() : $branch->setStatusInactive();

        $branch->create();
    }
    ```

- **WarehouseSeeder**:
  - Per company, pilih satu branch random:

    ```php
    $branch = Branch::where('company_id', $company->id)->inRandomOrder()->first();
    ```

  - Untuk tiap warehouse:
    - `->for($company)`
    - `->for($branch)`
    - Status random active/inactive via state:

    ```php
    $warehouse = Warehouse::factory()
        ->for($company)
        ->for($branch);

    random_int(0, 1) ? $warehouse->setStatusActive() : $warehouse->setStatusInactive();

    $warehouse->create();
    ```

- **CashAccountSeeder**:
  - Mirip WarehouseSeeder, tapi untuk CashAccount:
    - Param `run(?int $cashAccountsPerCompany = null, ?int $companyId = null, ?int $branchId = null)`.
    - Ambil branch dengan filter company, dan opsional `branchId`:

      ```php
      $branchQuery = Branch::where('company_id', $company->id);

      if ($branchId) {
          $branchQuery->where('id', $branchId);
      }
      $branch = $branchQuery->inRandomOrder()->first();

      if (! $branch) {
          continue;
      }
      ```

    - Loop buat CashAccount dengan `->for($company)->for($branch)`.

- **Seeder “simple master per company”** (`InvestorSeeder`, `CustomerGroupSeeder`, `CustomerSeeder`, `ProductCategorySeeder`, `BrandSeeder`):
  - Pola standar:

    ```php
    $entitiesPerCompany = $entitiesPerCompany ?? 5;

    $companies = $companyId ? Company::where('id', $companyId)->get() : Company::all();

    foreach ($companies as $company) {
        for ($i = 0; $i < $entitiesPerCompany; $i++) {
            ModelX::factory()
                ->for($company)
                ->create();
        }
    }
    ```

- **UnitSeeder (master dengan “required set”)**:
  - Jika ada daftar unit wajib, gunakan pola:
    - Daftar array `requiredUnits`.
    - Untuk setiap nama, cek dulu apakah sudah ada:

      ```php
      $exists = Unit::where('company_id', $company->id)
          ->where('name', $unitName)
          ->exists();
      if (! $exists) {
          Unit::factory()
              ->for($company)
              ->create([
                  'name' => $unitName,
                  'code' => $unitName,
              ]);
      }
      ```

    - Setelah itu hitung total dan top‑up sampai `unitsPerCompany` tercapai:

      ```php
      $currentCount = Unit::where('company_id', $company->id)->count();

      if ($currentCount < $unitsPerCompany) {
          Unit::factory()
              ->count($unitsPerCompany - $currentCount)
              ->for($company)
              ->create();
      }
      ```

5. **Pemanggilan seeder (di AppSeed / AppInstall)**

- Saat memanggil seeder dari command lain, gunakan **named arguments** seperti di `AppSeed` / `AppInstall`:

  ```php
  (new CompanySeeder())->run(companiesPerUser: 1, userId: null);
  (new BranchSeeder())->run(branchesPerCompany: 5, companyId: null);
  (new WarehouseSeeder())->run(warehousesPerCompany: 5, companyId: null);
  (new InvestorSeeder())->run(investorsPerCompany: 5, companyId: null);
  (new CashAccountSeeder())->run(cashAccountsPerCompany: 5, companyId: null);
  // dst.
  ```

- Jangan lupa pertahankan urutan seeding yang logis:
  - `User` → `Company` → `Branch` → `Warehouse` → master lainnya (ProductCategory, Brand, Unit, CustomerGroup, Customer, Investor, CashAccount, dll).

6. **Gaya coding di seeder**

- Tidak perlu komentar ekstra di dalam seeder kecuali benar‑benar diperlukan.
- Gunakan `random_int(0, 1)` untuk variasi sederhana status, bukan logic rumit.
- Hindari query berat di dalam loop kecil jika bisa disederhanakan dengan satu query di awal (seperti pattern di `UnitSeeder`).