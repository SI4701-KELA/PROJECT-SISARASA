<?php

use App\Http\Controllers\ProfileController;
<<<<<<< Updated upstream
=======
use App\Http\Controllers\BuyerController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FavoriteController;
>>>>>>> Stashed changes
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
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
        return redirect()->route('admin.stores');
    })->name('admin.dashboard');
<<<<<<< Updated upstream
=======
    Route::get('/stores', [AdminController::class, 'stores'])->name('admin.stores');
    Route::get('/sellers/{id}/document', [AdminController::class, 'viewDocument'])->name('admin.sellers.document');
    Route::patch('/sellers/{id}/verify', [AdminController::class, 'verifySeller'])->name('admin.sellers.verify');
    // PBI-24: Moderasi Pending Profile Updates
    Route::patch('/sellers/{id}/approve-update', [AdminController::class, 'approveUpdate'])->name('admin.sellers.approve-update');
    Route::patch('/sellers/{id}/reject-update', [AdminController::class, 'rejectUpdate'])->name('admin.sellers.reject-update');
>>>>>>> Stashed changes
});

// Seller Routes
Route::middleware(['auth', 'role:seller'])->prefix('seller')->group(function () {
    Route::get('/profile', function () {
        return view('seller'); // Nanti bisa dikonfigurasi ke view seller.profile
    })->name('seller.profile');
});

// Buyer Routes
Route::middleware(['auth', 'role:buyer'])->prefix('buyer')->group(function () {
<<<<<<< Updated upstream
    Route::get('/menu', function () {
        return view('buyer'); // Nanti bisa dikonfigurasi ke view buyer.menu
    })->name('buyer.menu');
=======
    Route::get('/menu', [BuyerController::class, 'menu'])->name('buyer.menu');

    // Fitur PBI-10: GPS Otomatis Pembeli
    Route::get('/nearby', [BuyerController::class, 'nearby'])->name('buyer.nearby');

    // Fitur PBI-23: Halaman Daftar Katalog Semua Toko
    Route::get('/stores', [BuyerController::class, 'stores'])->name('buyer.stores');

    // Fitur PBI-3: Manajemen Favorit & Toko Tersimpan
    Route::post('/favorite/toggle', [FavoriteController::class, 'toggle'])->name('buyer.favorite.toggle');
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('buyer.favorites.index');
>>>>>>> Stashed changes
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
