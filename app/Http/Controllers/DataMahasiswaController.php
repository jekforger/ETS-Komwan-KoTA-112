<?php

namespace App\Http\Controllers;

use App\Models\DataMahasiswa; // Pastikan model diimport
use Illuminate\Http\Request; // <-- Tambahkan Request
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class DataMahasiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) // <-- Tambahkan Request $request
    {
        // Validasi input sorting dari request
        $validated = $request->validate([
            // Pastikan kolom sort ada di daftar yang diizinkan
            'sort' => ['sometimes', 'required', Rule::in(['name', 'nim', 'jurusan', 'prodi', 'created_at'])],
            // Pastikan direction adalah 'asc' atau 'desc'
            'direction' => ['sometimes', 'required', Rule::in(['asc', 'desc'])],
        ]);

        // Tentukan kolom dan arah sorting (default jika tidak ada di request)
        $sortColumn = $validated['sort'] ?? 'created_at'; // Default sort by creation date
        $direction = $validated['direction'] ?? 'desc';    // Default descending

        // Ambil data, terapkan sorting, dan gunakan pagination
        $mahasiswas = DataMahasiswa::orderBy($sortColumn, $direction)
                                     ->paginate(10)
                                     // Penting: Tambahkan query string (termasuk sort & direction) ke link pagination
                                     ->withQueryString();

        // Kirim data mahasiswa DAN parameter sorting ke view
        return view('mahasiswa.index', compact('mahasiswas', 'sortColumn', 'direction'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('mahasiswa.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi input
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            // Pastikan aturan unique merujuk ke tabel yang benar (data_mahasiswas)
            'nim' => 'required|string|max:20|unique:data_mahasiswas,nim',
            'jurusan' => 'required|string|max:100',
            'prodi' => 'required|string|max:100',
        ]);

        try {
            DataMahasiswa::create($validatedData);
            return redirect()->route('mahasiswa.index')
                             ->with('success', 'Data mahasiswa berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error("Error storing mahasiswa: " . $e->getMessage());
            return redirect()->back()
                             ->with('error', 'Gagal menambahkan data mahasiswa. Silakan coba lagi.')
                             ->withInput(); // Kembalikan input sebelumnya ke form
        }
    }

    /**
     * Display the specified resource.
     * (Opsional, bisa dilewati jika tidak perlu halaman detail)
     */
    public function show(DataMahasiswa $mahasiswa) // Route Model Binding
    {
        // Jika Anda membuat view 'mahasiswa.show', gunakan ini:
        // return view('mahasiswa.show', compact('mahasiswa'));

        // Jika tidak ada halaman show, redirect ke edit atau index
        return redirect()->route('mahasiswa.edit', $mahasiswa->id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DataMahasiswa $mahasiswa) // Route Model Binding
    {
        return view('mahasiswa.edit', compact('mahasiswa'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DataMahasiswa $mahasiswa) // Route Model Binding
    {
        // Validasi input
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            // Saat update, abaikan unique check untuk record saat ini
            'nim' => 'required|string|max:20|unique:data_mahasiswas,nim,' . $mahasiswa->id,
            'jurusan' => 'required|string|max:100',
            'prodi' => 'required|string|max:100',
        ]);

        try {
            $mahasiswa->update($validatedData);
            return redirect()->route('mahasiswa.index')
                             ->with('success', 'Data mahasiswa berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error("Error updating mahasiswa ID {$mahasiswa->id}: " . $e->getMessage());
            return redirect()->back()
                             ->with('error', 'Gagal memperbarui data mahasiswa. Silakan coba lagi.')
                             ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DataMahasiswa $mahasiswa) // Route Model Binding
    {
        try {
            $mahasiswa->delete();
            return redirect()->route('mahasiswa.index')
                             ->with('success', 'Data mahasiswa berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error("Error deleting mahasiswa ID {$mahasiswa->id}: " . $e->getMessage());
            // Handle foreign key constraint violation jika ada relasi
            if ($e instanceof \Illuminate\Database\QueryException && str_contains($e->getMessage(), 'foreign key constraint fails')) {
                 return redirect()->route('mahasiswa.index')
                                 ->with('error', 'Gagal menghapus data mahasiswa karena terkait dengan data lain.');
            }
            return redirect()->route('mahasiswa.index')
                             ->with('error', 'Gagal menghapus data mahasiswa. Silakan coba lagi.');
        }
    }
}