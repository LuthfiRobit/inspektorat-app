@extends('administration.layouts.app')

@section('this-page-style')
    <link href="{{ asset('templates/administration/vendor/datatables/css/jquery.dataTables.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('templates/administration/vendor/datatables/responsive/responsive.css') }}" rel="stylesheet" />
    @stack('styles')
@endsection

@section('content')
    <div class="content-body default-height">
        <div class="container-fluid">
            <div class="form-head mb-4 d-flex justify-content-between align-items-center">
                <!-- Bagian Kiri: Judul -->
                <div class="d-flex align-items-center gap-2">
                    <small class="text-muted">Setting Monev -</small>
                    <h4 class="text-dark fw-semibold mb-0">Laporan</h4>
                </div>

                <!-- Bagian Kanan: Tab Navigation -->
                <div class="card-tabs">
                    <ul class="nav nav-tabs style-1 d-inline-flex">
                        <li class="nav-item">
                            <a href="{{ route('administrator.setting-monev.laporan.kategori.index') }}"
                                class="nav-link {{ request()->routeIs('administrator.setting-monev.laporan.kategori.*') ? 'active' : '' }}">
                                Kategori Laporan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('administrator.setting-monev.laporan.jenis.index') }}"
                                class="nav-link {{ request()->routeIs('administrator.setting-monev.laporan.jenis.*') ? 'active' : '' }}">
                                Jenis Laporan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('administrator.setting-monev.laporan.dokumen.index') }}"
                                class="nav-link {{ request()->routeIs('administrator.setting-monev.laporan.dokumen.*') ? 'active' : '' }}">
                                Jenis Dokumen
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('administrator.setting-monev.laporan.pertanyaan.index') }}"
                                class="nav-link {{ request()->routeIs('administrator.setting-monev.laporan.pertanyaan.*') ? 'active' : '' }}">
                                Pertanyaan
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    {{-- Konten halaman tab di-yield dari masing-masing view --}}
                    @yield('tab-content')
                </div>
            </div>
        </div>
    </div>
@endsection

@section('this-page-scripts')
    <script src="{{ asset('templates/assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('templates/administration/vendor/datatables/responsive/responsive.js') }}"></script>
    @stack('scripts')
@endsection
