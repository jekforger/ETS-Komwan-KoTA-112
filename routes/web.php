<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\DataMahasiswaController; // <-- Tambahkan ini

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// --- Rute untuk Proses Upload Excel ---
Route::get('/upload', [ExcelController::class, 'index'])->name('upload.form');
Route::post('/upload', [ExcelController::class, 'handleUpload'])->name('upload.process');
Route::post('/save-data', [ExcelController::class, 'saveData'])->name('save.data');
// --- Akhir Rute Upload Excel ---


// --- Rute untuk CRUD Data Mahasiswa ---
// Route::resource() secara otomatis membuat route untuk:
// GET /mahasiswa           (index)   -> DataMahasiswaController@index   (nama: mahasiswa.index)
// GET /mahasiswa/create    (create)  -> DataMahasiswaController@create  (nama: mahasiswa.create)
// POST /mahasiswa          (store)   -> DataMahasiswaController@store   (nama: mahasiswa.store)
// GET /mahasiswa/{mahasiswa} (show)    -> DataMahasiswaController@show    (nama: mahasiswa.show)
// GET /mahasiswa/{mahasiswa}/edit (edit) -> DataMahasiswaController@edit (nama: mahasiswa.edit)
// PUT/PATCH /mahasiswa/{mahasiswa} (update) -> DataMahasiswaController@update (nama: mahasiswa.update)
// DELETE /mahasiswa/{mahasiswa} (destroy) -> DataMahasiswaController@destroy (nama: mahasiswa.destroy)
Route::resource('mahasiswa', DataMahasiswaController::class);
// --- Akhir Rute CRUD ---