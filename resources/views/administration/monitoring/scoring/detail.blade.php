<div class="row">
    <!-- Header Summary - Compact Version -->
    <div class="col-xl-12 mb-3">
        <div class="card border-0 bg-light">
            <div class="card-body-compact">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                    <div class="flex-grow-1">
                        <h5 class="mb-1 fw-bold">Detail Scoring: {{ $desa->nama_desa }}</h5>
                        <div class="d-flex align-items-center gap-2 text-muted small">
                            <i class="las la-map-marker"></i>
                            <span>{{ $desa->kecamatan->nama_kecamatan }}</span>
                        </div>
                        @if (isset($filters['tahun']) || isset($filters['periode']))
                            <div class="mt-2">
                                <span class="badge bg-primary" style="font-size: 0.75rem;">
                                    <i class="las la-calendar me-1"></i>
                                    Periode:
                                    {{ $filters['periode'] ? \Carbon\Carbon::create()->month((int) $filters['periode'])->format('F') : 'Semua Bulan' }}
                                </span>
                            </div>
                        @endif
                    </div>
                    <div class="text-end">
                        <h2 class="mb-0 text-primary fw-bold">{{ number_format($summary['total_skor'], 2) }}</h2>
                        <small class="text-muted" style="font-size: 0.75rem;">Total Skor</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards - Compact Version -->
    <div class="col-md-6 mb-3">
        <div class="card bg-success bg-opacity-10 border-success h-100">
            <div class="card-body-compact">
                <div class="d-flex align-items-center">
                    <div class="bg-success bg-opacity-25 rounded p-2 me-3"
                        style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                        <i class="las la-check-circle text-success" style="font-size: 1.5rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <label class="info-label mb-0 text-success">Kegiatan Terlapor</label>
                        <div class="d-flex align-items-baseline">
                            <h4 class="mb-0 fw-bold text-success me-2">{{ $summary['terlapor'] }}</h4>
                            <small class="text-muted">/ {{ $summary['total_kegiatan'] }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card bg-danger bg-opacity-10 border-danger h-100">
            <div class="card-body-compact">
                <div class="d-flex align-items-center">
                    <div class="bg-danger bg-opacity-25 rounded p-2 me-3"
                        style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                        <i class="las la-times-circle text-danger" style="font-size: 1.5rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <label class="info-label mb-0 text-danger">Belum Terlapor</label>
                        <div class="d-flex align-items-baseline">
                            <h4 class="mb-0 fw-bold text-danger me-2">{{ $summary['belum_terlapor'] }}</h4>
                            <small class="text-muted">Kegiatan</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Table - Compact Version -->
    <div class="col-12">
        <div class="card">
            <div class="card-header-compact">
                <h6 class="mb-0 fw-bold d-flex align-items-center">
                    <i class="las la-list me-2"></i>
                    Detail Per Kegiatan
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 0.85rem;">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th class="text-center align-middle py-2" style="width: 5%;">No</th>
                                <th class="align-middle py-2" style="width: 25%;">Kegiatan</th>
                                <th class="text-center align-middle py-2" style="width: 10%;">Status</th>
                                <th class="align-middle py-2" style="width: 30%;">Ketepatan Waktu</th>
                                <th class="text-center align-middle py-2" style="width: 10%;">Dok. Wajib</th>
                                <th class="text-center align-middle py-2" style="width: 10%;">Dok. Tambahan</th>
                                <th class="text-center align-middle py-2" style="width: 10%;">Skor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($details as $index => $item)
                                <tr>
                                    <td class="text-center py-2">{{ $index + 1 }}</td>
                                    <td class="py-2">
                                        <span class="fw-semibold d-block">{{ $item->nama_kegiatan }}</span>
                                    </td>
                                    <td class="text-center py-2">
                                        @if(in_array($item->status, ['submitted', 'approved']))
                                            <span class="badge bg-success" style="font-size: 0.7rem;">Terlapor</span>
                                        @else
                                            <span class="badge bg-secondary" style="font-size: 0.7rem;">Belum</span>
                                        @endif
                                    </td>
                                    <td class="py-2">
                                        <div class="small">
                                            <div class="mb-1">
                                                <i class="las la-calendar-check text-muted" style="font-size: 0.9rem;"></i>
                                                <span class="text-muted" style="font-size: 0.7rem;">Target:</span>
                                                <span class="fw-semibold">{{ $item->tanggal_target }}</span>
                                            </div>
                                            @if($item->tanggal_submit !== '-')
                                                <div class="mb-1 {{ $item->late_days > 0 ? 'text-danger' : 'text-success' }}">
                                                    <i class="las la-paper-plane" style="font-size: 0.9rem;"></i>
                                                    <span style="font-size: 0.7rem;">Submit:</span>
                                                    <span class="fw-semibold">{{ $item->tanggal_submit }}</span>
                                                </div>
                                                @if($item->late_days > 0)
                                                    <div class="text-danger">
                                                        <i class="las la-exclamation-circle" style="font-size: 0.9rem;"></i>
                                                        <span class="fw-bold" style="font-size: 0.75rem;">Terlambat
                                                            {{ $item->late_days }} hari</span>
                                                        <small class="d-block ms-3">(Penalti: -{{ $item->late_days * 10 }})</small>
                                                    </div>
                                                @else
                                                    <div class="text-success">
                                                        <i class="las la-check-circle" style="font-size: 0.9rem;"></i>
                                                        <span style="font-size: 0.75rem;">Tepat Waktu</span>
                                                    </div>
                                                @endif
                                            @else
                                                <div class="text-muted">
                                                    <i class="las la-minus-circle" style="font-size: 0.9rem;"></i>
                                                    <span style="font-size: 0.75rem;">Belum Submit</span>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center py-2">
                                        <span class="fw-bold">{{ $item->doc_wajib_approved }}</span>
                                        <small class="text-muted d-block" style="font-size: 0.7rem;">File</small>
                                    </td>
                                    <td class="text-center py-2">
                                        <span class="fw-bold">{{ $item->doc_tambahan_approved }}</span>
                                        <small class="text-muted d-block" style="font-size: 0.7rem;">File</small>
                                    </td>
                                    <td class="text-center py-2">
                                        <span class="fw-bold fs-5 text-primary">{{ $item->timeliness_score }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="las la-inbox" style="font-size: 2rem;"></i>
                                        <div class="small">Tidak ada data kegiatan.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card-header-compact {
        padding: 0.75rem 1rem;
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }

    .card-body-compact {
        padding: 1rem;
    }

    .info-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        font-weight: 600;
    }

    .table thead th {
        font-size: 0.8rem;
        font-weight: 600;
    }

    .table tbody td {
        vertical-align: middle;
    }
</style>