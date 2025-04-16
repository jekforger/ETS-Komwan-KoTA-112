<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload & Preview Data Mahasiswa</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .container { max-width: 950px; margin-top: 40px; } /* Sedikit lebih lebar */
        .preview-table { margin-top: 30px; }
        .alert { margin-top: 20px; }
        .table-responsive { max-height: 500px; } /* Batas tinggi tabel preview */
    </style>
</head>
<body>
<div class="container">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center"> {{-- Tambah d-flex --}}
            <h4 class="mb-0">Upload & Preview Data Mahasiswa</h4>
            {{-- TOMBOL BARU MENUJU DAFTAR MAHASISWA --}}
            <a href="{{ route('mahasiswa.index') }}" class="btn btn-light btn-sm">
                <i class="bi bi-list-ul me-1"></i> Lihat Semua Data
            </a>
            {{-- AKHIR TOMBOL BARU --}}
        </div>
            <div class="card-body">

                {{-- Tampilkan Pesan Sukses --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- Tampilkan Pesan Error --}}
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- Tampilkan Error Validasi Form (jika ada) --}}
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Terdapat kesalahan input:
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif


                {{-- 1. Form Upload Awal (Hanya Tampil Jika Tidak Ada Preview) --}}
                @unless(isset($data) && count($data) > 0)
                <form action="{{ route('upload.process') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                    @csrf
                    <div class="mb-3">
                        <label for="file" class="form-label fw-bold">Pilih File Excel</label>
                        <input type="file" name="file" id="file" class="form-control" accept=".xlsx, .xls, .csv" required>
                        <div class="form-text">Format yang didukung: .xlsx, .xls, .csv. Maksimal 10MB. Baris pertama harus header.</div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-cloud-arrow-up-fill me-1"></i> Upload & Preview
                    </button>
                </form>
                @endunless


                {{-- 2. Bagian Preview Data (Tampil Jika Ada Data) --}}
                @if(isset($data) && count($data) > 0)
                <div class="preview-table">
                    <h5 class="mt-4 mb-3 fw-bold">Preview Data</h5>
                    <p>Berikut adalah data dari file yang Anda upload. Periksa kembali sebelum menyimpan ke database.</p>
                    <div class="table-responsive border rounded">
                        <table class="table table-striped table-bordered table-hover table-sm mb-0">
                            <thead class="table-dark sticky-top">
                                <tr>
                                    <th>#</th> {{-- Tambah kolom nomor baris --}}
                                    {{-- Ambil header dari kunci array baris pertama --}}
                                    @if(isset($data[0]) && is_array($data[0]))
                                        @foreach(array_keys($data[0]) as $key)
                                            {{-- Format header: ganti underscore jadi spasi, buat Title Case --}}
                                            <th>{{ \Illuminate\Support\Str::title(str_replace('_', ' ', $key)) }}</th>
                                        @endforeach
                                    @else
                                        <th>Data Tidak Terbaca</th> {{-- Fallback --}}
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data as $index => $row)
                                    <tr>
                                        <td>{{ $index + 1 }}</td> {{-- Nomor baris mulai dari 1 --}}
                                        @if(is_array($row))
                                            @foreach($row as $value)
                                                <td>{{ $value }}</td>
                                            @endforeach
                                        @else
                                            <td colspan="{{ isset($data[0]) ? count($data[0]) : 1 }}">Baris ini tidak valid</td> {{-- Fallback --}}
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Form untuk menyimpan data ke database --}}
                    <form action="{{ route('save.data') }}" method="POST" class="mt-4">
                        @csrf
                        {{-- KIRIM PATH FILE SEMENTARA secara tersembunyi --}}
                        <input type="hidden" name="temp_file_path" value="{{ $tempFilePath ?? '' }}">

                        <button type="submit" class="btn btn-success me-2">
                            <i class="bi bi-database-fill-add me-1"></i> Simpan ke Database
                        </button>
                        {{-- Tombol Batal/Upload Ulang (Kembali ke form awal) --}}
                        <a href="{{ route('upload.form') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i> Batal / Upload Ulang
                        </a>
                    </form>
                </div>
                @elseif(isset($data)) {{-- Kondisi jika $data ada tapi kosong (sudah ditangani di controller, tapi sbg fallback) --}}
                 <div class="alert alert-warning mt-3">
                     <i class="bi bi-exclamation-circle-fill me-2"></i>
                     Tidak ada data yang ditemukan dalam file untuk dipreview.
                </div>
                 <a href="{{ route('upload.form') }}" class="btn btn-primary mt-2">
                     <i class="bi bi-arrow-left me-1"></i> Kembali ke Upload
                 </a>
                @endif

            </div> {{-- End card-body --}}
        </div> {{-- End card --}}
    </div> {{-- End container --}}

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>