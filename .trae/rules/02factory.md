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
- `name`: realistis (Indonesia; “PT/CV …” bila cocok).
- `remarks`: kalimat pendek wajar (boleh `sentence`, bukan lorem noise).

Lokal Indonesia (jika ada): `city` kota Indo; `address` gaya “Jl.”; `phone/mobile` format `+62/08`; `tax_id` angka masuk akal (mis. `##.###.###.#-###.###`).

Relasi/FK:
- Jangan isi `*_id` di `definition()`. Set di pemanggil via `->for($model)` / `->for(Model::factory())` / state khusus.

Enum/boolean:
- Enum cast: pakai enum; boolean: `fake()->boolean()` atau default logis + state variasi.

Gaya:
- Konsisten; `fake()` / `fake('id_ID')`; 1 field per baris; tanpa logika bisnis berat.