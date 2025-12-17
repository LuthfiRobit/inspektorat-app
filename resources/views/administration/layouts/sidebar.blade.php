<!-- Sidebar start -->
<div class="deznav">
    <div class="deznav-scroll">
        <!-- Sidebar menu -->
        <ul class="metismenu" id="menu">
            @php
                $user = auth()->user();
            @endphp

            <!-- Dashboard (Accessible to all roles) -->
            @if ($user->hasPermissionTo('administrator.dashboard.index'))
                <li>
                    <a class="ai-icon" href="{{ route('administrator.dashboard.index') }}">
                        <i class="fas fa-tachometer-alt fw-bold"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
            @endif

            <!-- Master Data (Only accessible to Developer) -->
            @if (
                    $user->hasAnyPermission([
                        'administrator.master.kecamatan.index',
                        'administrator.master.desa.index',
                        'administrator.master.petugas.inspektorat.index',
                        'administrator.master.petugas.kecamatan.index',
                        'administrator.master.petugas.desa.index',
                        'administrator.master.tahun-anggaran.index',
                        'administrator.master.jenis-kegiatan.index',
                        'administrator.master.kegiatan.index',
                        'administrator.master.pertanyaan-kegiatan.index',
                        'administrator.master.persyaratan.index',
                    ])
                )
                <li>
                    <a class="has-arrow ai-icon" href="javascript:void(0);" aria-expanded="false">
                        <i class="fas fa-database fw-bold"></i>
                        <span class="nav-text">Master Data</span>
                    </a>
                    <ul aria-expanded="false">
                        <!-- Wilayah -->
                        @if ($user->hasAnyPermission(['administrator.master.kecamatan.index', 'administrator.master.desa.index']))
                            <li class="nav-label mt-2 text-uppercase small text-muted px-2">Wilayah</li>
                        @endif

                        @if ($user->hasPermissionTo('administrator.master.kecamatan.index'))
                            <li><a href="{{ route('administrator.master.kecamatan.index') }}" class="fs-6">Kecamatan</a></li>
                        @endif

                        @if ($user->hasPermissionTo('administrator.master.desa.index'))
                            <li><a href="{{ route('administrator.master.desa.index') }}" class="fs-6">Desa</a></li>
                        @endif

                        <!-- Petugas -->
                        @if (
                                $user->hasAnyPermission([
                                    'administrator.master.petugas.inspektorat.index',
                                    'administrator.master.petugas.kecamatan.index',
                                    'administrator.master.petugas.desa.index',
                                ])
                            )
                            <li class="nav-label mt-2 text-uppercase small text-muted px-2">Petugas</li>
                        @endif

                        @if ($user->hasPermissionTo('administrator.master.petugas.inspektorat.index'))
                            <li><a href="{{ route('administrator.master.petugas.inspektorat.index') }}"
                                    class="fs-6">Inspektorat</a></li>
                        @endif

                        @if ($user->hasPermissionTo('administrator.master.petugas.kecamatan.index'))
                            <li><a href="{{ route('administrator.master.petugas.kecamatan.index') }}" class="fs-6">Kecamatan</a>
                            </li>
                        @endif

                        @if ($user->hasPermissionTo('administrator.master.petugas.desa.index'))
                            <li><a href="{{ route('administrator.master.petugas.desa.index') }}" class="fs-6">Desa</a>
                            </li>
                        @endif

                        <!-- Kegiatan & Kriteria -->
                        @if (
                                $user->hasAnyPermission([
                                    'administrator.master.tahun-anggaran.index',
                                    'administrator.master.jenis-kegiatan.index',
                                    'administrator.master.kegiatan.index',
                                    'administrator.master.pertanyaan-kegiatan.index',
                                    'administrator.master.persyaratan.index',
                                ])
                            )
                            <li class="nav-label mt-2 text-uppercase small text-muted px-2">Kegiatan & Kriteria</li>
                        @endif

                        @if ($user->hasPermissionTo('administrator.master.tahun-anggaran.index'))
                            <li><a href="{{ route('administrator.master.tahun-anggaran.index') }}" class="fs-6">Tahun
                                    Anggaran</a></li>
                        @endif

                        @if ($user->hasPermissionTo('administrator.master.jenis-kegiatan.index'))
                            <li><a href="{{ route('administrator.master.jenis-kegiatan.index') }}" class="fs-6">Jenis
                                    Kegiatan</a></li>
                        @endif

                        @if ($user->hasPermissionTo('administrator.master.kegiatan.index'))
                            <li><a href="{{ route('administrator.master.kegiatan.index') }}" class="fs-6">Kegiatan</a>
                            </li>
                        @endif

                        @if ($user->hasPermissionTo('administrator.master.pertanyaan-kegiatan.index'))
                            <li><a href="{{ route('administrator.master.pertanyaan-kegiatan.index') }}" class="fs-6">Pertanyaan
                                    Kegiatan</a></li>
                        @endif

                        @if ($user->hasPermissionTo('administrator.master.persyaratan.index'))
                            <li><a href="{{ route('administrator.master.persyaratan.index') }}" class="fs-6">Persyaratan
                                    Dokumen</a></li>
                        @endif
                    </ul>
                </li>
            @endif

            <!-- Pelaporan (Monev) -->
            @if ($user->hasAnyPermission(['administrator.monev.laporan.index', 'administrator.monev.review.index']))
                <li>
                    <a class="has-arrow ai-icon" href="javascript:void(0);" aria-expanded="false">
                        <i class="fas fa-file-alt fw-bold"></i>
                        <span class="nav-text">Monev</span>
                    </a>
                    <ul aria-expanded="false">
                        @if ($user->hasPermissionTo('administrator.monev.laporan.index'))
                            <li><a href="{{ route('administrator.monev.laporan.index') }}" class="fs-6">Buat
                                    Laporan</a></li>
                        @endif

                        @if ($user->hasPermissionTo('administrator.monev.review.index'))
                            <li><a href="{{ route('administrator.monev.review.index') }}" class="fs-6">Review
                                    Laporan</a></li>
                        @endif
                    </ul>
                </li>
            @endif

            <!-- Monitoring & Evaluasi -->
            @if ($user->hasAnyPermission(['administrator.monitoring.riwayat.index']))
                <li>
                    <a class="has-arrow ai-icon" href="javascript:void(0);" aria-expanded="false">
                        <i class="fas fa-chart-line fw-bold"></i>
                        <span class="nav-text">Monitoring</span>
                    </a>
                    <ul aria-expanded="false">
                        @if ($user->hasPermissionTo('administrator.monitoring.riwayat.index'))
                            <li><a href="{{ route('administrator.monitoring.riwayat.index') }}" class="fs-6">Riwayat
                                    Laporan</a></li>
                        @endif

                        {{-- Contoh menu tanpa permission check --}}
                        {{-- <li><a href="#" class="fs-6">Keterlambatan</a></li> --}}
                        {{-- <li><a href="#" class="fs-6">Scoring Desa</a></li> --}}
                        <!-- Wilayah -->
                        <li class="nav-label mt-2 text-uppercase small text-muted px-2">Scoring</li>

                        <li><a href="{{ route('administrator.monitoring.scoring.desa.index') }}" class="fs-6">Desa</a></li>
                        <li><a href="{{ route('administrator.monitoring.scoring.kecamatan.index') }}" class="fs-6">Kecamatan</a></li>
                    </ul>
                </li>
            @endif

            <!-- Pengaturan Sistem (RBAC) -->
            @if (
                    $user->hasAnyPermission([
                        'administrator.rbac.permission.index',
                        'administrator.rbac.role.index',
                        'administrator.rbac.user.index',
                    ])
                )
                <li>
                    <a class="has-arrow ai-icon" href="javascript:void(0);" aria-expanded="false">
                        <i class="fas fa-users-cog fw-bold"></i>
                        <span class="nav-text">RBAC</span>
                    </a>
                    <ul aria-expanded="false">
                        @if ($user->hasPermissionTo('administrator.rbac.permission.index'))
                            <li><a href="{{ route('administrator.rbac.permission.index') }}" class="fs-6">Permission</a></li>
                        @endif

                        @if ($user->hasPermissionTo('administrator.rbac.role.index'))
                            <li><a href="{{ route('administrator.rbac.role.index') }}" class="fs-6">Role</a></li>
                        @endif

                        @if ($user->hasPermissionTo('administrator.rbac.user.index'))
                            <li><a href="{{ route('administrator.rbac.user.index') }}" class="fs-6">User</a></li>
                        @endif
                    </ul>
                </li>
            @endif

            <!-- Sistem -->
            @if ($user->hasAnyPermission(['administrator.system.log-activity.index', 'log-viewer::dashboard']))
                <li>
                    <a class="has-arrow ai-icon" href="javascript:void(0);" aria-expanded="false">
                        <i class="fas fa-server fw-bold"></i>
                        <span class="nav-text">Sistem</span>
                    </a>
                    <ul aria-expanded="false">
                        @if ($user->hasPermissionTo('administrator.system.log-activity.index'))
                            <li><a href="{{ route('administrator.system.log-activity.index') }}" class="fs-6">Log
                                    Activity</a></li>
                        @endif

                        @if ($user->hasPermissionTo('log-viewer::dashboard'))
                            <li><a href="{{ route('log-viewer::dashboard') }}" class="fs-6">Log Viewer</a></li>
                        @endif
                    </ul>
                </li>
            @endif
        </ul>

        <!-- Footer with copyright information -->
        <div class="copyright">
            <p><strong>Payment App</strong> © <span class="current-year"></span> All Rights Reserved</p>
            <p>Developed by <a href="#" target="_blank">FAKULTAS TEKNIK UNUJA</a></p>
        </div>
    </div>
</div>
<!-- Sidebar end -->