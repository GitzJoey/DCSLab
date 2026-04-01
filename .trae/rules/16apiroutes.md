---
alwaysApply: false
description: Standarisasi penulisan routes/api.php backend
---
# Laravel API Route Standard

## Route Group Format

### Rule
Untuk penambahan endpoint baru di `api/routes/api.php`, gunakan format route modular per resource seperti berikut, bukan format nested legacy `get/post` yang dibungkus grup besar berdasarkan domain.

Format yang WAJIB dipakai:
- `Route::prefix('{resource}')`
- `->middleware('auth:sanctum')`
- subgroup GET dengan `throttle:100,1` dan name prefix `api.get.{resource}.`
- subgroup POST dengan `throttle:50,1` + `precognitive` dan name prefix `api.post.{resource}.`

Struktur endpoint di dalamnya:
- `GET read`
- `GET read/{resource:ulid}`
- `POST save`
- `POST edit/{resource:ulid}`
- `POST delete/{resource:ulid}`

### Why
- Lebih ringkas dan mudah dibaca dibanding nested group legacy
- Konsisten per resource sehingga lebih mudah dicari di `routes/api.php`
- Name route dan middleware menjadi seragam untuk semua modul baru
- Memisahkan jelas endpoint GET dan POST tanpa bergantung pada wrapper domain besar

### Example (Correct)
```php
Route::prefix('capital_opening')->middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:100,1')->name('api.get.capital_opening.')->group(function () {
        Route::get('read', [CapitalOpeningController::class, 'readAny'])->name('read_any');
        Route::get('read/{capital_opening:ulid}', [CapitalOpeningController::class, 'read'])->name('read');
    });

    Route::middleware(['throttle:50,1', 'precognitive'])->name('api.post.capital_opening.')->group(function () {
        Route::post('save', [CapitalOpeningController::class, 'store'])->name('save');
        Route::post('edit/{capital_opening:ulid}', [CapitalOpeningController::class, 'update'])->name('edit');
        Route::post('delete/{capital_opening:ulid}', [CapitalOpeningController::class, 'delete'])->name('delete');
    });
});
```

### Example (Avoid for New Modules)
```php
Route::group(['prefix' => 'get', 'middleware' => ['auth:sanctum', 'throttle:100,1'], 'as' => 'api.get'], function () {
    Route::group(['prefix' => 'capital', 'as' => '.capital'], function () {
        Route::group(['prefix' => 'capital_opening', 'as' => '.capital_opening'], function () {
            Route::get('read', [CapitalOpeningController::class, 'readAny'])->name('.read_any');
            Route::get('read/{capital_opening:ulid}', [CapitalOpeningController::class, 'read'])->name('.read');
        });
    });
});
```
