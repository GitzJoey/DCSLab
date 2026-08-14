---
alwaysApply: true
description: Proyek masih tahap development; utamakan kontrak yang benar dan implementasi yang sederhana tanpa overthinking kompatibilitas sementara
---
# Aturan Status Development Project

Project ini masih tahap development aktif.

## Rule
- Utamakan kontrak data dan implementasi yang benar.
- Frontend dan backend harus mengikuti kontrak yang sama jika user sudah memutuskan formatnya.
- Jangan membuat compatibility layer, normalisasi tersembunyi, atau patch transisi kecuali diminta user.
- Jika ada inkonsistensi lama di seed, default data, atau config development, luruskan sumbernya.
- Perapihan kontrak data, preview hitung, dan struktur UI boleh dilakukan selama tetap konsisten dengan pola proyek.

## Guidance
- `vat_rate` mengikuti satu format yang sama di semua layer.
- Jika seed lama salah, perbaiki seed, bukan tambal di banyak tempat.
