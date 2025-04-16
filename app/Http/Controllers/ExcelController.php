<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DataImport; // Import untuk menyimpan ke DB
use App\Imports\HeadingRowImport; // Import hanya untuk membaca preview
use Illuminate\Support\Facades\Storage; // Untuk mengelola file sementara
use Illuminate\Support\Facades\Log; // Untuk logging error

class ExcelController extends Controller
{
    /**
     * Menampilkan halaman form upload awal.
     */
    public function index()
    {
        // Opsional: Bersihkan file temp lama jika ada
        // Storage::deleteDirectory('temp_imports');
        return view('upload'); // Mengembalikan view upload.blade.php
    }

    /**
     * Menangani upload file, membaca data, menyimpan file sementara,
     * dan menampilkan halaman preview.
     */
    public function handleUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240' // Max 10MB, sesuaikan
        ]);

        $tempPath = null; // Inisialisasi path sementara

        try {
            $file = $request->file('file');

            // 1. Simpan file sementara di storage/app/temp_imports
            //    Nama file akan di-generate otomatis dan unik
            $tempPath = $file->store('temp_imports');

            // 2. Baca data dari file sementara untuk preview
            //    Gunakan HeadingRowImport agar baris pertama jadi header (kunci array)
            //    dan data dibaca sebagai array.
            $dataArray = Excel::toArray(new HeadingRowImport(), storage_path('app/' . $tempPath));

            // Ambil data dari sheet pertama (index 0)
            $previewData = $dataArray[0] ?? [];

            // Hapus file sementara jika tidak ada data di sheet pertama
            if (empty($previewData)) {
                 Storage::delete($tempPath);
                 return back()->with('error', 'File Excel kosong atau sheet pertama tidak mengandung data.');
            }

            // 3. Kirim data preview dan path file sementara ke view 'upload'
            return view('upload', [
                'data' => $previewData, // Data untuk tabel preview
                'tempFilePath' => $tempPath // Path relatif file sementara (dibutuhkan saat menyimpan)
            ]);

        // Tangani error validasi Maatwebsite/Excel (jika Anda menambahkannya di Importer)
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
             $failures = $e->failures();
             $errorMessage = 'Error validasi data Excel: ';
             foreach ($failures as $failure) {
                 $errorMessage .= "Baris " . $failure->row() . ": " . implode(', ', $failure->errors()) . " ";
             }
             // Hapus file sementara jika ada error validasi saat preview
             if ($tempPath && Storage::exists($tempPath)) {
                 Storage::delete($tempPath);
             }
             return back()->with('error', $errorMessage);
        // Tangani error umum
        } catch (\Exception $e) {
            // Hapus file sementara jika ada error lain saat proses preview
            if ($tempPath && Storage::exists($tempPath)) {
                Storage::delete($tempPath);
            }
            Log::error("Error uploading/previewing Excel: " . $e->getMessage() . "\nStack trace:\n" . $e->getTraceAsString()); // Log error detail
            return back()->with('error', 'Terjadi kesalahan saat memproses file: Periksa format file atau hubungi administrator.');
        }
    }

    /**
     * Menyimpan data dari file sementara ke database.
     */
    public function saveData(Request $request)
    {
        // Validasi bahwa path file sementara dikirimkan
        $request->validate([
            'temp_file_path' => 'required|string'
        ]);

        $tempPath = $request->input('temp_file_path');

        // Validasi keamanan: pastikan file ada di storage dan di dalam folder temp_imports
        if (!Storage::exists($tempPath) || !str_starts_with($tempPath, 'temp_imports/')) {
             // Redirect kembali ke form Awal dengan error
             return redirect()->route('upload.form')->with('error', 'File sementara tidak ditemukan atau tidak valid.');
        }

        try {
            // Gunakan DataImport (yang punya ToModel & WithHeadingRow) untuk import ke DB
            Excel::import(new DataImport, storage_path('app/' . $tempPath));

            // Hapus file sementara setelah berhasil import
            Storage::delete($tempPath);

            // Redirect kembali ke form Awal dengan pesan sukses
            return redirect()->route('upload.form')->with('success', 'Data mahasiswa berhasil disimpan ke database!');

        // Tangani error validasi Maatwebsite/Excel saat menyimpan
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
             $failures = $e->failures();
             $errorMessage = 'Gagal menyimpan. Error validasi data Excel: ';
             foreach ($failures as $failure) {
                 $errorMessage .= "Baris " . $failure->row() . ": " . implode(', ', $failure->errors()) . " ";
             }
             // Hapus file sementara meskipun gagal validasi saat simpan
             Storage::delete($tempPath);
             // Redirect kembali ke form Awal dengan error validasi
             return redirect()->route('upload.form')->with('error', $errorMessage);
        // Tangani error umum saat menyimpan (misal error SQL)
        } catch (\Exception $e) {
            // Jangan hapus file sementara jika gagal import agar bisa dicek manual
            Log::error("Error saving imported Excel: " . $e->getMessage() . " for file " . $tempPath . "\nStack trace:\n" . $e->getTraceAsString());
            // Redirect kembali ke form Awal dengan error umum
            return redirect()->route('upload.form')->with('error', 'Gagal menyimpan data ke database. Periksa log untuk detail atau hubungi administrator.');
        }
    }
}