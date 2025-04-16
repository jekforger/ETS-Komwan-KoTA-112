<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Data Mahasiswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .container { max-width: 1100px; margin-top: 40px; }
        .action-buttons form { display: inline-block; margin-left: 5px; }
        /* Style untuk link header tabel */
        th a {
            text-decoration: none;
            color: inherit; /* Warna sama seperti teks header biasa */
            display: inline-block; /* Agar icon bisa di sebelahnya */
        }
        th a:hover {
            color: #fff; /* Warna saat hover (sesuaikan jika perlu) */
            text-decoration: underline;
        }
        .sort-icon {
            font-size: 0.8em; /* Ukuran icon sedikit lebih kecil */
            margin-left: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Daftar Mahasiswa</h2>
            <div>
                 <a href="{{ route('mahasiswa.create') }}" class="btn btn-success">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Mahasiswa
                </a>
                <a href="{{ route('upload.form') }}" class="btn btn-secondary">
                    <i class="bi bi-cloud-arrow-up-fill me-1"></i> Upload Data
                </a>
            </div>
        </div>

        {{-- Tampilkan Pesan Sukses/Error --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
         @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                 <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered">
                        <thead class="table-dark">
                            <tr>
                                {{-- Helper function untuk membuat link sort --}}
                                @php
                                function sort_link($label, $column, $currentSort, $currentDirection) {
                                    // Tentukan arah sort berikutnya
                                    $nextDirection = ($currentSort == $column && $currentDirection == 'asc') ? 'desc' : 'asc';
                                    // Buat parameter link, pertahankan query string lain (seperti page)
                                    $linkParams = array_merge(request()->query(), ['sort' => $column, 'direction' => $nextDirection]);
                                    // Buat URL
                                    $url = route('mahasiswa.index', $linkParams);
                                    // Tampilkan ikon jika kolom ini sedang disortir
                                    $icon = '';
                                    if ($currentSort == $column) {
                                        $iconClass = ($currentDirection == 'asc') ? 'bi-sort-up' : 'bi-sort-down';
                                        $icon = "<i class='bi $iconClass sort-icon'></i>";
                                    }
                                    // Return HTML link
                                    return "<th><a href=\"$url\">$label</a>$icon</th>";
                                }
                                @endphp

                                <th>No</th>
                                {!! sort_link('Nama', 'name', $sortColumn, $direction) !!}
                                {!! sort_link('NIM', 'nim', $sortColumn, $direction) !!}
                                {!! sort_link('Jurusan', 'jurusan', $sortColumn, $direction) !!}
                                {!! sort_link('Prodi', 'prodi', $sortColumn, $direction) !!}
                                {!! sort_link('Ditambahkan', 'created_at', $sortColumn, $direction) !!} {{-- Contoh sort by tanggal --}}
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($mahasiswas as $index => $mhs)
                            <tr>
                                <td>{{ $mahasiswas->firstItem() + $index }}</td>
                                <td>{{ $mhs->name }}</td>
                                <td>{{ $mhs->nim }}</td>
                                <td>{{ $mhs->jurusan }}</td>
                                <td>{{ $mhs->prodi }}</td>
                                <td>{{ $mhs->created_at->format('d M Y H:i') }}</td> {{-- Format tanggal --}}
                                <td class="action-buttons">
                                    <a href="{{ route('mahasiswa.edit', $mhs->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <form action="{{ route('mahasiswa.destroy', $mhs->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                {{-- Sesuaikan colspan dengan jumlah kolom baru --}}
                                <td colspan="7" class="text-center">Tidak ada data mahasiswa.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                 {{-- Tampilkan Link Pagination (Dengan styling Bootstrap) --}}
                 <div class="d-flex justify-content-center mt-4">
                     {{-- Pemanggilan ini akan menggunakan styling default Bootstrap jika sudah dikonfigurasi --}}
                    {{ $mahasiswas->links() }}
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>