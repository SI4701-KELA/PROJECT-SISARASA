<?php

use App\Http\Controllers\Api\AccountPasswordController;
use App\Http\Controllers\Api\AccountProfileController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BuyerController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\AdminComplaintController;
use App\Http\Controllers\SellerComplaintController;
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
})->middleware(['auth', 'verified', 'check.banned'])->name('dashboard');

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
    // PBI-28: Admin Reports
    Route::get('/reports', [AdminController::class, 'reports'])->name('admin.reports');
    // PBI-29: Admin Report Actions
    Route::patch('/reports/{id}/reject', [AdminController::class, 'rejectReport'])->name('admin.reports.reject');
    Route::patch('/reports/{id}/ban-store', [AdminController::class, 'banStore'])->name('admin.reports.ban');
    // PBI-20: Ticketing Komplain (Admin)
    Route::get('/complaints', [AdminComplaintController::class, 'index'])->name('admin.complaints.index');
    Route::patch('/complaints/{id}', [AdminComplaintController::class, 'update'])->name('admin.complaints.update');
});

// Seller Routes
Route::middleware(['auth', 'check.banned', 'role:seller'])->prefix('seller')->group(function () {
    Route::get('/profile', [SellerController::class, 'profile'])->name('seller.profile');
    Route::post('/profile', [SellerController::class, 'updateProfile'])->name('seller.profile.update');
    Route::post('/documents', [SellerController::class, 'uploadDocuments'])->name('seller.upload-documents');
    // PBI-20: Komplain Masuk ke Toko
    Route::get('/complaints', [SellerComplaintController::class, 'index'])->name('seller.complaints');

    // Katalog management restricted to verified sellers
    Route::middleware('verified_seller')->group(function () {
        Route::get('/products', [SellerController::class, 'products'])->name('seller.products');
        Route::post('/product', [SellerController::class, 'storeProduct'])->name('seller.product.store');
        Route::put('/product/{id}', [SellerController::class, 'updateProduct'])->name('product.update');
        Route::delete('/product/{id}', [SellerController::class, 'destroyProduct'])->name('product.destroy');
        Route::patch('/product/{id}/toggle-discount', [SellerController::class, 'toggleDiscount'])->name('seller.product.toggle-discount');
    });
});

// Buyer Routes
Route::middleware(['auth', 'check.banned', 'role:buyer'])->prefix('buyer')->group(function () {
    Route::get('/menu', [BuyerController::class, 'menu'])->name('buyer.menu');

    // Fitur PBI-10: GPS Otomatis Pembeli
    Route::get('/nearby', [BuyerController::class, 'nearby'])->name('buyer.nearby');

    // Fitur PBI-23: Halaman Daftar Katalog Semua Toko
    Route::get('/stores', [BuyerController::class, 'stores'])->name('buyer.stores');
    Route::get('/store/{id}', [BuyerController::class, 'storeDetail'])->name('buyer.store-show');

    // Halaman detail toko (profil + produk)
    Route::get('/store/{id}', [BuyerController::class, 'storeDetail'])->name('buyer.store.show');

    // Fitur PBI-3: Manajemen Favorit & Toko Tersimpan
    Route::post('/favorite/toggle', [FavoriteController::class, 'toggle'])->name('buyer.favorite.toggle');
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('buyer.favorites.index');

    // Fitur PBI-28: Pelaporan Toko
    Route::post('/reports', [ReportController::class, 'store'])->name('buyer.reports.store');
    
    // PBI-20: Ticketing Komplain (Buyer)
    Route::get('/stores/{seller}/complaint', [ComplaintController::class, 'create'])->name('buyer.complaint.create');
    Route::post('/stores/{seller}/complaint', [ComplaintController::class, 'store'])->name('buyer.complaint.store');
    Route::get('/complaints', [ComplaintController::class, 'index'])->name('buyer.complaints.index');
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

Route::get('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
});

require __DIR__.'/auth.php';