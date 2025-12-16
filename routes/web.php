<?php

use Illuminate\Support\Facades\Route;

 route::get('/tentang', function (){
    return view('tentang');
 });
 route::get('/sapa/{nama}', function ($nama){
    return "hallo,$nama! Selamat datang di toko gehel.";
 });
 route::get('/kategori/{nama?}', function ($nama = 'Semua'){
    return "Menampilkan kategori: $nama";
 });
 Route::get('/produk/{id}', function ($id) {
    return "Detail produk #$id";
})->name('produk.detail');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
