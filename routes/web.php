<?php

use App\Http\Controllers\Api\AccountPasswordController;
use App\Http\Controllers\Api\AccountProfileController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BuyerController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FavoriteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $role = request()->user()->role;
    if ($role === 'buyer') {
        return redirect()->route('buyer.menu');
    } elseif ($role === 'seller') {
        return redirect()->route('seller.profile');
    }
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('admin.validations');
    })->name('admin.dashboard');
    Route::get('/stores', [AdminController::class, 'stores'])->name('admin.stores');
    Route::get('/validations', [AdminController::class, 'validations'])->name('admin.validations');
    Route::get('/sellers/{id}/document', [AdminController::class, 'viewDocument'])->name('admin.sellers.document');
    Route::patch('/sellers/{id}/verify', [AdminController::class, 'verifySeller'])->name('admin.sellers.verify');
    // PBI-24: Moderasi Pending Profile Updates
    Route::patch('/sellers/{id}/approve-update', [AdminController::class, 'approveUpdate'])->name('admin.sellers.approve-update');
    Route::patch('/sellers/{id}/reject-update', [AdminController::class, 'rejectUpdate'])->name('admin.sellers.reject-update');
});

// Seller Routes
Route::middleware(['auth', 'role:seller'])->prefix('seller')->group(function () {
    Route::get('/profile', [SellerController::class, 'profile'])->name('seller.profile');
    Route::post('/profile', [SellerController::class, 'updateProfile'])->name('seller.profile.update');
    Route::post('/documents', [SellerController::class, 'uploadDocuments'])->name('seller.upload-documents');

    // Katalog management restricted to verified sellers
    Route::middleware('verified_seller')->group(function () {
        Route::get('/products', [SellerController::class, 'products'])->name('seller.products');
        Route::post('/product', [SellerController::class, 'storeProduct'])->name('seller.product.store');
        Route::post('/product/{id}', [SellerController::class, 'updateProduct'])->name('product.update');
        Route::delete('/product/{id}', [SellerController::class, 'destroyProduct'])->name('product.destroy');
        Route::patch('/product/{id}/toggle-discount', [SellerController::class, 'toggleDiscount'])->name('seller.product.toggle-discount');
    });
});

// Buyer Routes
Route::middleware(['auth', 'role:buyer'])->prefix('buyer')->group(function () {
    Route::get('/menu', [BuyerController::class, 'menu'])->name('buyer.menu');

    // Fitur PBI-10: GPS Otomatis Pembeli
    Route::get('/nearby', [BuyerController::class, 'nearby'])->name('buyer.nearby');

    // Fitur PBI-23: Halaman Daftar Katalog Semua Toko
    Route::get('/stores', [BuyerController::class, 'stores'])->name('buyer.stores');

    // Fitur PBI-3: Manajemen Favorit & Toko Tersimpan
    Route::post('/favorite/toggle', [FavoriteController::class, 'toggle'])->name('buyer.favorite.toggle');
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('buyer.favorites.index');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::match(['patch', 'put'], '/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'throttle:60,1'])->prefix('api/account')->group(function () {
    Route::match(['patch', 'put'], '/profile', [AccountProfileController::class, 'update'])
        ->name('api.account.profile.update');
    Route::match(['post', 'patch'], '/password', [AccountPasswordController::class, 'update'])
        ->name('api.account.password.update');
});

require __DIR__.'/auth.php';
