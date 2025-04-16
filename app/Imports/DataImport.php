<?php

namespace App\Imports;

use App\Models\DataMahasiswa; // Sesuaikan jika nama model berbeda
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow; // WAJIB ADA INI
use Maatwebsite\Excel\Concerns\WithValidation; // Opsional: jika ingin validasi
use Maatwebsite\Excel\Concerns\SkipsEmptyRows; // Opsional: lewati baris kosong

class DataImport implements ToModel, WithHeadingRow, SkipsEmptyRows // Tambahkan interface lain jika perlu
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Pastikan kunci array ($row['nama'], $row['nim'], dll.)
        // SESUAI dengan header di file Excel Anda (case-insensitive biasanya)
        return new DataMahasiswa([
            'name'    => $row['nama'] ?? null, // Gunakan null coalescing operator untuk fallback
            'nim'     => $row['nim'] ?? null,
            'jurusan' => $row['jurusan'] ?? null,
            'prodi'   => $row['prodi'] ?? null,
        ]);
    }

    // Opsional: Tambahkan rules jika menggunakan WithValidation
    // public function rules(): array
    // {
    //     return [
    //         '*.nama' => ['required', 'string', 'max:255'],
    //         '*.nim' => ['required', 'string', 'unique:data_mahasiswas,nim'], // Pastikan tabel dan kolom benar
    //         '*.jurusan' => ['required', 'string', 'max:100'],
    //         '*.prodi' => ['required', 'string', 'max:100'],
    //     ];
    // }
}