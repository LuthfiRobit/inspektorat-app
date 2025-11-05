@extends('administration.layouts.app')

@section('title', 'Dashboard Sistem Informasi Monitoring Desa | Inspektorat Kabupaten Probolinggo')
@section('meta-description',
    'Halaman dashboard Sistem Informasi Monitoring Desa Inspektorat Kabupaten Probolinggo.
    Menyajikan ringkasan data dan pemantauan kegiatan desa secara real-time untuk mendukung pengawasan dan evaluasi.')

@section('this-page-style')
    <link href="{{ asset('templates/administration/vendor/datatables/css/jquery.dataTables.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('templates/administration/vendor/datatables/responsive/responsive.css') }}" rel="stylesheet" />
@endsection

@section('content')
    <!-- Content body start -->
    <div class="content-body default-height">
        <div class="container-fluid">
            <!-- Section Heading -->
            <div class="form-head mb-4 d-flex align-items-center gap-2">
                <small class="text-muted">Dashboard -</small>
                <h4 class="text-dark fw-semibold mb-0">Sistem Informasi Monitoring Desa</h4>
            </div>

            <!-- Filter Section -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- Tahun Anggaran -->
                                <div class="col-md-3">
                                    <label class="form-label">Tahun Anggaran</label>
                                    <select class="selectpicker form-control wide form-select-md" id="filter-tahun"
                                        data-live-search="true" data-size="5" title="Pilih Tahun">
                                        <option value="">Loading...</option>
                                    </select>
                                </div>

                                <!-- Periode Bulan -->
                                <div class="col-md-3">
                                    <label class="form-label">Periode Bulan</label>
                                    <select class="selectpicker form-control wide form-select-md" id="filter-bulan"
                                        data-live-search="true" data-size="5" title="Pilih Bulan">
                                        <option value="">Semua Bulan</option>
                                        <option value="1">Januari</option>
                                        <option value="2">Februari</option>
                                        <option value="3">Maret</option>
                                        <option value="4">April</option>
                                        <option value="5">Mei</option>
                                        <option value="6">Juni</option>
                                        <option value="7">Juli</option>
                                        <option value="8">Agustus</option>
                                        <option value="9">September</option>
                                        <option value="10">Oktober</option>
                                        <option value="11">November</option>
                                        <option value="12">Desember</option>
                                    </select>
                                </div>

                                <!-- Jenis Kegiatan -->
                                <div class="col-md-3">
                                    <label class="form-label">Jenis Kegiatan</label>
                                    <select class="selectpicker form-control wide form-select-md" id="filter-jenis-kegiatan"
                                        data-live-search="true" data-size="5" title="Pilih Jenis Kegiatan">
                                        <option value="">Semua Jenis</option>
                                    </select>
                                </div>

                                <!-- Filter Desa -->
                                <div class="col-md-3" id="filter-desa-container">
                                    <label class="form-label">Desa</label>
                                    <select class="selectpicker form-control wide form-select-md" id="filter-desa"
                                        data-live-search="true" data-size="5" title="Pilih Desa">
                                        <option value="">Semua Desa</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-2">
                <!-- Total Desa -->
                <div class="col-sm-6 col-md-4 col-lg-2">
                    <div class="media align-items-center bgl-primary rounded p-2 h-100">
                        <span class="bg-white p-3 me-3 rounded d-flex justify-content-center align-items-center"
                            style="width:50px;height:50px">
                            <i class="fas fa-home" style="font-size:20px;color:#0d6efd;"></i>
                        </span>
                        <div class="media-body">
                            <h5 id="total_desa" class="fs-16 text-black font-w600 mb-0">0</h5>
                            <span class="fs-14">Total Desa</span>
                        </div>
                    </div>
                </div>

                <!-- Total Laporan -->
                <div class="col-sm-6 col-md-4 col-lg-2">
                    <div class="media align-items-center bgl-info rounded p-2 h-100">
                        <span class="bg-white p-3 me-3 rounded d-flex justify-content-center align-items-center"
                            style="width:50px;height:50px">
                            <i class="fas fa-file-alt" style="font-size:20px;color:#0dcaf0;"></i>
                        </span>
                        <div class="media-body">
                            <h5 id="total_laporan" class="fs-16 text-black font-w600 mb-0">0</h5>
                            <span class="fs-14">Total Laporan</span>
                        </div>
                    </div>
                </div>

                <!-- Total Laporan Draft -->
                <div class="col-sm-6 col-md-4 col-lg-2">
                    <div class="media align-items-center bgl-secondary rounded p-2 h-100">
                        <span class="bg-white p-3 me-3 rounded d-flex justify-content-center align-items-center"
                            style="width:50px;height:50px">
                            <i class="fas fa-pencil-alt" style="font-size:20px;color:#6c757d;"></i>
                        </span>
                        <div class="media-body">
                            <h5 id="total_laporan_draft" class="fs-16 text-black font-w600 mb-0">0</h5>
                            <span class="fs-14">Laporan Draft</span>
                        </div>
                    </div>
                </div>

                <!-- Laporan Disetujui -->
                <div class="col-sm-6 col-md-4 col-lg-2">
                    <div class="media align-items-center bgl-success rounded p-2 h-100">
                        <span class="bg-white p-3 me-3 rounded d-flex justify-content-center align-items-center"
                            style="width:50px;height:50px">
                            <i class="fas fa-check-circle" style="font-size:20px;color:#198754;"></i>
                        </span>
                        <div class="media-body">
                            <h5 id="laporan_disetujui" class="fs-16 text-black font-w600 mb-0">0</h5>
                            <span class="fs-14">Disetujui</span>
                        </div>
                    </div>
                </div>

                <!-- Laporan Perlu Revisi -->
                <div class="col-sm-6 col-md-4 col-lg-2">
                    <div class="media align-items-center bgl-warning rounded p-2 h-100">
                        <span class="bg-white p-3 me-3 rounded d-flex justify-content-center align-items-center"
                            style="width:50px;height:50px">
                            <i class="fas fa-edit" style="font-size:20px;color:#ffc107;"></i>
                        </span>
                        <div class="media-body">
                            <h5 id="laporan_revisi" class="fs-16 text-black font-w600 mb-0">0</h5>
                            <span class="fs-14">Perlu Revisi</span>
                        </div>
                    </div>
                </div>

                <!-- Laporan Menunggu Approval -->
                <div class="col-sm-6 col-md-4 col-lg-2">
                    <div class="media align-items-center bgl-primary rounded p-2 h-100">
                        <span class="bg-white p-3 me-3 rounded d-flex justify-content-center align-items-center"
                            style="width:50px;height:50px">
                            <i class="fas fa-hourglass-half" style="font-size:20px;color:#0d6efd;"></i>
                        </span>
                        <div class="media-body">
                            <h5 id="laporan_pending" class="fs-16 text-black font-w600 mb-0">0</h5>
                            <span class="fs-14">Menunggu Approval</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <!-- Kegiatan yang Harus Dilaporkan -->
                <div class="col-sm-12 col-md-6 col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-semibold text-dark">Kegiatan yang Harus Dilaporkan</h5>
                            <span class="badge bg-info text-dark" id="count_kegiatan_harus_dilaporkan">0</span>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-hover align-middle mb-0"
                                    id="table-kegiatan-dilaporkan">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" style="width: 10px;">#</th>
                                            <th scope="col">Periode</th>
                                            <th scope="col">Jenis Kegiatan</th>
                                            <th scope="col">Nama Kegiatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Belum ada kegiatan yang
                                                perlu dilaporkan</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Laporan Perlu Revisi -->
                <div class="col-sm-12 col-md-6 col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-semibold text-dark">Laporan Perlu Revisi</h5>
                            <span class="badge bg-warning text-dark" id="count_laporan_perlu_revisi">0</span>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-hover align-middle mb-0"
                                    id="table-laporan-revisi">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" style="width: 10px;">#</th>
                                            <th scope="col">Desa / Kecamatan</th>
                                            <th scope="col">Kegiatan / Jenis</th>
                                            <th scope="col">Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Tidak ada laporan yang perlu
                                                revisi</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('this-page-scripts')
    <script src="{{ asset('templates/assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('templates/administration/vendor/datatables/responsive/responsive.js') }}"></script>
    <script src="{{ asset('templates/assets/plugins/datatables/lodash.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const kegiatanHarusDilaporkan = [{
                    bulan: 'November',
                    tahun: '2025',
                    jenis: 'Pembangunan Desa',
                    nama: 'Perbaikan Jalan Dusun'
                },
                {
                    bulan: 'November',
                    tahun: '2025',
                    jenis: 'Pendidikan',
                    nama: 'Pelatihan BUMDes'
                },
            ];

            const laporanRevisi = [{
                    desa: 'Desa Ranuagung',
                    kecamatan: 'Kec. Tiris',
                    kegiatan: 'Peningkatan Irigasi',
                    jenis: 'Infrastruktur',
                    tanggal: '2025-10-28'
                },
                {
                    desa: 'Desa Banyuanyar',
                    kecamatan: 'Kec. Banyuanyar',
                    kegiatan: 'Posyandu Lansia',
                    jenis: 'Kesehatan',
                    tanggal: '2025-10-30'
                },
            ];

            // Kegiatan harus dilaporkan
            const tableKegiatan = document.querySelector('#table-kegiatan-dilaporkan tbody');
            if (kegiatanHarusDilaporkan.length) {
                tableKegiatan.innerHTML = kegiatanHarusDilaporkan.map((item, i) => `
            <tr>
                <td>${i + 1}</td>
                <td>${item.bulan} ${item.tahun}</td>
                <td>${item.jenis}</td>
                <td>${item.nama}</td>
            </tr>
        `).join('');
                document.getElementById('count_kegiatan_harus_dilaporkan').textContent = kegiatanHarusDilaporkan
                    .length;
            }

            // Laporan perlu revisi
            const tableRevisi = document.querySelector('#table-laporan-revisi tbody');
            if (laporanRevisi.length) {
                tableRevisi.innerHTML = laporanRevisi.map((item, i) => `
            <tr>
                <td>${i + 1}</td>
                <td><strong>${item.desa}</strong><br><small>${item.kecamatan}</small></td>
                <td>${item.kegiatan}<br><small class="text-muted">${item.jenis}</small></td>
                <td>${item.tanggal}</td>
            </tr>
        `).join('');
                document.getElementById('count_laporan_perlu_revisi').textContent = laporanRevisi.length;
            }
        });
    </script>

@endsection
