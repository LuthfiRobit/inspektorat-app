@extends('administration.layouts.app')

@section('this-page-style')
    <link href="{{ asset('templates/administration/vendor/datatables/css/jquery.dataTables.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('templates/administration/vendor/datatables/responsive/responsive.css') }}" rel="stylesheet" />
    <style>
        .apexcharts-canvas {
            margin: 0 auto;
        }
    </style>
@endsection

@section('content')
    <div class="content-body default-height">
        <div class="container-fluid">

            <!-- Heading -->
            <div class="form-head mb-4 d-flex align-items-center gap-2">
                <small class="text-muted">Laporan -</small>
                <h4 class="text-dark fw-semibold mb-0">Visualisasi Monitoring</h4>
            </div>

            <!-- Filter -->
            <div class="card mb-4">
                <div class="card-header border-0 pb-0">
                    <h4 class="fs-20 text-black mb-1">Filter Visualisasi</h4>
                </div>
                <div class="card-body">
                    <form id="filterForm" method="GET" class="row gy-3 gx-3 align-items-start">
                        <div class="col-md-3">
                            <label for="filter_tahun" class="form-label">Tahun</label>
                            <select id="filter_tahun" name="filter_tahun" class="selectpicker form-control wide">
                                <option value="">Semua Tahun</option>
                                @for ($i = date('Y'); $i >= 2020; $i--)
                                    <option value="{{ $i }}"
                                        {{ request('filter_tahun') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filter_kecamatan" class="form-label">Kecamatan</label>
                            <select id="filter_kecamatan" name="filter_kecamatan" class="selectpicker form-control wide"
                                data-live-search="true">
                                <option value="">Semua Kecamatan</option>
                                <option value="1">Kecamatan A</option>
                                <option value="2">Kecamatan B</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filter_kategori" class="form-label">Kategori</label>
                            <select id="filter_kategori" name="filter_kategori" class="selectpicker form-control wide">
                                <option value="">Semua Kategori</option>
                                <option value="keuangan" {{ request('filter_kategori') == 'keuangan' ? 'selected' : '' }}>
                                    Keuangan</option>
                                <option value="kinerja" {{ request('filter_kategori') == 'kinerja' ? 'selected' : '' }}>
                                    Kinerja</option>
                                <option value="audit" {{ request('filter_kategori') == 'audit' ? 'selected' : '' }}>Audit
                                </option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filter_jenis" class="form-label">Jenis</label>
                            <select id="filter_jenis" name="filter_jenis" class="selectpicker form-control wide">
                                <option value="">Semua Jenis</option>
                                <option value="bulanan">Bulanan</option>
                                <option value="triwulan">Triwulan</option>
                                <option value="semester">Semester</option>
                                <option value="tahunan">Tahunan</option>
                            </select>
                        </div>

                        <!-- Tombol -->
                        <div class="col-12 d-flex justify-content-end mt-3">
                            <button type="button" class="btn btn-sm btn-secondary me-2" onclick="location.reload();">
                                <i class="las la-redo-alt me-1"></i> Muat Ulang
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="las la-filter me-1"></i> Terapkan Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Grafik -->
            <div class="row">
                <!-- Grafik 1: Bar -->
                <div class="col-xl-6 mb-4">
                    <div class="card">
                        <div class="card-header border-0 pb-0">
                            <h4 class="fs-20 text-black mb-0">Laporan per Tahun</h4>
                        </div>
                        <div class="card-body">
                            <div id="chartBarTahun"></div>
                        </div>
                    </div>
                </div>

                <!-- Grafik 2: Donut -->
                <div class="col-xl-6 mb-4">
                    <div class="card">
                        <div class="card-header border-0 pb-0">
                            <h4 class="fs-20 text-black mb-0">Distribusi Kategori Laporan</h4>
                        </div>
                        <div class="card-body">
                            <div id="chartDonutKategori"></div>
                        </div>
                    </div>
                </div>

                <!-- Grafik 3: Area -->
                <div class="col-xl-6 mb-4">
                    <div class="card">
                        <div class="card-header border-0 pb-0">
                            <h4 class="fs-20 text-black mb-0">Tren Laporan per Bulan</h4>
                        </div>
                        <div class="card-body">
                            <div id="chartAreaBulanan"></div>
                        </div>
                    </div>
                </div>

                <!-- Grafik 4: Radial -->
                <div class="col-xl-6 mb-4">
                    <div class="card">
                        <div class="card-header border-0 pb-0">
                            <h4 class="fs-20 text-black mb-0">Capaian Monitoring Desa</h4>
                        </div>
                        <div class="card-body">
                            <div id="chartRadialCapaian"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('this-page-scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        // Grafik 1: Bar - Laporan per Tahun
        new ApexCharts(document.querySelector("#chartBarTahun"), {
            chart: {
                type: 'bar',
                height: 300
            },
            series: [{
                name: 'Laporan',
                data: [100, 130, 180, 120, 150]
            }],
            xaxis: {
                categories: ['2021', '2022', '2023', '2024', '2025']
            },
            colors: ['#4e73df'],
            dataLabels: {
                enabled: true
            }
        }).render();

        // Grafik 2: Donut - Distribusi Kategori
        new ApexCharts(document.querySelector("#chartDonutKategori"), {
            chart: {
                type: 'donut',
                height: 300
            },
            series: [45, 35, 20],
            labels: ['Keuangan', 'Kinerja', 'Audit'],
            colors: ['#1cc88a', '#36b9cc', '#f6c23e'],
            legend: {
                position: 'bottom'
            }
        }).render();

        // Grafik 3: Area - Tren Laporan per Bulan
        new ApexCharts(document.querySelector("#chartAreaBulanan"), {
            chart: {
                type: 'area',
                height: 300
            },
            series: [{
                name: 'Laporan Masuk',
                data: [20, 35, 40, 55, 60, 45, 30, 25, 50, 65, 70, 80]
            }],
            xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']
            },
            colors: ['#6f42c1'],
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth'
            }
        }).render();

        // Grafik 4: Radial Bar - Capaian Monitoring Desa
        new ApexCharts(document.querySelector("#chartRadialCapaian"), {
            chart: {
                type: 'radialBar',
                height: 300
            },
            series: [76],
            labels: ['Capaian'],
            colors: ['#fd7e14'],
            plotOptions: {
                radialBar: {
                    dataLabels: {
                        name: {
                            fontSize: '18px'
                        },
                        value: {
                            fontSize: '24px'
                        },
                        total: {
                            show: true,
                            label: 'Target',
                            formatter: function() {
                                return '100%'
                            }
                        }
                    }
                }
            }
        }).render();
    </script>
@endsection
