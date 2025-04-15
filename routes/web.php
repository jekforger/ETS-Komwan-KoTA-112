<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExcelController;

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

// Route GET untuk menampilkan form
Route::get('/upload', [ExcelController::class, 'index'])->name('upload');

// Route POST untuk proses upload
Route::post('/upload', [ExcelController::class, 'import'])->name('import');
