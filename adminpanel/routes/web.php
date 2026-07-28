<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;


Route::get('/clear-cache', function() {
    Artisan::call('view:clear');
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    return "All cache cleared successfully!";
});
// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });
Route::redirect('/', '/admin/login');

Route::namespace('App\Http\Controllers\Admin')->prefix('/admin')->group(function() {
    Route::match(['get', 'post'], 'login', [AdminController::class, 'login'])->name('admin.login');
    Route::middleware(['admin.auth'])->group(function () {
        Route::get('dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('logout', [AdminController::class, 'logout'])->name('logout-admin');
        //>>>>>>>>>>>>>>>>>>>>>>>>User activity<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        Route::get('users', [AdminController::class, 'users'])->name('admin-user');
        Route::post('users', [AdminController::class, 'storeUser'])->name('admin-user.store');
        Route::get('users/{user}', [AdminController::class, 'showUser'])->name('admin-user.show');
        Route::put('users/{user}', [AdminController::class, 'updateUser'])->name('admin-user.update');
        Route::patch('users/{user}/status', [AdminController::class, 'updateUserStatus'])->name('admin-user.status');
        Route::delete('users/{user}', [AdminController::class, 'deleteUser'])->name('admin-user.delete');
        //>>>>>>>>>>>>>>>>>>>>>>>>User activity<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        //>>>>>>>>>>>>>>>>>>>>>>>>Section activity<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        Route::get('users', [AdminController::class, 'users'])->name('admin-user');
        //>>>>>>>>>>>>>>>>>>>>>>>>Section activity<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    });
});
require __DIR__.'/auth.php';
