<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DoctorAccountingPageController extends Controller
{
    public function home()
    {
        return view('doctoraccounting/home', ['title' => 'Home']);
    }

    public function dokumentasi()
    {
        return view('doctoraccounting/dokumentasi/index', ['title' => 'Dokumentasi']);
    }

    public function faq()
    {
        return view('doctoraccounting/faq/index', ['title' => 'FAQ']);
    }

    public function download()
    {
        return view('doctoraccounting/download/index', ['title' => 'Download']);
    }

    public function harga()
    {
        return view('doctoraccounting/harga/index', ['title' => 'Harga']);
    }

    public function client()
    {
        return view('doctoraccounting/client/index', ['title' => 'Client']);
    }

    public function tentangkami()
    {
        return view('doctoraccounting/tentangkami/index', ['title' => 'Tentang Kami']);
    }
}
