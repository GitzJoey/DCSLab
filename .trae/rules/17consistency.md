---
alwaysApply: false
description: Tekankan konsistensi implementasi dan larang improvisasi struktur ketika pola proyek sudah ada
---
# Aturan Konsistensi Implementasi

Aturan ini dibuat untuk memastikan agent tidak membuat keputusan sepihak yang menghasilkan implementasi inkonsisten dengan pola arsitektur proyek.

## 1. Prinsip Utama
- Konsistensi lebih penting daripada solusi cepat yang bersifat ad-hoc.
- Jika proyek sudah memiliki pola yang jelas, agent wajib mengikuti pola itu.
- Agent dilarang membuat struktur manual satu kali pakai hanya untuk menyelesaikan kebutuhan sesaat.

## 2. Larangan Improvisasi Sepihak
- Jangan membuat keputusan desain sendiri jika kebutuhan tersebut sebenarnya sudah memiliki pola umum di codebase.
- Jangan membuat representasi data manual di dalam resource, service, action, atau layer lain hanya karena class pendukungnya belum tersedia.
- Jika ada kebutuhan relasi atau nested object yang secara arsitektur seharusnya memiliki class tersendiri, maka agent wajib membuat class tersebut terlebih dahulu lalu menggunakannya secara konsisten.

## 3. Aturan Khusus Untuk Resource
- Jangan otomatis mengekspos semua relasi yang tersedia di model.
- Hanya tampilkan relasi di API Resource jika memang ada kebutuhan UI, kontrak API, atau pola existing yang mendukungnya.
- Relasi internal, relasi teknis, atau relasi sinkronisasi sistem tidak boleh ikut ditampilkan hanya karena relasinya ada.
- Jika sebuah model/relasi perlu ditampilkan di response API, utamakan penggunaan Resource class dedicated.
- Jika Resource untuk model tersebut belum ada, buat Resource baru terlebih dahulu.
- Jangan membuat array manual inline di dalam Resource induk untuk menggantikan Resource class yang seharusnya ada.
- Semua relasi tetap harus mengikuti pola `whenLoaded`, `mergeWhen`, dan Resource dedicated agar konsisten dengan seluruh API project.

## 3.1 Aturan Khusus Untuk Relasi Internal
- Relasi seperti transaction log, stock transaction, cash transaction, audit trail, atau relasi turunan sinkronisasi tidak boleh dianggap perlu tampil secara default.
- Sebelum mengekspos relasi seperti ini, agent wajib membandingkan dengan pola resource modul lain di folder `api/app/Http/Resources`.
- Jika modul-modul serupa tidak menampilkan relasi internal tersebut, maka agent harus mengikuti pola itu dan tidak menampilkannya.
- Adanya relation method di model bukan alasan untuk otomatis menambahkannya ke output Resource.

## 4. Contoh Kasus Yang Dianggap Salah
- Salah:
```php
$this->mergeWhen($this->relationLoaded('cashTransaction'), [
    'cash_transaction' => $this->when($this->cashTransaction, fn () => [
        'id' => Hashids::encode($this->cashTransaction?->id),
        'date' => $this->cashTransaction?->date,
        'amount' => $this->cashTransaction?->amount,
    ]),
]),
```

- Alasan salah:
  - Membuat struktur response manual inline.
  - Tidak konsisten dengan pola Resource project.
  - Menambah special-case yang akan menyulitkan maintenance.

## 5. Bentuk Keputusan Yang Wajib Diambil Agent
- Saat menemukan relasi baru, jangan langsung menganggap relasi itu harus diekspos di response.
- Cek dulu apakah relasi itu memang bagian dari kebutuhan output atau hanya detail internal backend.
- Saat menemukan relasi baru yang perlu diekspos, cek dulu apakah Resource dedicated sudah ada.
- Jika belum ada, buat Resource dedicated yang sesuai.
- Setelah itu, gunakan Resource tersebut di parent Resource.
- Jangan memilih shortcut manual hanya karena terlihat lebih cepat.

## 6. Urutan Berpikir Yang Wajib
1. Cari pola existing di codebase.
2. Tentukan dulu apakah data itu memang perlu diekspos ke API.
3. Cari class pendukung yang seharusnya dipakai.
4. Jika belum ada, buat class pendukung sesuai pola proyek.
5. Baru integrasikan ke file utama.

## 7. Aturan Urutan Field
- Untuk tampilan field di UI, payload request, resource, DTO, form, dan detail card, urutan field wajib mengikuti urutan field bisnis pada migration tabel yang menjadi sumber data.
- Jika hanya sebagian field yang ditampilkan, pertahankan urutan relatifnya sesuai migration. Jangan menyusun ulang hanya karena terasa lebih enak dilihat.
- Contoh: jika migration berurutan `company_id`, `branch_id`, `code`, `name`, `is_bank`, maka saat menampilkan subset field, urutan yang benar adalah `branch`, lalu `code`, lalu `name`, lalu `is_bank`.
- Pengecualian hanya boleh dilakukan jika user secara eksplisit meminta urutan berbeda.
- Agent dilarang mengutamakan preferensi pribadi dalam penyusunan urutan field.

## 8. Prinsip Review Diri
- Sebelum finalizing perubahan, agent wajib memeriksa:
  - apakah field atau relasi ini benar-benar perlu tampil di response,
  - apakah implementasi ini konsisten dengan pola modul lain,
  - apakah ada class dedicated yang seharusnya dibuat,
  - apakah ada shortcut manual yang mestinya dihindari,
  - apakah urutan field sudah mengikuti migration kecuali user meminta sebaliknya.

Jika jawaban terhadap salah satu poin di atas adalah ya, maka implementasi harus diperbaiki sebelum diserahkan.
