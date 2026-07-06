<?php

use Illuminate\Support\Facades\Route;
use App\Mail\NotifikasiEmail;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/kirim-email', function () {
    // Alamat email tujuan
    $emailTujuan = "email_penerima@gmail.com"; 

    // Perintah untuk mengirim email
    Mail::to($emailTujuan)->send(new NotifikasiEmail());

    return "Email berhasil dikirim!";
});
