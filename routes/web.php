<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController; 
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;



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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::middleware('auth')->group(function () {

    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])
        ->name('home');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::put('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');
});

Route::controller(GoogleController::class)->group(function () {
   
    Route::get('/auth/google', 'redirect')
        ->name('auth.google');
    Route::get('/auth/google/callback', 'callback')
        ->name('auth.google.callback');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.avatar.destroy');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
});
// Homepage
Route::get('/', [HomeController::class, 'index'])->name('home');

// Katalog Produk
Route::get('/products', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/products/{slug}', [CatalogController::class, 'show'])->name('catalog.show');

// ================================================
// HALAMAN YANG BUTUH LOGIN (Customer)
// ================================================

Route::middleware('auth')->group(function () {
    // Keranjang Belanja
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/{item}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{item}', [CartController::class, 'remove'])->name('cart.remove');

    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/toggle/{product}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

    // Checkout
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    // Pesanan Saya
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    // Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ================================================
// HALAMAN ADMIN (Butuh Login + Role Admin)
// ================================================

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Produk CRUD
    Route::resource('products', AdminProductController::class);

    // Kategori CRUD
    Route::resource('categories', AdminCategoryController::class);

    // Manajemen Pesanan
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.updateStatus');
});

// ================================================
// AUTH ROUTES (dari Laravel UI)
// ================================================
Auth::routes();


