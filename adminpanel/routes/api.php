<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FrontController;

Route::get('/users', [FrontController::class, 'index']);
Route::get('/sections', [FrontController::class, 'sections'])->name('api.sections');
