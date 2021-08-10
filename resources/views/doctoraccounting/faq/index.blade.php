@extends('doctoraccounting.layouts.main')

@section('container')

<style>
  hr{
    border-top: 3px solid #000;
  }

  .accordion{
    margin-top: 50px;
  }
</style>


<div class="container text-center">
  <h1>Frequently Asked Questions</h1>
  <h1>(FAQs)</h1>
  <p>Pertanyaan dan jawaban tentang Doctor Accounting ada di sini.</p>
  <hr>
</div>

<div class="container text-center">
  <div class="accordion" id="accordionExample">
    <div class="accordion-item">
      <h2 class="accordion-header" id="heading1">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1" aria-expanded="true" aria-controls="collapse1">
          <h3>Apa itu Doctor Accounting?</h3>
        </button>
      </h2>
      <div id="collapse1" class="accordion-collapse collapse" aria-labelledby="heading1" data-bs-parent="#accordionExample">
        <div class="accordion-body">
          <p class="text-start">Doctor Accounting adalah aplikasi software keuangan yang lengkap dengan sistem Point of Sales (POS),
            pencarian data yang mudah dan pembuatan laporan yang dinamis, dan berbagai fitur lainnya.
            Doctor Accounting dibangun menggunakan aplikasi berbasis desktop yang memiliki keuntungan seperti biaya
            pemeliharaan lebih irit, dapat berdiri sendiri, tidak memerlukan koneksi internet, proses aplikasi cepat, lebih aman dari gangguan pencurian data maupun serangan virus.</p>
        </div>
      </div>
    </div>

    <div class="accordion-item">
      <h2 class="accordion-header" id="heading2">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2" aria-expanded="true" aria-controls="collapse2">
          <h3>Ada berapa jenis layanan Doctor Accounting?</h3>
        </button>
      </h2>
      <div id="collapse2" class="accordion-collapse collapse" aria-labelledby="heading2" data-bs-parent="#accordionExample">
        <div class="accordion-body">
          <p class="text-start">Untuk saat ini, keseluruhan paket layanan software yang bisa digunakan ada 6 jenis dan 12 add-on yang bisa dipilih sesuai dengan kapasitas bisnis Anda. Semua jenis layanan tersebut bisa digunakan dengan sistem berlangganan bulanan. Untuk lebih jelasnya bisa di cek di sini.</p>
        </div>
      </div>
    </div>

    <div class="accordion-item">
      <h2 class="accordion-header" id="heading3">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3" aria-expanded="true" aria-controls="collapse3">
          <h3>Bagaimana cara berlangganan Doctor Accounting?</h3>
        </button>
      </h2>
      <div id="collapse3" class="accordion-collapse collapse" aria-labelledby="heading3" data-bs-parent="#accordionExample">
        <div class="accordion-body">
          <p class="text-start">Untuk bisa menggunakan layanan doctor accounting, Anda bisa menggunakan 2 cara by online / offline:</p>
          <p class="text-start"></p>
          <p class="text-start">By Online: Anda bisa langsung menghubungi customer sales kami dan memilih paket yang diperlukan, nanti untuk activasi dan tutorial akan kami layani by email / datang ke lokasi*.</p>
          <p class="text-start">By Offline: Anda Cukup menghubungi customer sales kami dan tentukan waktu serta hari yang tepat. Selanjutnya  team kami akan datang ke lokasi anda untuk demo product dan aktivasi.</p>
          <p class="text-start"></p>
          <p class="text-start">Sebelum memutuskan untuk berlangganan, Anda bisa mencoba trial gratis selama 30 hari. Silahkan hubungi customer sales kami untuk detailnya.</p>
        </div>
      </div>
    </div>

    <div class="accordion-item">
      <h2 class="accordion-header" id="heading4">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4" aria-expanded="true" aria-controls="collapse4">
          <h3>Apakah saya harus expert di bidang akuntansi terlebih dahulu?</h3>
        </button>
      </h2>
      <div id="collapse4" class="accordion-collapse collapse" aria-labelledby="heading4" data-bs-parent="#accordionExample">
        <div class="accordion-body">
          <p class="text-start">Tidak. Pada dasarnya software ini dibuat untuk memfasilitasi orang awam menjadi expert. Setidaknya anda harus mengerti basic pembukuan.</p>
        </div>
      </div>
    </div>

    <div class="accordion-item">
      <h2 class="accordion-header" id="heading5">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse5" aria-expanded="true" aria-controls="collapse5">
          <h3>Spesifikasi Minimum Untuk Menggunakan Software Doctor Accounting</h3>
        </button>
      </h2>
      <div id="collapse5" class="accordion-collapse collapse" aria-labelledby="heading5" data-bs-parent="#accordionExample">
        <div class="accordion-body">
          <p class="text-start">Prosesor: Intel® Dual Core</p>
          <p class="text-start"></p>
          <p class="text-start">RAM: 1 Gigabyte (GB)</p>
          <p class="text-start"></p>
          <p class="text-start">Hard Disk:  Tersedia 5 Gigabytes (GB)</p>
          <p class="text-start"></p>
          <p class="text-start">Sistem Operasi</p>
          <p class="text-start">Sistem operasi minimal Windows 7</p>
          <p class="text-start"></p>
          <p class="text-start">Jaringan</p>
          <p class="text-start">Local Area Network with WirelessInternet: 256 Kbps Uplink, 512 Kbps Downlink</p>
        </div>
      </div>
    </div>

    <div class="accordion-item">
      <h2 class="accordion-header" id="heading6">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse6" aria-expanded="true" aria-controls="collapse6">
          <h3>Haruskan tersambung dengan internet untuk menggunakan software Doctor Accounting?</h3>
        </button>
      </h2>
      <div id="collapse6" class="accordion-collapse collapse" aria-labelledby="heading8" data-bs-parent="#accordionExample">
        <div class="accordion-body">
          <p class="text-start">Bisa kedua-duanya, karena software kami mempermudah pekerjaan anda. Kalaupun tempat anda tidak ada koneksi internet, data bisa di export dalam berbagai macam jenis file.</p>
        </div>
      </div>
    </div>

    <div class="accordion-item">
      <h2 class="accordion-header" id="heading7">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse7" aria-expanded="true" aria-controls="collapse7">
          <h3>Apakah layanan ini bisa di Upgrade / Downgrade?</h3>
        </button>
      </h2>
      <div id="collapse7" class="accordion-collapse collapse" aria-labelledby="heading7" data-bs-parent="#accordionExample">
        <div class="accordion-body">
          <p class="text-start">Sangat bisa. Untuk downgrade paket atau updgrade paket Anda tinggal menghubungi customer sales kami untuk info lebih lanjut. </p>
        </div>
      </div>
    </div>

    <div class="accordion-item">
      <h2 class="accordion-header" id="heading8">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse8" aria-expanded="true" aria-controls="collapse8">
          <h3>Amankah memakai software ini?</h3>
        </button>
      </h2>
      <div id="collapse8" class="accordion-collapse collapse" aria-labelledby="heading8" data-bs-parent="#accordionExample">
        <div class="accordion-body">
          <p class="text-start">Sangat aman karena software kami untuk keamanan databasenya sudah enkripsi setara dengan Bank. Database di server tidak dapat diketahui (Anonymous) dan di hack sehingga keamanan di Doctor Accounting sangat terjamin.</p>
        </div>
      </div>
    </div>

    <div class="accordion-item">
      <h2 class="accordion-header" id="heading9">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse9" aria-expanded="true" aria-controls="collapse9">
          <h3>Layanan ini berlaku untuk berapa PC?</h3>
        </button>
      </h2>
      <div id="collapse9" class="accordion-collapse collapse" aria-labelledby="heading9" data-bs-parent="#accordionExample">
        <div class="accordion-body">
          <p class="text-start">Layanan ini berlaku untuk satu cabang.</p>
        </div>
      </div>
    </div>

    <div class="accordion-item">
      <h2 class="accordion-header" id="heading10">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse10" aria-expanded="true" aria-controls="collapse10">
          <h3>Apakah usaha saya cocok menggunakan software ini?</h3>
        </button>
      </h2>
      <div id="collapse10" class="accordion-collapse collapse" aria-labelledby="heading10" data-bs-parent="#accordionExample">
        <div class="accordion-body">
          <p class="text-start">Berikut ini berbagai bidang usaha yang cocok menggunakan Doctor Accounting:</p>
          <p class="text-start"></p>
          <p class="text-start">Pabrik;</p>
          <p class="text-start">Toko Kelontong;</p>
          <p class="text-start">Toko Listrik;</p>
          <p class="text-start">Toko Bangunan;</p>
          <p class="text-start">Kedai Kopi;</p>
          <p class="text-start">Vape Store;</p>
          <p class="text-start">Apotik;</p>
          <p class="text-start">Penyewaan Alat Berat;</p>
          <p class="text-start">Kontraktor;</p>
          <p class="text-start">Forwarding;</p>
          <p class="text-start">Jasa konsultan, keuangan dan pajak;</p>
          <p class="text-start">Jasa arsitek;</p>
          <p class="text-start">Jasa periklanan;</p>
          <p class="text-start">Jasa teknologi infomasi dan telekomunikasi</p>
          <p class="text-start">Dan bidang usaha lainnya yang memerlukan teknologi online cloud computing dan masih banyak lagi.</p>
          <p class="text-start"></p>
          <p class="text-start">Namun pada dasarnya hampir semua lini usaha yang membutuhkan laporan baik keuangan maupun stock, sangat cocok disinergikan dengan software doctor accounting.</p>
        </div>
      </div>
    </div>

    <div class="accordion-item">
      <h2 class="accordion-header" id="heading11">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse11" aria-expanded="true" aria-controls="collapse11">
          <h3>Apabila saya mulai dengan paket starter, apakah saya dapat menambahkan fitur lainnya di kemudian hari?</h3>
        </button>
      </h2>
      <div id="collapse11" class="accordion-collapse collapse" aria-labelledby="heading11" data-bs-parent="#accordionExample">
        <div class="accordion-body">
          <p class="text-start">Tentu. Anda bisa melakukan upgrade ke paket yang Anda butuhkan kapan saja.</p>
        </div>
      </div>
    </div>
  </div>
</div>




@endsection