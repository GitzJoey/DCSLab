@extends('doctoraccounting.layouts.main')

@section('container')

<style>
  hr{
    border-top: 3px solid #000;
  }
</style>

    <div class="container text-center">
      <h1>Booking Jadwal DEMO</h1>
      <p>Ingin agar Bisnis Anda Menguntungkan & Lebih Mudah di Kontrol Dengan Doctor Accounting?</p>
    </div>

  <section class="container">
    <div class="row text-center">
      <div class="col-sm-12">
        <h5>👇 Langsung aja pilih isi form di bawah ini 👇 </h5>
        <h5> dan Tanggal di bawah ini untuk mulai..</h5>
        <hr>
      </div>
    </div>
  </section>
  
  <div class="card" style="margin-top: 75px; width:25rem">
    <div class="card-header">
      <p class="font-weight-bold">Perhatian!</p> 
    </div>
    <ul class="list-group list-group-flush">
      <li class="list-group-item">1. Isi form sesuai dengan data asli untuk mempermudah kami menghubungi anda.</li>
      <li class="list-group-item">2. Download software untuk free trial ada di bawah ini,</li>
      <li class="list-group-item">3. Hubungi customer sales kami untuk aktivasi trial dan tutorial penggunaan. </li>
    </ul>
  </div>

  <div class="button" style="margin-top: 15px;">
    <button type="button" class="btn btn-primary btn-lg">DOWNLOAD</button>
  </div>
@endsection