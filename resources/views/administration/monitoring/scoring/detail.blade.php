<div class="row">
    <!-- Header Summary -->
    <div class="col-xl-12 mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h4 class="mb-1">Detail Scoring: {{ $desa->nama_desa }}</h4>
                <span class="text-muted">{{ $desa->kecamatan->nama_kecamatan }}</span>
                @if (isset($filters['tahun']) || isset($filters['periode']))
                    <br>
                    <small class="text-primary">
                        Periode:
                        {{ $filters['periode'] ? \Carbon\Carbon::create()->month($filters['periode'])->format('F') : 'Semua Bulan' }}
                        {{-- {{ $filters['tahun'] ? 'Tahun ' . $filters['tahun'] : '' }} --}}
                        <!-- Tahun info might need relational fetch if just ID passed, but context usually implies it -->
                    </small>
                @endif
            </div>
            <div class="text-end">
                <h2 class="mb-0 text-primary">{{ number_format($summary['total_skor'], 2) }}</h2>
                <small class="text-muted">Total Skor</small>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card bg-light h-100 border-0">
            <div class="card-body py-3">
                <h6 class="mb-2">Kegiatan Terlapor</h6>
                <div class="d-flex align-items-end">
                    <h3 class="mb-0 text-success">{{ $summary['terlapor'] }}</h3>
                    <small class="ms-2 text-muted">/ {{ $summary['total_kegiatan'] }}</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card bg-light h-100 border-0">
            <div class="card-body py-3">
                <h6 class="mb-2">Belum Terlapor</h6>
                <div class="d-flex align-items-end">
                    <h3 class="mb-0 text-danger">{{ $summary['belum_terlapor'] }}</h3>
                    <small class="ms-2 text-muted">Kegiatan</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Table -->
    <div class="col-12">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle">
                <thead class="bg-primary text-white">
                    <tr>
                        <th class="text-center align-middle" width="5%">No</th>
                        <th class="align-middle" width="25%">Kegiatan</th>
                        <th class="text-center align-middle" width="10%">Status</th>
                        <th class="align-middle" width="30%">Ketepatan Waktu</th>
                        <th class="text-center align-middle" width="10%">Dok. Wajib</th>
                        <th class="text-center align-middle" width="10%">Dok. Tambahan</th>
                        <th class="text-center align-middle" width="10%">Skor</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($details as $index => $item)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>
                                <span class="fw-bold d-block">{{ $item->nama_kegiatan }}</span>
                            </td>
                            <td class="text-center">
                                @if(in_array($item->status, ['submitted', 'approved']))
                                    <span class="badge badge-success">Terlapor</span>
                                @else
                                    <span class="badge badge-light text-muted">Belum</span>
                                @endif
                            </td>
                            <td>
                                <div class="small">
                                    <div><i class="fa fa-calendar-check me-1 text-muted"></i> Target:
                                        {{ $item->tanggal_target }}</div>
                                    @if($item->tanggal_submit !== '-')
                                        <div class="{{ $item->late_days > 0 ? 'text-danger fw-bold' : 'text-success' }}">
                                            <i class="fa fa-paper-plane me-1"></i> Submit: {{ $item->tanggal_submit }}
                                        </div>
                                        @if($item->late_days > 0)
                                            <div class="text-danger mt-1">
                                                <i class="fa fa-exclamation-circle me-1"></i> Terlambat {{ $item->late_days }} hari
                                                <br>
                                                <small>(Penalti: -{{ $item->late_days * 0.1 }})</small>
                                            </div>
                                        @else
                                            <div class="text-success mt-1"><i class="fa fa-check-circle me-1"></i> Tepat Waktu</div>
                                        @endif
                                    @else
                                        <div class="text-muted"><i class="fa fa-minus-circle me-1"></i> Belum Submit</div>
                                    @endif
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold">{{ $item->doc_wajib_approved }}</span> File
                            </td>
                            <td class="text-center">
                                <span class="fw-bold">{{ $item->doc_tambahan_approved }}</span> File
                            </td>
                            <td class="text-center">
                                <span class="fw-bold fs-5">{{ $item->timeliness_score }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Tidak ada data kegiatan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>