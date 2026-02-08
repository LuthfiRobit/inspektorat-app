@extends('administration.layouts.app')

@section('title', 'Monitoring | Scoring Desa')
@section('meta-description',
    'Halaman monitoring untuk melihat dan memantau scoring desa berdasarkan kelengkapan dokumen
    kegiatan setiap tahun anggaran dan periode.')

@section('this-page-style')
    <link href="{{ asset('templates/administration/vendor/datatables/css/jquery.dataTables.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('templates/administration/vendor/datatables/responsive/responsive.css') }}" rel="stylesheet" />
@endsection

@section('content')
    <div class="content-body default-height">
        <div class="container-fluid">
            <!-- Header Halaman -->
            <div class="form-head mb-4 d-flex align-items-center gap-2">
                <small class="text-muted">Scoring -</small>
                <h4 class="text-dark fw-semibold mb-0">Scoring Desa</h4>
            </div>

            <div class="row">
                <div class="card">
                    <div class="card-header d-sm-flex d-block border-0 pb-0 flex-wrap">
                        <div class="pr-3 me-auto mb-sm-0 mb-3">
                            <h4 class="fs-20 text-black mb-1">Daftar Informasi Scoring Desa</h4>
                            <span class="fs-12 text-muted">Pantau skor desa berdasarkan kelengkapan dokumen setiap tahun
                                anggaran dan periode yang telah ditetapkan.</span>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Alert Peringkat User (Hidden by Default) -->
                        <div id="userRankingAlert" class="alert alert-info d-none mb-4 fade show" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fa fa-trophy fs-3 me-3"></i>
                                <div>
                                    <h5 class="alert-heading fw-bold mb-1">Informasi Peringkat</h5>
                                    <p class="mb-0" id="userRankingText">Memuat peringkat...</p>
                                </div>
                            </div>
                        </div>

                        <!-- Filter Section -->
                        <div class="row g-3 mb-4 align-items-end">

                            <!-- Filter Tahun -->
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Tahun</label>
                                <select id="filter_tahun" class="selectpicker form-control wide form-select-md"
                                    data-live-search="true" data-size="5" title="Pilih Tahun">
                                    <option value="">Semua Tahun</option>
                                    @foreach ($tahunAnggaranList as $tahun)
                                        <option value="{{ $tahun->id_tahun_anggaran }}">
                                            {{ $tahun->tahun }} @if ($tahun->status === 'aktif')
                                                (Aktif)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Filter Periode -->
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Periode</label>
                                <select id="filter_periode" class="selectpicker form-control wide form-select-md"
                                    data-live-search="true" data-size="5" title="Pilih Periode">
                                    <option value="">Semua Periode</option>
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

                            <!-- Filter Kecamatan -->
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Kecamatan</label>
                                <select id="filter_kecamatan" class="selectpicker form-control wide form-select-md"
                                    data-live-search="true" data-size="5" title="Pilih Kecamatan">
                                    <option value="">Semua Kecamatan</option>
                                    @foreach ($kecamatanList as $item)
                                        <option value="{{ $item->id_kecamatan }}">
                                            {{ $item->nama_kecamatan }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Reset Button -->
                            <div class="col-md-3 d-flex justify-content-end">
                                <button id="btnResetFilter" type="button" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-sync-alt me-1"></i> Reset Filter
                                </button>
                            </div>

                        </div>

                        <!-- Table Section -->
                        <div class="table-responsive">
                            <table id="example" class="table table-sm align-middle table-striped gs-0 gy-2 nowrap"
                                style="width:100%;">
                                <thead>
                                    <tr class="text-center text-muted text-uppercase">
                                        {{-- <th style="width: 8%;" class="text-start align-middle">Tahun</th> --}}
                                        <th width="5%" class="text-center align-middle">Aksi</th>
                                        <th width="15%" class="text-center align-middle">Desa</th>
                                        <th width="12%" class="text-center align-middle">Kecamatan</th>
                                        <th width="5%" class="text-center align-middle">Peringk</th>
                                        <th width="8%" class="text-center align-middle">Total Skor</th>
                                        <!-- <th width="10%" class="text-center align-middle">Waktu Submit</th> -->
                                        <th width="8%" class="text-center align-middle">Kegiatan Terlapor</th>
                                        <th width="8%" class="text-center align-middle">Belum Terlapor</th>
                                        <th width="8%" class="text-center align-middle">Dok. Wajib</th>
                                        <th width="8%" class="text-center align-middle">Dok. Tambahan</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-800 fw-bolder fs-sm-8 fs-lg-6">
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            <i class="las la-folder-open fs-1 d-block mb-2"></i>
                                            Tidak ada data scoring desa yang tercatat saat ini.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Catatan Tambahan -->
                        <div class="alert alert-light border mt-3">
                            <strong>Catatan:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Gunakan filter di atas untuk menyaring laporan berdasarkan tahun, periode, desa, atau
                                    status.</li>
                                <li>Klik tombol <span class="text-primary fw-semibold">"Detail"</span> pada kolom aksi untuk
                                    melihat rincian scoring desa.</li>
                                <li>Data pada halaman ini bersifat historis dan digunakan untuk pemantauan perkembangan
                                    scoring desa di wilayah kecamatan Anda.</li>
                            </ul>
                        </div>

                        <!-- Deskripsi Halaman -->
                        <!-- Deskripsi Halaman & Aturan Scoring -->
                        <div class="alert alert-primary mb-4">
                            <div class="d-flex align-items-start gap-3">
                                <i class="fa fa-info-circle fs-3 mt-1"></i>
                                <div>
                                    <h5 class="alert-heading fw-bold mb-2">Informasi & Aturan Penilaian (Scoring)</h5>
                                    <p class="mb-2">Halaman ini menampilkan peringkat kinerja desa berdasarkan ketepatan waktu dan kelengkapan administrasi.</p>
                                    
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <strong><i class="fa fa-calculator me-1"></i> Mekanisme Penilaian:</strong>
                                            <ul class="mb-2 ps-3 small">
                                                <li><strong>Total Skor:</strong> Merupakan poin rata-rata (Total Poin / Jumlah Kegiatan) dari seluruh laporan yang disubmit.</li>
                                                <li><strong>Poin Kegiatan:</strong> Maksimun 100 poin per kegiatan. Penalti sebesar <strong>10 poin</strong> dikurangi untuk setiap hari keterlambatan.</li>
                                                <li><strong>Kelengkapan:</strong> Dokumen wajib yang disetujui berkontribusi pada profil kepatuhan desa.</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <strong><i class="fa fa-trophy me-1"></i> Pemeringkatan:</strong>
                                            <ul class="mb-0 ps-3 small">
                                                <li>Urutan peringkat desa ditentukan berdasarkan <strong>Total Skor (Rata-rata) tertinggi</strong>.</li>
                                                <li>Jika skor sama, prioritas diberikan kepada desa dengan jumlah <strong>Dokumen Wajib</strong> yang lebih banyak.</li>
                                            </ul>
                                        </div>
                                    </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Detail Scoring -->
    <div class="modal fade" id="modalDetailScoring" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Scoring Desa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalDetailContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Memuat data...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('this-page-scripts')
    <script src="{{ asset('templates/assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('templates/administration/vendor/datatables/responsive/responsive.js') }}"></script>
    <script src="{{ asset('templates/assets/plugins/datatables/lodash.min.js') }}"></script>

    @include('administration.monitoring.scoring.scripts.desa-datatable-init')
@endsection
