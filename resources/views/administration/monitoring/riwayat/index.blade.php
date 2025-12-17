@extends('administration.layouts.app')

@section('title', 'Monitoring | Riwayat Laporan Kegiatan')
@section('meta-description',
    'Halaman monitoring untuk melihat dan memantau riwayat laporan kegiatan desa di wilayah
    kecamatan.')

@section('this-page-style')
    <link href="{{ asset('templates/administration/vendor/datatables/css/jquery.dataTables.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('templates/administration/vendor/datatables/responsive/responsive.css') }}" rel="stylesheet" />
@endsection

@section('content')
    <div class="content-body default-height">
        <div class="container-fluid">
            <!-- Header Halaman -->
            <div class="form-head mb-4 d-flex align-items-center gap-2">
                <small class="text-muted">Monitoring -</small>
                <h4 class="text-dark fw-semibold mb-0">Riwayat Laporan Kegiatan</h4>
            </div>

            <div class="row">
                <div class="card">
                    <div class="card-header d-sm-flex d-block border-0 pb-0 flex-wrap">
                        <div class="pr-3 me-auto mb-sm-0 mb-3">
                            <h4 class="fs-20 text-black mb-1">Riwayat Laporan Kegiatan Desa</h4>
                            <span class="fs-12 text-muted">Pantau laporan kegiatan yang telah diunggah oleh desa-desa di
                                wilayah Anda.</span>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row g-3 mb-4 align-items-end">

                            <!-- Filter Tahun -->
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Tahun</label>
                                <select id="filter_tahun" class="selectpicker form-control wide form-select-md"
                                    data-live-search="true" title="Pilih Tahun" data-size="5">
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

                            <!-- Filter Bulan -->
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Periode</label>
                                <select id="filter_periode" class="selectpicker form-control wide form-select-md"
                                    data-live-search="true" title="Pilih Bulan" data-size="5">
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

                            <!-- Filter Status -->
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Status</label>
                                <select id="filter_status" class="selectpicker form-control wide form-select-md"
                                    data-live-search="false" title="Filter Status">
                                    <option value="">Semua Status</option>
                                    <option value="belum_dilaporkan">Belum Dilaporkan</option>
                                    <option value="draft">Draft</option>
                                    <option value="submitted">Menunggu Review</option>
                                    <option value="revision">Perlu Revisi</option>
                                    <option value="approved">Disetujui</option>
                                    <option value="rejected">Ditolak</option>
                                </select>
                            </div>

                            <!-- Filter Desa -->
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Desa</label>
                                <select id="filter_desa" class="selectpicker form-control wide form-select-md"
                                    data-live-search="true" title="Filter Desa" data-size="5">
                                    <option value="">Semua Desa</option>
                                    @foreach ($desaList as $item)
                                        <option value="{{ $item->id_desa }}">
                                            {{ $item->nama_desa }} | {{ $item->nama_kecamatan }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Reset Button -->
                            <div class="col-md-2 d-flex justify-content-end">
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
                                        <th style="width: 10%;" class="align-middle">Aksi</th>
                                        <th style="width: 8%;" class="text-start align-middle">Tahun</th>
                                        <th style="width: 10%;" class="text-start align-middle">Periode</th>
                                        <th style="width: 15%;" class="text-start align-middle">Desa</th>
                                        <th style="width: 25%;" class="text-start align-middle">Kegiatan</th>
                                        <th style="width: 17%;" class="text-center align-middle">Timeline</th>
                                        <th style="width: 10%;" class="text-center align-middle">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-800 fw-bolder fs-sm-8 fs-lg-6">
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="las la-folder-open fs-1 d-block mb-2"></i>
                                            Tidak ada laporan kegiatan yang tercatat saat ini.
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
                                <li>Klik tombol <span class="text-primary fw-semibold">“Detail”</span> pada kolom aksi untuk
                                    melihat rincian laporan.</li>
                                <li>Data pada halaman ini bersifat historis dan digunakan untuk pemantauan perkembangan
                                    kegiatan desa.</li>
                            </ul>
                        </div>

                        <!-- Deskripsi Halaman -->
                        <div class="alert alert-primary mb-4">
                            <strong>Tentang Halaman Ini:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Halaman ini merupakan bagian dari <strong>Monitoring</strong> dengan sub-menu
                                    <strong>Riwayat Laporan Kegiatan</strong>.
                                </li>
                                <li>Digunakan untuk melihat daftar laporan kegiatan yang telah dibuat oleh desa di wilayah
                                    kecamatan Anda.</li>
                                <li>Status laporan menunjukkan kondisi terakhir dari proses pelaporan.</li>
                                <li>Status <span class="badge badge-sm badge-success">Disetujui</span> menandakan laporan
                                    telah diverifikasi.</li>
                            </ul>
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

    @include('administration.monitoring.riwayat.scripts.datatable-init')
@endsection
