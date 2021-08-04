@extends('doctoraccounting.layouts.main')

@section('container')
<div class="container mt-5">
    <div class="card text-dark bg-light">
        <div class="card-header"><h1 class="text-center">Fitur & Nilai Investasi</h1></div>
        
        <div class="container mt-3">
            <div class="row">
                <div class="col">

                    <p>
                        <a class="btn btn-dark" data-bs-toggle="collapse" href="#perdagangan-standard" role="button" aria-expanded="false" aria-controls="collapseExample">
                            Perdagangan Standard
                        </a>
                    </p>
                    <div class="collapse" id="perdagangan-standard">
                        <div class="card mb-5">
                            <div class="card-body">
                                <p class="card-text">
                                    <p>+ Multi Operator (Bisa Digunakan Oleh Lebih Dari Satu Pemakai) <br />+ Berbagai Macam Satuan / Kemasan Product (Pcs -&gt; Pack, Gram -&gt; Ons -&gt; Kg, Pcs -&gt; Lusin, Meter -&gt; Roll, dll&hellip;) <br />+ Product Discount Per Quantity <br />+ Product Discount Per Kelompok Pelanggan <br />+ Pencatatan Penyesuaian Stock <br />+ Pencatatan Purchase Order (PO) <br />+ Pencatatan Pembelian <br />+ Pencatatan Retur Pembelian <br />+ Pencatatan Penawaran Penjualan <br />+ Pencatatan Sales Order (SO) <br />+ Pencatatan Penjualan <br />+ Pencatatan Piutang Penjualan <br />+ Pencatatan Retur Penjualan <br />+ Pencatatan Kelebihan Penjualan <br />+ Pencatatan Laporan Log Pelanggan <br />+ Laporan Stock <br />+ Laporan Kartu Stock <br />+ Laporan Analisa Stock <br />+ Laporan Pembuktian HPP <br />+ Laporan Laba Rugi </p>    
                                </p>
                            </div>

                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-sm-5">Free Training : 3x</div>
                                    <div class="col-sm fw-bold">Sistem Beli : Rp. 3.500.000</div>
                                    <div class="col-sm fw-bold">Sistem Sewa : Rp. 150.000 / bln</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p>
                        <a class="btn btn-dark" data-bs-toggle="collapse" href="#multi-gudang" role="button" aria-expanded="false" aria-controls="collapseExample">
                            Multi Gudang
                        </a>
                    </p>
                    <div class="collapse" id="multi-gudang">
                        <div class="card mb-5">
                            <div class="card-body">
                                <p class="card-text">
                                    <p>Pencatatan Lebih Dari Satu Gudang / Lokasi Stock </p>
                                </p>
                            </div>

                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-sm-5">Free Training : 1x</div>
                                    <div class="col-sm fw-bold">Sistem Beli : Rp. 2.000.000</div>
                                    <div class="col-sm fw-bold">Sistem Sewa : Rp. 100.000 / bln</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p>
                        <a class="btn btn-dark" data-bs-toggle="collapse" href="#salesman" role="button" aria-expanded="false" aria-controls="collapseExample">
                            Salesman
                        </a>
                    </p>
                    <div class="collapse" id="salesman">
                        <div class="card mb-5">
                            <div class="card-body">
                                <p class="card-text">
                                    <p>+ Pencatatan Wilayah Penjualan <br />+ Pencatatan Nama Salesman</p>
                                </p>
                            </div>

                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-sm-5">Free Training : 1x</div>
                                    <div class="col-sm fw-bold">Sistem Beli : Rp. 1.000.000</div>
                                    <div class="col-sm fw-bold">Sistem Sewa : Rp. 100.000 / bln</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p>
                        <a class="btn btn-dark" data-bs-toggle="collapse" href="#multi-kelompok-pelanggan" role="button" aria-expanded="false" aria-controls="collapseExample">
                            Multi Kelompok Pelanggan
                        </a>
                    </p>
                    <div class="collapse" id="multi-kelompok-pelanggan">
                        <div class="card mb-5">
                            <div class="card-body">
                                <p class="card-text">
                                    <p>+ Pencatatan penjualan untuk setiap kelompok pelanggan yang berbeda (End User, Semi Grosir, Grosir, dsb&hellip;) / (Tokopedia, Bukalapak, Shopee, dsb&hellip;)<br />+ Pencatatan discount yang berbeda untuk setiap kelompok pelanggan. </p>
                                </p>
                            </div>

                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-sm-5">Free Training : 2x</div>
                                    <div class="col-sm fw-bold">Sistem Beli : Rp. 1.500.000</div>
                                    <div class="col-sm fw-bold">Sistem Sewa : Rp. 100.000 / bln</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p>
                        <a class="btn btn-dark" data-bs-toggle="collapse" href="#jasa" role="button" aria-expanded="false" aria-controls="collapseExample">
                            Jasa
                        </a>
                    </p>
                    <div class="collapse" id="jasa">
                        <div class="card mb-5">
                            <div class="card-body">
                                <p class="card-text">
                                    <p>+ Pencatatan Product Jasa <br />+ Pencatatan Komposisi Product Jasa </p>
                                </p>
                            </div>

                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-sm-5">Free Training : 3x</div>
                                    <div class="col-sm fw-bold">Sistem Beli : Rp. 1.500.000</div>
                                    <div class="col-sm fw-bold">Sistem Sewa : Rp. 100.000 / bln</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p>
                        <a class="btn btn-dark" data-bs-toggle="collapse" href="#ppn" role="button" aria-expanded="false" aria-controls="collapseExample">
                            PPN
                        </a>
                    </p>
                    <div class="collapse" id="ppn">
                        <div class="card mb-5">
                            <div class="card-body">
                                <p class="card-text">
                                    <p>+ Pencatatan Penyesuaian Hutang PPN<br />+ Pencatatan Penyesuaian Piutang PPN<br />+ Pencatatan PPN Pembelian<br />+ Pencatatan Pembayaran Hutang PPN melalui Piutang PPN<br />+ Laporan Arus PPN Keluar Masuk</p>
                                </p>
                            </div>

                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-sm-5">Free Training : 6x</div>
                                    <div class="col-sm fw-bold">Sistem Beli : Rp. 3.000.000</div>
                                    <div class="col-sm fw-bold">Sistem Sewa : Rp. 200.000 / bln</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p>
                        <a class="btn btn-dark" data-bs-toggle="collapse" href="#point-penjualan" role="button" aria-expanded="false" aria-controls="collapseExample">
                            Point Penjualan
                        </a>
                    </p>
                    <div class="collapse" id="point-penjualan">
                        <div class="card mb-5">
                            <div class="card-body">
                                <p class="card-text">
                                    <p>+ Pencatatan Point Penjualan <br />+ Laporan Point Penjualan Per Pelanggan</p>
                                </p>
                            </div>

                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-sm-5">Free Training : 4x</div>
                                    <div class="col-sm fw-bold">Sistem Beli : Rp. 2.000.000</div>
                                    <div class="col-sm fw-bold">Sistem Sewa : Rp. 100.000 / bln</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p>
                        <a class="btn btn-dark" data-bs-toggle="collapse" href="#hutang-piutang" role="button" aria-expanded="false" aria-controls="collapseExample">
                            Hutang Piutang
                        </a>
                    </p>
                    <div class="collapse" id="hutang-piutang">
                        <div class="card mb-5">
                            <div class="card-body">
                                <p class="card-text">
                                    <p>+ Pencatatan Biaya Langsung Terhutang (Apabila mengambil modul keuangan)<br />+ Pencatatan Hutang Biaya Dibayar Dimuka (Apabila mengambil modul keuangan)<br />+ Pencatatan Piutang Pendapatan Langsung (Apabila mengambil modul keuangan)<br />+ Pencatatan Piutang Pendapatan Dibayar Dimuka (Apabila mengambil modul keuangan)<br />+ Pencatatan Hutang Pembelian Aset (Apabila mengambil modul keuangan)<br />+ Pencatatan Piutang Penjualan Aset (Apabila mengambil modul keuangan)<br />+ Pencatatan Piutang Uang Muka Purchase Order (PO) <br />+ Pencatatan Hutang Pembelian <br />+ Pencatatan Piutang Retur Pembelian <br />+ Pencatatan Hutang Biaya Langsung Proses Produksi (Apabila mengambil modul proses produksi)<br />+ Pencatatan Hutang Biaya Periode Proses Produksi (Apabila mengambil modul proses produksi)<br />+ Pencatatan Hutang Uang Muka Sales Order (SO) <br />+ Pencatatan Piutang Penjualan <br />+ Pencatatan Hutang Bahan Tambahan Penjualan <br />+ Pencatatan Piutang Retur Penjualan <br />+ Pencatatan Hutang Dari Pencairan Pihak Lain Baik Yang Sudah Berjalan Maupun Yang Baru Dicairkan (Hutang Bank, dsb&hellip;)<br />+ Pencatatan Piutang Dari Pencairan Pihak Lain Baik Yang Sudah Berjalan Maupun Yang Baru Dicairkan (Kasbon Karyawan, dsb&hellip;)<br />+ Mendukung pencatatan pembayaran lebih dari 1 kali angsuran</p>
                                </p>
                            </div>

                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-sm-5">Free Training : 4x</div>
                                    <div class="col-sm fw-bold">Sistem Beli : Rp. 4.000.000</div>
                                    <div class="col-sm fw-bold">Sistem Sewa : Rp. 200.000 / bln</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p>
                        <a class="btn btn-dark" data-bs-toggle="collapse" href="#manufaktur" role="button" aria-expanded="false" aria-controls="collapseExample">
                            Proses Produksi (Manufaktur)
                        </a>
                    </p>
                    <div class="collapse" id="manufaktur">
                        <div class="card mb-5">
                            <div class="card-body">
                                <p class="card-text">
                                    <p>+ Pencatatan Informasi Surat Perintah Kerja <br />+ Pencatatan Informasi Kepala Produksi <br />+ Pencatatan Informasi Pelaksana Produksi <br />+ Pencatatan Kelompok Proses Produksi <br />+ Pencatatan Proses Produksi (Mendukung Proses Produksi One To Many, One To One, Many To Many, Many To One)<br />+ Pencatatan Resep Produksi (Mendukung Multi Step Proses Produksi)<br />+ Pencatatan Biaya Langsung Terkait Proses Produksi <br />+ Pencatatan Biaya Periode Terkait Proses Produksi (Gaji Karyawan, Listrik, dsb&hellip;)</p>
                                </p>
                            </div>

                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-sm-5">Free Training : 3x</div>
                                    <div class="col-sm fw-bold">Sistem Beli : Rp. 7.500.000</div>
                                    <div class="col-sm fw-bold">Sistem Sewa : Rp. 250.000 / bln</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p>
                        <a class="btn btn-dark" data-bs-toggle="collapse" href="#proyek" role="button" aria-expanded="false" aria-controls="collapseExample">
                            Proyek
                        </a>
                    </p>
                    <div class="collapse" id="proyek">
                        <div class="card mb-5">
                            <div class="card-body">
                                <p class="card-text">
                                    <p>+ Pencatatan Pekerjaan Berdasarkan Proyek</p>
                                </p>
                            </div>

                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-sm-5">Free Training : 1x</div>
                                    <div class="col-sm fw-bold">Sistem Beli : Rp. 1.500.000</div>
                                    <div class="col-sm fw-bold">Sistem Sewa : Rp. 100.000 / bln</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p>
                        <a class="btn btn-dark" data-bs-toggle="collapse" href="#multi-kelompok-pelanggan" role="button" aria-expanded="false" aria-controls="collapseExample">
                            Accounting
                        </a>
                    </p>
                    <div class="collapse" id="multi-kelompok-pelanggan">
                        <div class="card mb-5">
                            <div class="card-body">
                                <p class="card-text">
                                    <p>+ Pencatatan Biaya Tambahan Dalam Pembelian (Ongkos Angkut / Ongkos Bongkar, dsb &hellip;) <br />+ Pencatatan Refund Pembelian (Proteksi Harga, dsb&hellip;) <br />+ Pencatatan Biaya Tambahan Dalam Penjualan (Ongkos Angkut / Ongkos Bongkar, dsb &hellip;) <br />+ Pencatatan Rekening (Kas Kecil, Kas Besar, Bank BCA, Bank Mandiri, dsb &hellip;) <br />+ Pencatatan Modal <br />+ Pencatatan Prive <br />+ Pencatatan Biaya Langsung (Gaji Karyawan, Listrik, PDAM, dsb&hellip;) <br />+ Pencatatan Biaya Dibayar Dimuka (Sewa Ruko, Iklan Per Tahun, dsb&hellip;) <br />+ Pencatatan Pendapatan (Bonus Supplier, Bunga Bank, dsb&hellip;) <br />+ Pencatatan Pendapatan Dibayar Dimuka (Sewa Baliho Tahunan, Pembayaran Kontrak Tahunan, dsb&hellip;) <br />+ Pencatatan Mutasi Saldo Antar Kas <br />+ Pencatatan Penyesuaian Jumlah Aset <br />+ Pencatatan Pembelian Aset <br />+ Pencatatan Penjualan Aset <br />+ Laporan Arus Kas Keluar Masuk <br />+ Laporan Transaksi Jurnal Umum <br />+ Laporan Buku Besar <br />+ Laporan Neraca Saldo <br />+ Laporan Neraca</p>
                                </p>
                            </div>

                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-sm-5">Free Training : 3x</div>
                                    <div class="col-sm fw-bold">Sistem Beli : Rp. 3.500.000</div>
                                    <div class="col-sm fw-bold">Sistem Sewa : Rp. 200.000 / bln</div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        
        <div class="card-footer">
            <p class="fs-6 fw-bold">
                * Harga yang tertera adalah harga non custom / cabang<br />* Setup Cost untuk penambahan client Rp. 200.000,- per pc<br />* Pemanggilan Trainer di luar training Rp 75.000 / 2 jam
            </p>
        </div>
    </div>
</div>

<div class="container">
    <div class="card text-dark bg-light my-5">
        <h3 class="text-center mt-3">Fasilitas Lain</h3>

        <div class="card-body">
            <div class="row">
                <div class="col-sm mb-3">
                    <div class="card text-dark bg-light">
                        <div class="card-header"><h5>Berlangganan</h5></div>
                        <div class="card-body" style="min-height: 220px;">
                            <p class="card-text">
                                <p>+ Konsultasi<br />+ Implementasi<br />+ Inspeksi<br />+ Pemeliharaan Berjalan<br />+ Remote Via Team Viewer<br />+ Auto Updater (Otomatis update apabila ada)</p>
                            </p>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-sm fw-bold">Rp. 350.000 / bln per database</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm mb-3">
                    <div class="card text-dark bg-light"">
                        <div class="card-header"><h5>Management Consultant</h5></div>
                        <div class="card-body" style="min-height: 220px;">
                            <p class="card-text">
                                <p>+ Mengetahui permasalahan yang MENGHAMBAT di perusahaan Anda<br />+ Menentukan Solusi yang Tepat Untuk Masalah yang Menghambat<br />+ Penerapan Solusi yang Telah disepakati dan melakukan evaluasi secara berkala</p>
                            </p>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-sm fw-bold">Rp. 350.000 / bln per kantor cabang</div>
                            </div>
                        </div>
                    </div>      
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col">
                    <div class="card text-dark bg-light">
                        <div class="card-header"><h5>Online</h5></div>
                        <div class="card-body">
                            <p class="card-text">
                                <p>+ Dapat dioperasikan dari mana saja selama terkoneksi internet dan program sudah di install.</p>
                            </p>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-sm fw-bold">Rp. 500.000 (2 jam / pertemuan)</div>
                            </div>
                        </div>
                    </div>      
                </div>
            </div>
        </div>
    </div> 
</div>

<div class="container my-5">
    <div class="card text-dark bg-light">
        <h5 class="text-center mt-3">
            <p class="fs-6">
                KEBUTUHAN DASAR PERANGKAT KOMPUTER SERVER, ADMINISTRASI, KASIR <br>
                DAN PRINTER SERTA PERANGKAT JARINGAN <br>
                UNTUK IMPLEMENTASI PROGRAM DOCTOR ACCOUNTING
            </p>
        </h5>

        <div class="card-body">                       
            <div class="row mb-3">
                <div class="col">
                    <div class="card text-dark bg-light">
                        <div class="card-header"><h5>Komputer Kerja</h5></div>
                        <div class="card-body">
                            <p class="card-text">
                                <p>Untuk Komputer Server, minimal memiliki CPU dengan core processor yang banyak sehingga dapat bekerja secara multi tasking.</p>
                                <p>Untuk Komputer Admin, minimal memiliki CPU dengan core lebih dari satu sehingga dapat bekerja dengan baik saat menghadapi data dengan jumlah yang besar.</p>
                                <p>Untuk Komputer Kasir, dapat menggunakan computer standard yang dilengkapi dengan barcode scanner dan printer kasir karena hanya mengambil data dengan jumlah yang tidak terlalu banyak.</p>
                                
                                <div class="row">
                                    <div class="col-sm-4">

                                        <div class="card text-dark bg-light mb-3" style="min-height: 650px;">                                              
                                            <div class="card-header"><h5>PC Desktop</h5></div>
                                            <div class="card-body">
                                                <div class="text-center">
                                                    <img src="{{ asset('images/doctoraccounting/komputer/pc-desktop.jpg') }}" width="300" class="rounded-circle">
                                                </div>
                                                
                                                <p class="card-text">
                                                    <div class="row">
                                                        <div class="col-6">
                                                            Processor<br>
                                                            Mainboard<br>
                                                            Harddisk<br>
                                                            RAM<br>
                                                            Casing + PSU<br>
                                                            Monitor<br>
                                                            Mouse + Keyboard
                                                        </div>
                                                        <div class="col-6">
                                                            Intel / AMD Dual Core<br>
                                                            Standard<br>
                                                            SSD 120 GB<br>
                                                            4 GB<br>
                                                            Casing Standard<br>
                                                            LED 19 Inch<br>
                                                            Logitech USB
                                                        </div>
                                                    </div>
                                                </p>
                                            </div>
                                            <div class="card-footer">
                                                <div class="row">
                                                    <div class="col fw-bold text-end">Harga Perkiraan Rp. 5.000.000</div>
                                                </div>
                                            </div>
                                        </div> 

                                    </div>

                                    <div class="col-sm-4">

                                        <div class="card text-dark bg-light mb-3" style="min-height: 650px;">
                                            <div class="card-header"><h5>PC All In One</h5></div>
                                            <div class="card-body">
                                                <div class="text-center">
                                                    <img src="{{ asset('images/doctoraccounting/komputer/pc-allinone.jpg') }}" width="300" class="rounded-circle">
                                                </div>

                                                <p class="card-text">
                                                    <div class="row">
                                                        <div class="col-6">
                                                            Processor<br>
                                                            Mainboard<br>
                                                            Harddisk<br>
                                                            RAM<br>
                                                            Casing + PSU<br>
                                                            Monitor<br>
                                                            Mouse + Keyboard
                                                        </div>
                                                        <div class="col-6">
                                                            Intel / AMD Dual Core<br>
                                                            Built Up<br>
                                                            SSD 120 GB<br>
                                                            4 GB<br>
                                                            Built Up<br>
                                                            LED 19 - 22 Inch<br>
                                                            Built Up
                                                        </div>
                                                    </div>
                                                </p>
                                            </div>
                                            <div class="card-footer">
                                                <div class="row">
                                                    <div class="col fw-bold text-end">Harga Perkiraan Rp. 6.000.000</div>
                                                </div>
                                            </div>
                                        </div> 
                                        
                                    </div>

                                    <div class="col-sm-4">

                                        <div class="card text-dark bg-light mb-3" style="min-height: 650px;">
                                            <div class="card-header"><h5>Laptop</h5></div>
                                            <div class="card-body">
                                                <div class="text-center">
                                                    <img src="{{ asset('images/doctoraccounting/komputer/laptop.jpg') }}" width="300" class="rounded-circle">
                                                </div>

                                                <p class="card-text">
                                                    <div class="row">
                                                        <div class="col-6">
                                                            Processor<br>
                                                            Mainboard<br>
                                                            Harddisk<br>
                                                            RAM<br>
                                                            Casing + PSU<br>
                                                            Monitor<br>
                                                            Mouse + Keyboard
                                                        </div>
                                                        <div class="col-6">
                                                            Intel / AMD Dual Core<br>
                                                            Built Up<br>
                                                            SSD 120 GB<br>
                                                            4 GB<br>
                                                            Built Up<br>
                                                            LED 14 Inch<br>
                                                            Built Up
                                                        </div>
                                                    </div>
                                                </p>
                                            </div>
                                            <div class="card-footer">
                                                <div class="row">
                                                    <div class="col fw-bold text-end">Harga Perkiraan Rp. 4.500.000 s/d 5.500.000</div>
                                                </div>
                                            </div>
                                        </div> 
                                        
                                    </div>
                                </div>

                            </p>
                        </div>
                    </div>      
                </div>
            </div>
        </div>

        <div class="card-body">                       
            <div class="row mb-3">
                <div class="col">
                    <div class="card text-dark bg-light">
                        <div class="card-header"><h5>Printer Kasir</h5></div>
                        <div class="card-body">
                            <p class="card-text">

                                <p>
                                    <h5 class="text-center fw-bold">Perbedaan Printer Thermal dan Dot Matrix. Mana Lebih Baik?</h5>

                                    <p>
                                        <p>Kita akan membandingkan dua jenis printer yang lain lagi yaitu perbedaan printer thermal dan dot matrix yang saat ini banyak digunakan untuk mencetak struk pembayaran. Buat kamu yang punya usaha entah itu toko, online shop, jasa PPOB dan sebagainya, pasti tidak asing mendengar kedua jenis printer ini. Lalu apa saja perbedaannya? Lebih baik yang mana?</p>
                                        <p>Membahas mengenai perbedaan printer thermal dan dot matrix, tidak elok rasanya jika kita tidak mengenalinya secara mendasar terlebih dahulu. Kedua printer ini sebenarnya memiliki fungsi yang sama, yaitu banyak digunakan sebagai alat cetak yang berhubungan dengan struk pembayaran entah itu nota, invoice dan juga bill. Namun dalam beberapa jenis tertentu, printer thermal difungsikan untuk mencetak barcode dan juga tag produk untuk keperluan retail.</p>
                                        <p>Printer thermal dan dot matrix untuk kasir ini bisa juga disebut sebagai printer POS (Point Of Sales) yang banyak di gunakan untuk aktivitas perdagangan produk atau jasa yang bisa terintegrasi dengan aplikasi penjualan pada laptop, komputer, tablet atau smartphone Android. Tanpa adanya kedua printer ini, maka para pengusaha akan kesulitan mencetak struk pembayaran untuk setiap pengguna jasa atau pembeli yang berkunjung.</p>
                                        
                                        <p>Bagi kamu yang saat ini sedang mencari printer kasir, pasti dibuat bingung dengan berbagai jenis yang ada di pasaran. Dari segi teknologinya, printer kasir ini dibedakan menjadi dua jenis yaitu thermal dan juga dot matrix. Keduanya memiliki kelebihan dan kekurangannya masing-masing. Nah daripada bingung, lebih baik kamu cari tahu apa saja hal-hal yang membedakan printer thermal dan dot matrix. Lalu yang manakah yang lebih baik?</p>
                                        
                                        <div class="fw-bold">PERBEDAAN DARI SEGI TEKNOLOGI</div>
                                        <p>Dari segi teknologi, perbedaan printer thermal dan dot matrix memiliki perbedaan yang amat sangat mencolok. Printer thermal tidak menggunakan tinta, namun memakai elemen thermal untuk menghasilkan panas yang diaplikasikan ke kertas khusus. Sedangkan printer dot matrix masih memakai teknologi tua yang mirip dengan mesin ketik. Tulisan bisa tercetak pada kertas melalui kain pita tipis yang berisi tinta kemudian ditekan oleh pin logam kecil yang bergerak naik turun atau maju mundur.</p>
                                    
                                        <div class="fw-bold">PERBEDAAN DARI SEGI KECEPATAN CETAK</div>
                                        <p>Perbedaan printer thermal dan dot matrix dari segi kecepatan cetak jauh berbeda. Printer thermal memiliki kecepatan cetak jauh lebih cepat daripada printer dot matrix. Printer dot matrix dapat mencetak dalam skala kecepatan 50 milimeter per detik. Namun printer thermal memiliki kecepatan cetak 5 kali lipat lebih cepat yaitu 250 milimeter per detik. Hal ini memang memungkinkan karena teknologi thermal yang dipakai jauh lebih efektif dalam mencetak tulisan daripada dot matrix.</p>
                                        
                                        <div class="fw-bold">PERBEDAAN DARI KUALITAS HASIL CETAK</div>
                                        <p>Dari kualitas hasil cetak, perbedaan printer thermal dan dot matrix dapat kita lihat secara kasat mata. Hasil cetak printer thermal jauh lebih baik dan lebih solid karena elemen dapat memanaskan kertas secara merata. Sedangkan hasil cetak printer dot matrix sedikit lebih buram apalagi ketika pita mulai mengering. Terlebih hasil cetak printer dot matrix berupa titik-titik kecil yang membentuk suatu pola sesuai dengan perintah. Seperti nama “dot” yang merupakan titik.</p>

                                        <div class="fw-bold">PERBEDAAN DARI JENIS KERTAS YANG DIGUNAKAN</div>
                                        <p>Kertas yang digunakan untuk kedua jenis printer ini sangat jauh berbeda. Perbedaan printer thermal dan dot matrix dari segi kertas ada pada jenisnya. Printer thermal hanya bisa digunakan dengan kertas khusus yang sudah dilapisi suatu bahan kimia, kertas ini dapat membentuk sebuah tulisan ketika terkena elemen thermal. Sedangkan printer dot matrix dapat dipakai dengan kertas biasa namun dengan ukuran tertentu, namun printer dot matrix memiliki kelebihan yaitu dapat mencetak 2 rangkap atau lebih. Karena prinsip dasar printer ini masih berbasis tinta yang bisa diaplikasikan ke kertas biasa.</p>

                                        <div class="fw-bold">PERBEDAAN SEGI PERAWATAN</div>
                                        <p>Perbedaan printer thermal dan dot matrix dari segi perawatan hampir mirip. Hanya saja, printer thermal memiliki perawatan yang jauh lebih mudah. Kamu hanya perlu rajin membersihkan body printer serta elemen thermal dari debu dan kotoran memakai alkohol. Sedangkan ketika memakai printer dot matrix, kamu harus mengganti pita tinta ketika sudah mulai kering. Namun kamu juga harus rajin membersihkan body printer agar debu dan kotoran tidak menempel.</p>

                                        <div class="fw-bold">LEBIH BAIK MANA PRINTER THERMAL ATAU DOT MATRIX?</div>
                                        <p>Melihat dari berbagai segi yang sudah dibahas diatas, kami mengambil kesimpulan bahwa printer thermal jauh lebih baik daripada printer dot matrix. Namun ini adalah hal wajar mengingat teknologi thermal yang digunakan juga lebih baik daripada dot matrik yang merupakan teknologi tua. Selain itu, pertimbangan dari segi hasil cetak dan juga kecepatan mencetak membuat printer thermal bisa jadi pilihan utama. Hanya saja ukuran kertas printer thermal hanya dua jenis yaitu 58mm dan 80mm.</p>

                                        <p>Itulah beberapa penjelasan mengenai perbedaan printer thermal dan dot matrix yang perlu kamu ketahui. Jika saat ini kamu ingin membeli printer kasir untuk kebutuhan aplikasi POS (Point Of Sale), sebaiknya pilihlah yang thermal saja. Alasannya sudah kita bahas pada paragraf diatas. Selain itu, jangan lupa persiapkan aplikasi penjualan yang baik supaya printer bisa bekerja dengan baik. Printer thermal bisa dipakai di beberapa perangkat seperti laptop, komputer, tablet atau smartphone Android.</p>
                                    </p>
                                </p>
                                    
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="card text-dark bg-light mb-3" style="min-height: 410px;">
                                            <div class="card-body">
                                                <div class="text-center">
                                                    <img src="{{ asset('images/doctoraccounting/printer/thermal-58mm.jpg') }}" width="300" class="rounded-circle">
                                                </div>
                                            </div>
                                            <div class="card-footer">

                                                <div class="row">
                                                    <div class="col-9">
                                                        Printer Thermal 58mm
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="text-end">
                                                            ± 350.000
                                                        </div>
                                                    </div>
                                                </div>
                                
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="card text-dark bg-light mb-3" style="min-height: 410px;">
                                            <div class="card-body">
                                                <div class="text-center">
                                                    <img src="{{ asset('images/doctoraccounting/printer/thermal-80mm.jpg') }}" width="300" class="rounded-circle">
                                                </div>
                                            </div>
                                            <div class="card-footer">
                                                
                                                <div class="row">
                                                    <div class="col-9">
                                                        Printer Thermal 80mm
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="text-end">
                                                            ± 850.000
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="card text-dark bg-light mb-3" style="min-height: 410px;">
                                            <div class="card-body">
                                                <div class="text-center">
                                                    <img src="{{ asset('images/doctoraccounting/printer/dotmatrix-lx310.jpg') }}" width="300" class="rounded-circle">
                                                </div>
                                            </div>
                                            <div class="card-footer">

                                                <div class="row">
                                                    <div class="col-8">
                                                        Printer Dot Matrix LX 310
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="text-end">
                                                            ± 2.500.000
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </p>
                        </div>
                    </div>      
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col">
                    <div class="card text-dark bg-light">
                        <div class="card-header"><h5>Printer Barcode</h5></div>
                        <div class="card-body">

                            <p>
                                <div class="fw-bold">Printer barcode DIRECT THERMAL</div>
                                Adalah printer dengan teknologi DIRECT THERMAL. Yaitu kertas atau bahan dipanaskan langsung oleh thermal head (bagian pemanas) dari printer. Kelemahan jenis direct thermal ini adalah bahwa hasil cetak mudah pudar atau rusak, karena terpengaruh suhu.
                            </p>   
                            <p>
                                <div class="fw-bold">Printer Barcode THERMAL TRANSFER</div>
                                Cara kerja kedua disebut dengan THERMAL TRANSFER. Teknologi ini juga digunakan oleh alat cetak atau printer foto profesional. Intinya pada printer ini selain menggunakan kertas, juga menggunakan media yang disebut RIBBON. Ribbon inilah yang dipanaskan, sehingga warna dari ribbon akan lengket (transfer) ke kertas atau media. Hasil cetaknya jelas sudah tidak terpengaruh suhu, karena prinsipnya adalah pelengketan dengan panas.
                            </p>                                       
                            
                            <p class="card-text">

                                <div class="row">
                                    <div class="col-sm">
                                        <div class="card text-dark bg-light mb-3" style="min-height: 400px;">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="text-center">
                                                        <img src="{{ asset('images/doctoraccounting/printer/barcode-printer.jpg') }}" width="300" class="rounded-circle">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-footer">

                                                <div class="row">
                                                    <div class="col-9">
                                                        Printer Barcode
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="text-end">
                                                            ± 850.000
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-sm">
                                        <div class="card text-dark bg-light mb-3" style="min-height: 400px;">
                                            <div class="card-body">
                                                <div class="row">

                                                    <div class="text-center">
                                                        <img src="{{ asset('images/doctoraccounting/printer/barcode-label.png') }}" width="300" class="rounded-circle">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-footer">
                                                <div class="row">
                                                    <div class="col-9">
                                                        Contoh barcode label
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="text-end">
                                                            ± 10-20 / pcs
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </p>

                        </div>
                    </div>      
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col">
                    <div class="card text-dark bg-light">
                        <div class="card-header"><h5>Perangkat Pendukung Lainnya</h5></div>
                        <div class="card-body">
                            <p class="card-text">

                                <div class="row">
                                    <div class="col-sm">
                                        <div class="card text-dark bg-light mb-3" style="min-height: 420px;">
                                            <div class="card-body">
                                                <p class="card-text">
                                                    <div class="text-center">
                                                        <img src="{{ asset('images/doctoraccounting/barcode-scanner/barcode-scanner-manual.jpg') }}" width="300" class="rounded-circle">
                                                    </div>
                                                </p>
                                            </div>
                                            <div class="card-footer">
                                                
                                                <div class="row">
                                                    <div class="col-9">
                                                        Barcode Scanner Manual
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="text-end">
                                                            ± 250.000
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-sm">
                                        <div class="card text-dark bg-light mb-3" style="min-height: 420px;">
                                            <div class="card-body">
                                                <p class="card-text">
                                                    <div class="text-center">
                                                        <img src="{{ asset('images/doctoraccounting/barcode-scanner/barcode-scanner-duduk.jpg') }}" width="300" class="rounded-circle">
                                                    </div>
                                                </p>
                                            </div>
                                            <div class="card-footer">

                                                <div class="row">
                                                    <div class="col-9">
                                                        Barcode Scanner Duduk
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="text-end">
                                                            ± 2.000.000
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm">
                                        <div class="card text-dark bg-light mb-3">
                                            <div class="card-body">
                                                <p class="card-text">
                                                    <div class="text-center">
                                                        <img src="{{ asset('images/doctoraccounting/printer/printer-laporan-laser.jpg') }}" width="300" class="rounded-circle">
                                                    </div>
                                                </p>
                                            </div>
                                            <div class="card-footer">

                                                <div class="row">
                                                    <div class="col-9">
                                                        Printer Inkjet Infus Pabrik
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="text-end">
                                                            ± 1.500.000
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-sm">
                                        <div class="card text-dark bg-light mb-3">
                                            <div class="card-body">
                                                <p class="card-text">
                                                    <div class="text-center">
                                                        <img src="{{ asset('images/doctoraccounting/printer/printer-laporan-tinta-infus-pabrik.jpg') }}" width="300" class="rounded-circle">
                                                    </div>
                                                </p>
                                            </div>
                                            <div class="card-footer">

                                                <div class="row">
                                                    <div class="col-9">
                                                        Printer Laser
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="text-end">
                                                            ± 1.500.000
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm">

                                        <div class="card text-dark bg-light mb-3" style="min-height: 420px;">
                                            <div class="card-body">
                                                <p class="card-text">
                                                    <div class="text-center">
                                                        <img src="{{ asset('images/doctoraccounting/cash-drawer/cash-drawer.jpg') }}" width="300" class="rounded-circle">
                                                    </div>
                                                </p>
                                            </div>
                                            <div class="card-footer">

                                                <div class="row">
                                                    <div class="col-9">
                                                        Cash Drawer
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="text-end">
                                                            ± 500.000
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                    </div>
                                    <div class="col-sm">

                                        <div class="card text-dark bg-light mb-3" style="min-height: 420px;">
                                            <div class="card-body">
                                                <p class="card-text">
                                                    <div class="text-center">
                                                        <img src="{{ asset('images/doctoraccounting/ups/ups-700va.jpg') }}" width="300" class="rounded-circle">
                                                    </div>
                                                </p>
                                            </div>
                                            <div class="card-footer">
                                                <div class="row">
                                                    <div class="col-9">
                                                        UPS
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="text-end">
                                                            ± 600.000
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                
                            </p>
                        </div>
                    </div>      
                </div>
            </div>
        </div>
        
    </div>            
</div>
@endsection