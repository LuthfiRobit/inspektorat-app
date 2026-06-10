@extends('administration.layouts.app')

@section('title', 'Monitoring | Penarikan Data Laporan Kegiatan')
@section(
    'meta-description',
    'Halaman penarikan data laporan kegiatan desa untuk menampilkan dan mengunduh data berdasarkan filter yang dipilih.'
)

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
                <h4 class="text-dark fw-semibold mb-0">Penarikan Data Laporan Kegiatan</h4>
            </div>

            <div class="row">
                <div class="card">
                    <div class="card-header d-sm-flex d-block border-0 pb-0 flex-wrap">
                        <div class="pr-3 me-auto mb-sm-0 mb-3">
                            <h4 class="fs-20 text-black mb-1">Penarikan & Unduh Data Laporan Kegiatan Desa</h4>
                            <span class="fs-12 text-muted">
                                Gunakan filter di bawah ini untuk menampilkan data pada tabel. 
                                Setelah data sesuai, Anda dapat menarik data dalam format Excel.
                            </span>
                        </div>
                    </div>

                    <div class="card-body">

                        <!-- INFORMASI -->
                        <div class="alert alert-warning mb-4">
                            <strong>Perhatian:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Data tidak akan ditampilkan sebelum Anda menggunakan filter.</li>

                                <li>Untuk menarik data Excel, wajib memilih <strong>Tahun Anggaran</strong>, <strong>Bul
                                an Awal</strong>, dan <strong>Status Laporan</strong>.</li>
                                <li>Bulan Akhir bersifat opsional, namun jika dipilih harus lebih besar dari Bulan Awal.</li>
                            </ul>
                        </div>

                        <!-- FILTER SECTION -->
                        <div class="row g-3 mb-4 align-items-end">

                            <!-- Tahun -->
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Tahun Anggaran</label>
                                <select id="filter_tahun" class="selectpicker form-control wide form-select-md"
                                    data-live-search="true" title="Pilih Tahun" data-size="5">
                                @foreach ($tahunAnggaranList as $tahun)
                                    <option value="{{ $tahun->id_tahun_anggaran }}">
                                        {{ $tahun->tahun }} 
                                        @if ($tahun->status === 'aktif') (Aktif) @endif
                                        </option>
                                @endforeach
                                </select>
                            </div>

                            <!-- Bulan Awal -->
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Bulan Awal</label>
                                <select id="filter_bulan_awal" class="selectpicker form-control wide form-select-md"
                                    title="Pilih Bulan Awal">
                                    <option value="">Pilih Bulan</option>
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}">
                                        {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                        </option>
                                @endfor
                                </select>
                            </div>

                            <!-- Bulan Akhir -->
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Bulan Akhir</label>
                                <select id="filter_bulan_akhir" class="selectpicker form-control wide form-select-md"
                                    title="Opsional">
                                    <option value="">Opsional</option>
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}">
                                        {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                        </option>
                                @endfor
                                </select>
                            </div>

                            <!-- Status -->
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Status Laporan</label>
                                <select id="filter_status" class="selectpicker form-control wide form-select-md"
                                    title="Pilih Status">
                                    <option value="belum_dilaporkan">Belum Dilaporkan</option>
                                    <option value="draft">Draft</option>
                                    <option value="submitted">Menunggu Review</option>
                                    <option value="revision">Perlu Revisi</option>
                                    <option value="approved">Disetujui</option>
                                    <option value="rejected">Ditolak</option>
                                </select>
                            </div>

                            <!-- BUTTONS -->
                            <div class="col-md-3 d-flex justify-content-end gap-2">
                                <button id="btnResetFilter" type="button" class="btn btn-outline-secondary">
                                    <i class="fas fa-sync-alt me-1"></i> Reset
                                </button>
                                @if (auth()->user()->hasPermissionTo('monitoring.tarik-data.view'))
                                <button id="btnTarikData" type="button" class="btn btn-success">
                                    <i class="fas fa-file-excel me-1"></i> Tarik Data
                                </button>
                                @endif
                            </div>

                        </div>

                        <!-- TABLE -->
                        <div class="table-responsive">
                            <table id="example" class="table table-sm align-middle table-striped gs-0 gy-2 nowrap"
                                style="width:100%;">
                                <thead>
                                    <tr class="text-center text-muted text-uppercase">
                                        <th>Tahun</th>
                                        <th>Periode</th>
                                        <th>Kecamatan</th>
                                        <th>Desa</th>
                                        <th>Jenis Kegiatan</th>
                                        <th>Nama Kegiatan</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>

                        <!-- DESKRIPSI -->
                        <div class="alert alert-primary mt-4">
                            <strong>Tentang Penarikan Data:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Data yang ditarik akan mengikuti filter yang Anda pilih.</li>
                                <li>File yang dihasilkan berbentuk <strong>Excel (.xlsx)</strong>.</li>
                                <li>Pastikan filter telah sesuai sebelum melakukan proses penarikan data.</li>
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

    <script>
        $(document).ready(function() {
            let dt = $('#example').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('administrator.monitoring.tarik-data.list') }}",
                    data: function(d) {
                        d.tahun = $('#filter_tahun').val();
                        d.bulan_awal = $('#filter_bulan_awal').val();
                        d.bulan_akhir = $('#filter_bulan_akhir').val();
                        d.status = $('#filter_status').val();
                    }
                },
                columns: [
                    { data: 'tahun', name: 'tahun' },
                    { data: 'bulan', name: 'bulan' },
                    { data: 'nama_kecamatan', name: 'nama_kecamatan' },
                    { data: 'nama_desa', name: 'nama_desa' },
                    { data: 'jenis_kegiatan', name: 'jenis_kegiatan' },
                    { data: 'nama_kegiatan', name: 'nama_kegiatan' },
                    { data: 'status_display', name: 'status_display' },
                ],
                language: {
                    emptyTable: "Gunakan filter terlebih dahulu untuk menampilkan data.",
                    infoEmpty: "Menampilkan 0 data",
                    zeroRecords: "Data tidak ditemukan"
                }
            });

            function validasiBulan() {
                let awal = parseInt($('#filter_bulan_awal').val());
                let akhir = parseInt($('#filter_bulan_akhir').val());

                if (awal && akhir && akhir <= awal) {
                    alert('Bulan akhir harus lebih besar dari bulan awal.');
                    $('#filter_bulan_akhir').val('');
                    $('.selectpicker').selectpicker('refresh');
                    return false;
                }
                return true;
            }

            $('#filter_bulan_akhir').on('change', function() {
                validasiBulan();
            });

            $('#filter_tahun, #filter_bulan_awal, #filter_bulan_akhir, #filter_status').on('change', function() {
                // Fetch data automatically when required fields are filled
                if ($('#filter_tahun').val() && $('#filter_bulan_awal').val() && $('#filter_status').val()) {
                    if (validasiBulan()) {
                        dt.ajax.reload();
                    }
                }
            });

            $('#btnTarikData').on('click', function() {
                let tahun = $('#filter_tahun').val();
                let bulanAwal = $('#filter_bulan_awal').val();
                let status = $('#filter_status').val();

                if (!tahun || !bulanAwal || !status) {
                    alert('Tahun Anggaran, Bulan Awal, dan Status Laporan wajib dipilih.');
                    return;
                }

                if (!validasiBulan()) return;

                let exportUrl = `{{ route('administrator.monitoring.tarik-data.export') }}?tahun=${tahun}&bulan_awal=${bulanAwal}&bulan_akhir=${$('#filter_bulan_akhir').val()}&status=${status}`;
                window.location.href = exportUrl;
            });

            $('#btnResetFilter').on('click', function() {
                $('.selectpicker').val('');
                $('.selectpicker').selectpicker('refresh');
                dt.ajax.reload();
            });
        });
    </script>
@endsection