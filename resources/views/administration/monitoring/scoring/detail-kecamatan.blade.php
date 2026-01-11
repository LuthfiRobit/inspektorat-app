<div class="row">
    <!-- Header Summary -->
    <div class="col-xl-12 mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h4 class="mb-1">Detail Scoring Kecamatan: {{ $kecamatan->nama_kecamatan }}</h4>
                <span class="text-muted">Breakdown per Desa</span>
                @if (isset($filters['tahun']) || isset($filters['periode']))
                    <br>
                    <small class="text-primary">
                        Periode:
                        {{ $filters['periode'] ? \Carbon\Carbon::create()->month($filters['periode'])->format('F') : 'Semua Bulan' }}
                    </small>
                @endif
            </div>
            <div class="text-end">
                <h2 class="mb-0 text-primary">{{ number_format($summary['total_skor'], 2) }}</h2>
                <small class="text-muted">Total Skor Akumulasi</small>
                <div class="mt-1">
                    <span class="badge badge-light text-dark border">
                        Rata-rata: {{ number_format($summary['rata_rata_skor'], 2) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card bg-light h-100 border-0">
            <div class="card-body py-3">
                <h6 class="mb-2">Total Desa</h6>
                <div class="d-flex align-items-end">
                    <h3 class="mb-0 text-dark">{{ $summary['total_desa'] }}</h3>
                    <small class="ms-2 text-muted">Desa Valid</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card bg-light h-100 border-0">
            <div class="card-body py-3">
                <h6 class="mb-2">Total Kegiatan Terlapor</h6>
                <div class="d-flex align-items-end">
                    <h3 class="mb-0 text-success">{{ $summary['total_kegiatan_terlapor'] }}</h3>
                    <small class="ms-2 text-muted">Kegiatan (All Desa)</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card bg-light h-100 border-0">
            <div class="card-body py-3">
                <h6 class="mb-2">Total Belum Terlapor</h6>
                <div class="d-flex align-items-end">
                    <h3 class="mb-0 text-danger">{{ $summary['total_kegiatan_belum'] }}</h3>
                    <small class="ms-2 text-muted">Kegiatan (All Desa)</small>
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
                        <th class="text-center align-middle" width="10%">Peringkat</th>
                        <th class="align-middle" width="30%">Nama Desa</th>
                        <th class="text-center align-middle" width="15%">Total Skor</th>
                        <th class="text-center align-middle" width="15%">Terlapor</th>
                        <th class="text-center align-middle" width="15%">Belum</th>
                        <th class="text-center align-middle" width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($desas as $item)
                        <tr>
                            <td class="text-center">
                                {!! $item->peringkat_badge !!}
                            </td>
                            <td>
                                <span class="fw-bold d-block">{{ $item->nama_desa }}</span>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold fs-5">{{ $item->total_skor_formatted }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-success">{{ $item->kegiatan_terlapor }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-danger">{{ $item->kegiatan_belum_terlapor }}</span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-xs btn-outline-info" onclick="showDesaDetail({{ $item->id_desa }})">
                                    <i class="fa fa-search me-1"></i> Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Tidak ada data desa.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>