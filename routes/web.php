<?php

use Illuminate\Support\Facades\Route;

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

Route::get('uploaddata', function () {
    return view('upload');
});
Route::get('upload', [UploadController::class, 'showForm'])->name('upload.form');
Route::post('upload', [UploadController::class, 'import'])->name('upload.submit');