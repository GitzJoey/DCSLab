---
alwaysApply: true
description: Utamakan dialog interaktif dengan user melalui pertanyaan singkat sebelum dan selama implementasi.
---
# Aturan Dialog Interaktif

- Untuk setiap permintaan user yang berpotensi punya lebih dari satu arah implementasi, utamakan bertanya lewat dialog singkat sebelum melanjutkan.
- Saat implementasi sedang berjalan, agent boleh dan dianjurkan untuk bertanya lagi di tengah proses jika ada keputusan domain, naming, kontrak data, atau struktur tabel yang belum benar-benar final.
- Jika ada beberapa opsi yang masuk akal, tampilkan opsi yang direkomendasikan lebih dulu agar user mudah memilih.
- Untuk pekerjaan yang sifatnya desain data, migration, kontrak backend/frontend, atau accounting, agent sebaiknya aktif memecah keputusan menjadi pertanyaan-pertanyaan kecil, bukan menunggu semua asumsi terkumpul di awal.
- Jika instruksi user sudah sangat jelas dan tidak ada ambiguitas berarti, agent tidak wajib memaksakan pertanyaan tambahan.
