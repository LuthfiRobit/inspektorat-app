<div class="row">
    <!-- Header Summary - Compact Version -->
    <div class="col-xl-12 mb-3">
        <div class="card border-0 bg-light">
            <div class="card-body-compact">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                    <div class="flex-grow-1">
                        <h5 class="mb-1 fw-bold">Detail Scoring Kecamatan: {{ $kecamatan->nama_kecamatan }}</h5>
                        <div class="d-flex align-items-center gap-2 text-muted small">
                            <i class="las la-building"></i>
                            <span>Breakdown per Desa</span>
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
                        <small class="text-muted d-block" style="font-size: 0.75rem;">Total Skor Akumulasi</small>
                        <div class="mt-1">
                            <span class="badge bg-light text-dark border" style="font-size: 0.75rem;">
                                Rata-rata: {{ number_format($summary['rata_rata_skor'], 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards - Compact Version in 3 Columns -->
    <div class="col-md-4 mb-3">
        <div class="card bg-info bg-opacity-10 border-primary h-100">
            <div class="card-body-compact">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-25 rounded p-2 me-3"
                        style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                        <i class="las la-map-marked text-white" style="font-size: 1.5rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <label class="info-label mb-0 text-primary">Total Desa</label>
                        <div class="d-flex align-items-baseline">
                            <h4 class="mb-0 fw-bold text-dark me-2">{{ $summary['total_desa'] }}</h4>
                            <small class="text-muted">Desa Valid</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card bg-success bg-opacity-10 border-success h-100">
            <div class="card-body-compact">
                <div class="d-flex align-items-center">
                    <div class="bg-success bg-opacity-25 rounded p-2 me-3"
                        style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                        <i class="las la-check-double text-success" style="font-size: 1.5rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <label class="info-label mb-0 text-success">Total Terlapor</label>
                        <div class="d-flex align-items-baseline">
                            <h4 class="mb-0 fw-bold text-success me-2">{{ $summary['total_kegiatan_terlapor'] }}</h4>
                            <small class="text-muted">Kegiatan</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card bg-danger bg-opacity-10 border-danger h-100">
            <div class="card-body-compact">
                <div class="d-flex align-items-center">
                    <div class="bg-danger bg-opacity-25 rounded p-2 me-3"
                        style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                        <i class="las la-exclamation-triangle text-danger" style="font-size: 1.5rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <label class="info-label mb-0 text-danger">Belum Terlapor</label>
                        <div class="d-flex align-items-baseline">
                            <h4 class="mb-0 fw-bold text-danger me-2">{{ $summary['total_kegiatan_belum'] }}</h4>
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
                    <i class="las la-trophy me-2"></i>
                    Ranking Per Desa
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 0.85rem;">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th class="text-center align-middle py-2" style="width: 10%;">Peringkat</th>
                                <th class="align-middle py-2" style="width: 30%;">Nama Desa</th>
                                <th class="text-center align-middle py-2" style="width: 15%;">Total Skor</th>
                                <th class="text-center align-middle py-2" style="width: 15%;">Terlapor</th>
                                <th class="text-center align-middle py-2" style="width: 15%;">Belum</th>
                                <th class="text-center align-middle py-2" style="width: 15%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($desas as $item)
                                <tr>
                                    <td class="text-center py-2">
                                        {!! $item->peringkat_badge !!}
                                    </td>
                                    <td class="py-2">
                                        <div class="d-flex align-items-center">
                                            <i class="las la-map-marker-alt text-muted me-2" style="font-size: 1.1rem;"></i>
                                            <span class="fw-semibold">{{ $item->nama_desa }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center py-2">
                                        <span class="fw-bold fs-5 text-primary">{{ $item->total_skor_formatted }}</span>
                                    </td>
                                    <td class="text-center py-2">
                                        <span class="badge bg-success" style="font-size: 0.75rem;">
                                            {{ $item->kegiatan_terlapor }}
                                        </span>
                                    </td>
                                    <td class="text-center py-2">
                                        <span class="badge bg-danger" style="font-size: 0.75rem;">
                                            {{ $item->kegiatan_belum_terlapor }}
                                        </span>
                                    </td>
                                    <td class="text-center py-2">
                                        <button class="btn btn-sm btn-outline-info"
                                            onclick="showDesaDetail({{ $item->id_desa }})" style="font-size: 0.75rem;">
                                            <i class="las la-search me-1"></i> Detail
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="las la-inbox" style="font-size: 2rem;"></i>
                                        <div class="small">Tidak ada data desa.</div>
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

    /* Responsive badge peringkat */
    .table tbody td .badge {
        min-width: 50px;
    }
</style>