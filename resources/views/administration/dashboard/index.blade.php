@extends('administration.layouts.app')

@section('this-page-style')
    <link href="{{ asset('templates/administration/vendor/simple-datatables/style.css') }}" rel="stylesheet">
@endsection

@section('content')
    <!-- Content body start -->
    <div class="pagetitle">
        <h1>Dashboard</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Admin</a></li>
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
        <div class="row justify-content-center g-4">

            <!-- Desa Belum Laporan -->
            <div class="col-xxl-2 col-md-4">
                <div class="card info-card sales-card">
                    <div class="card-body">
                        <h5 class="card-title">Desa <span>| Belum Laporan</span></h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-exclamation-triangle"></i> <!-- ikon warning -->
                            </div>
                            <div class="ps-3">
                                <h6>80</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- End Card -->

            <!-- Kec Belum Tuntas -->
            <div class="col-xxl-2 col-md-4">
                <div class="card info-card sales-card">
                    <div class="card-body">
                        <h5 class="card-title">Kecamatan <span>| Belum Tuntas</span></h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-building"></i> <!-- ikon gedung utk kecamatan -->
                            </div>
                            <div class="ps-3">
                                <h6>6</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- End Card -->

            <!-- Desa Belum Laporan Tahun Sebelumnya -->
            <div class="col-xxl-3 col-md-4">
                <div class="card info-card sales-card">
                    <div class="card-body">
                        <h5 class="card-title">Desa <span>| Tahun Sebelumnya</span></h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-clock-history"></i> <!-- ikon histori utk tahun lalu -->
                            </div>
                            <div class="ps-3">
                                <h6>76</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- End Card -->

            <!-- Desa Belum Laporan Tahun Berjalan -->
            <div class="col-xxl-3 col-md-4">
                <div class="card info-card sales-card">
                    <div class="card-body">
                        <h5 class="card-title">Desa <span>| Tahun Berjalan</span></h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-calendar-check"></i> <!-- ikon kalender aktif -->
                            </div>
                            <div class="ps-3">
                                <h6>5</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- End Card -->

            <!-- Desa Belum Laporan Tahun Berikutnya -->
            <div class="col-xxl-3 col-md-4">
                <div class="card info-card sales-card">
                    <div class="card-body">
                        <h5 class="card-title">Desa <span>| Tahun Berikutnya</span></h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-calendar-event"></i> <!-- ikon kalender event utk tahun depan -->
                            </div>
                            <div class="ps-3">
                                <h6>8</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- End Card -->

        </div>


        <!-- Row untuk tabel -->
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Desa Belum Laporan</h5>
                        <div class="d-flex justify-content-between mb-2">
                            <select class="form-select form-select-sm" style="width: auto;">
                                <option>Show 5 rows</option>
                                <option>Show 10 rows</option>
                            </select>
                            <input type="text" class="form-control form-control-sm" placeholder="search"
                                style="width: 150px;">
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Desa</th>
                                        <th>Kecamatan</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>Karangan</td>
                                        <td>Paiton</td>
                                        <td>Aktif</td>
                                        <td><a href="#">Edit</a> | <a href="#">Hapus</a></td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>Siddodadi</td>
                                        <td>Paiton</td>
                                        <td>Aktif</td>
                                        <td><a href="#">Edit</a> | <a href="#">Hapus</a></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item disabled"><a class="page-link">Previous</a></li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">Next</a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div><!-- End Table -->

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Desa Belum Laporan Tahun Sebelumnya</h5>
                        <div class="d-flex justify-content-between mb-2">
                            <select class="form-select form-select-sm" style="width: auto;">
                                <option>Show 5 rows</option>
                                <option>Show 10 rows</option>
                            </select>
                            <input type="text" class="form-control form-control-sm" placeholder="search"
                                style="width: 150px;">
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Desa</th>
                                        <th>Kecamatan</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>Karangan</td>
                                        <td>Paiton</td>
                                        <td>Aktif</td>
                                        <td><a href="#">Edit</a> | <a href="#">Hapus</a></td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>Siddodadi</td>
                                        <td>Paiton</td>
                                        <td>Aktif</td>
                                        <td><a href="#">Edit</a> | <a href="#">Hapus</a></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item disabled"><a class="page-link">Previous</a></li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">Next</a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div><!-- End Table -->
        </div>
    </section>

    <!-- Content body end -->
@endsection

@section('this-page-scripts')
    <script src="{{ asset('templates/administration/vendor/simple-datatables/simple-datatables.js') }}"></script>
@endsection
