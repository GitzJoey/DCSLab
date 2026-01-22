---
alwaysApply: false
description: 
---
# Standarisasi API Test (Master Data)

Aturan ini berlaku untuk pembuatan dan pemeliharaan tes API pada modul Master Data (dan turunannya) yang terletak di `tests/Feature/API`.

## 1. Struktur File dan Namespace

Setiap modul memiliki direktori sendiri di dalam `tests/Feature/API/` dengan format nama `{Module}API`.
Tes dipecah berdasarkan aksi (CRUD) menjadi file terpisah:

- `Create`: `{Module}API/{Module}APICreateTest.php`
- `Read`: `{Module}API/{Module}APIReadTest.php`
- `Edit`: `{Module}API/{Module}APIEditTest.php`
- `Delete`: `{Module}API/{Module}APIDeleteTest.php`

**Namespace:** `Tests\Feature\API\{Module}API;`
**Inheritance:** Semua class test harus meng-extend `Tests\APITestCase`.

## 2. Penamaan Method Test

Format penamaan method harus konsisten:
`test_{module}_api_call_{action}_{condition}_expect_{result}`

Contoh:
- `test_branch_api_call_store_without_authorization_expect_unauthorized_message`
- `test_branch_api_call_update_expect_successful`
- `test_branch_api_call_delete_of_nonexistance_ulid_expect_not_found`

## 3. Standar Test Case

Setiap file test harus mencakup skenario berikut (jika relevan):

### Common Scenarios (Create, Edit, Delete, Read)
1.  **Unauthorized Access**: Memastikan user tanpa login mendapatkan respon 401.
    - Method: `..._without_authorization_expect_unauthorized_message`
    - Assert: `$api->assertUnauthorized()`
2.  **Forbidden Access**: Memastikan user login tanpa hak akses mendapatkan respon 403.
    - Method: `..._without_access_right_expect_forbidden_message`
    - Assert: `$api->assertForbidden()`

### Create & Edit Scenarios (`save`, `edit`)
1.  **Successful Operation**: Test flow normal dengan data valid.
    - Gunakan `Hashids::encode($id)` untuk referensi ID dalam payload.
    - Assert: `$api->assertSuccessful()` dan `$this->assertDatabaseHas(...)`.
2.  **XSS Protection**: Memastikan script tag di-strip atau di-encode.
    - `..._with_script_tags_in_payload_expect_stripped`
    - `..._with_script_tags_in_payload_expect_encoded` (header `X-Sanitizer-Mode: encode`)
3.  **Validation Errors**: Test field wajib kosong atau format salah.
    - Assert: `$api->assertJsonValidationErrors(['field_name'])`.
4.  **Unique Code Validation**:
    - `..._with_existing_code_in_same_company_expect_failed` (422 Unprocessable).
    - `..._with_existing_code_in_different_company_expect_successful` (Harus boleh duplikat beda company).
5.  **Auto Code**: Jika fitur mendukung `AUTO` generate code.
    - `..._with_auto_code_expect_successful`.

### Delete Scenarios (`delete`)
1.  **Successful Delete**:
    - Assert: `$api->assertSuccessful()` dan `$this->assertSoftDeleted(...)` (jika soft delete).
2.  **Not Found**: Menghapus resource dengan ULID random/tidak ada.
    - Assert: `$api->assertStatus(404)`.
3.  **Logic Constraints**: Menghapus data yang tidak boleh dihapus (misal: Main Branch).
    - Assert: `$api->assertUnprocessable()` atau status 422.

### Read Scenarios (`read`)
1.  **Successful Read**:
    - Assert: `$api->assertSuccessful()`.
    - Cek struktur JSON respon (pagination, data).
2.  **Pagination & Filtering**:
    - Pastikan parameter seperti `page`, `per_page`, `search`, `company_id` berfungsi.
    - Ikuti urutan parameter sesuai `rules/05controller.md` untuk request.

## 4. Setup dan Data Factory

- Gunakan `User::factory()` dengan role `DEVELOPER` untuk user yang memiliki akses penuh dalam test positif.
- Gunakan `setUp(): void` yang memanggil `parent::setUp()`.
- Hindari hardcode ID, gunakan factory relationship.

```php
$user = User::factory()
    ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
    ->has(Company::factory()->setStatusActive()->setIsDefault()->has(Branch::factory()))
    ->create();
```

## 5. Routing

Gunakan helper `route()` dengan nama route yang standar:
- Create: `api.post.{module}.save`
- Read: `api.get.{module}.read`
- Edit: `api.post.{module}.edit` (parameter ULID)
- Delete: `api.post.{module}.delete` (parameter ULID)

## 6. Contoh Implementasi (Template Singkat)

```php
public function test_module_api_call_store_expect_successful()
{
    // 1. Setup User & Data
    $user = User::factory()->...->create();
    $this->actingAs($user);

    // 2. Prepare Payload
    $company = $user->companies->first();
    $payload = Model::factory()->make([
        'company_id' => Hashids::encode($company->id),
    ])->toArray();

    // 3. Call API
    $api = $this->json('POST', route('api.post.module.save'), $payload);

    // 4. Assertions
    $api->assertSuccessful();
    $this->assertDatabaseHas('table_name', [
        'company_id' => $company->id,
        'name' => $payload['name'],
    ]);
}
```
