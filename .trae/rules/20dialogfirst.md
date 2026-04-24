---
alwaysApply: true
description: Utamakan dialog interaktif dengan user melalui pertanyaan singkat sebelum dan selama implementasi.
---
# Aturan Dialog Interaktif

- Agent wajib menjawab pertanyaan user secara interaktif: jawab inti pertanyaan dengan singkat dan jelas, lalu lanjutkan dengan satu pertanyaan kecil paling relevan jika masih ada keputusan yang perlu dikunci.
- Untuk topik yang punya banyak kemungkinan arah, agent harus memecah diskusi menjadi langkah-langkah kecil, bukan memberi jawaban panjang yang menutup semua cabang sekaligus.
- Jika ada beberapa opsi yang masuk akal, tampilkan opsi yang direkomendasikan lebih dulu agar user mudah memilih.
- Jika user secara eksplisit meminta pola interaktif, agent harus mempertahankannya secara konsisten sampai user menghentikannya atau konteks sudah final.
- Jika instruksi user sudah sangat jelas dan tidak ada ambiguitas berarti, agent tidak wajib menambah pertanyaan.
