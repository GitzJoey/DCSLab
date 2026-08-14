---
alwaysApply: false
description: 
---
# PROMPT RULE FACTORY (Laravel)

Saat membuat/mengedit `database/factories/*.php`:

- Wajib: Hanya field internal (tanpa foreign key).
- State helper: method `public` deskriptif, return `$this->state(...)` (mis. `setStatusActive()`), hanya override field relevan.

Field:
- `code`: uppercase + pola rapi (mis. `SUP-####` via lexify/numerify); jangan `word/uuid` mentah.
- `name`: realistis (Indonesia; “PT/CV …” bila cocok). Jika menambahkan suffix random (misal `Str::random`), wajib tambahkan spasi sebagai pemisah (contoh: `$name . ' ' . Str::random(3)`).
- `remarks`: kalimat pendek wajar (boleh `sentence`, bukan lorem noise).

Lokal Indonesia (jika ada): `city` kota Indo; `address` gaya “Jl.”; `phone/mobile` format `+62/08`; `tax_id` angka masuk akal (mis. `##.###.###.#-###.###`).

Relasi/FK:
- **STRICT FORBIDDEN**: Jangan pernah mendefinisikan `*_id` (Foreign Key) di dalam method `definition()`.
- **Why**: 
    1. Menghindari inkonsistensi data (misal: Child Model dibuatkan Company baru yang beda dengan Parent Model).
    2. Mencegah spam database (membuat ratusan Company baru yang tidak perlu).
    3. Memudahkan testing dengan skenario fleksibel.
- **Solution**: Set relasi di pemanggil (Seeder/Test) menggunakan `->for($model)` atau `->for(Model::factory())`.

Enum/boolean:
- Enum cast: pakai enum; boolean: `fake()->boolean()` atau default logis + state variasi.

Gaya:
- Konsisten; `fake()` / `fake('id_ID')`; 1 field per baris; tanpa logika bisnis berat.
