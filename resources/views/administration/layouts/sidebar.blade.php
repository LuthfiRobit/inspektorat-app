<!-- Sidebar start -->
<div class="deznav">
    <div class="deznav-scroll">
        <!-- Sidebar menu -->
        <ul class="metismenu" id="menu">
            @php
                $user = auth()->user();
            @endphp

            <!-- Dashboard (Accessible to all roles) -->
            @if ($user->hasPermissionTo('dashboard.view'))
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
                        'master.kecamatan.view',
                        'master.desa.view',
                        'master.petugas.inspektorat.view',
                        'master.petugas.kecamatan.view',
                        'master.petugas.desa.view',
                        'master.tahun-anggaran.view',
                        'master.jenis-kegiatan.view',
                        'master.kegiatan.view',
                        'master.pertanyaan-kegiatan.view',
                        'master.persyaratan.view',
                    ])
                )
                <li>
                    <a class="has-arrow ai-icon" href="javascript:void(0);" aria-expanded="false">
                        <i class="fas fa-database fw-bold"></i>
                        <span class="nav-text">Master Data</span>
                    </a>
                    <ul aria-expanded="false">
                        <!-- Wilayah -->
                        @if ($user->hasAnyPermission(['master.kecamatan.view', 'master.desa.view', 'master.wilayah-binaan.view']))
                            <li class="nav-label mt-2 text-uppercase small text-muted px-2">Wilayah</li>
                        @endif

                        @if ($user->hasPermissionTo('master.kecamatan.view'))
                            <li><a href="{{ route('administrator.master.kecamatan.index') }}" class="fs-6">Kecamatan</a></li>
                        @endif

                        @if ($user->hasPermissionTo('master.desa.view'))
                            <li><a href="{{ route('administrator.master.desa.index') }}" class="fs-6">Desa</a></li>
                        @endif

                        @if ($user->hasPermissionTo('master.wilayah-binaan.view'))
                            <li><a href="{{ route('administrator.master.wilayah-binaan.index') }}" class="fs-6">Wilayah Binaan</a></li>
                        @endif

                        <!-- Petugas -->
                        @if (
                                $user->hasAnyPermission([
                                    'master.petugas.inspektorat.view',
                                    'master.petugas.kecamatan.view',
                                    'master.petugas.desa.view',
                                ])
                            )
                            <li class="nav-label mt-2 text-uppercase small text-muted px-2">Petugas</li>
                        @endif

                        @if ($user->hasPermissionTo('master.petugas.inspektorat.view'))
                            <li><a href="{{ route('administrator.master.petugas.inspektorat.index') }}"
                                    class="fs-6">Inspektorat</a></li>
                        @endif

                        @if ($user->hasPermissionTo('master.petugas.kecamatan.view'))
                            <li><a href="{{ route('administrator.master.petugas.kecamatan.index') }}" class="fs-6">Kecamatan</a>
                            </li>
                        @endif

                        @if ($user->hasPermissionTo('master.petugas.desa.view'))
                            <li><a href="{{ route('administrator.master.petugas.desa.index') }}" class="fs-6">Desa</a>
                            </li>
                        @endif

                        <!-- Kegiatan & Kriteria -->
                        @if (
                                $user->hasAnyPermission([
                                    'master.tahun-anggaran.view',
                                    'master.jenis-kegiatan.view',
                                    'master.kegiatan.view',
                                    'master.pertanyaan-kegiatan.view',
                                    'master.persyaratan.view',
                                ])
                            )
                            <li class="nav-label mt-2 text-uppercase small text-muted px-2">Kegiatan & Kriteria</li>
                        @endif

                        @if ($user->hasPermissionTo('master.tahun-anggaran.view'))
                            <li><a href="{{ route('administrator.master.tahun-anggaran.index') }}" class="fs-6">Tahun
                                    Anggaran</a></li>
                        @endif

                        @if ($user->hasPermissionTo('master.jenis-kegiatan.view'))
                            <li><a href="{{ route('administrator.master.jenis-kegiatan.index') }}" class="fs-6">Jenis
                                    Kegiatan</a></li>
                        @endif

                        @if ($user->hasPermissionTo('master.kegiatan.view'))
                            <li><a href="{{ route('administrator.master.kegiatan.index') }}" class="fs-6">Kegiatan</a>
                            </li>
                        @endif

                        @if ($user->hasPermissionTo('master.pertanyaan-kegiatan.view'))
                            <li><a href="{{ route('administrator.master.pertanyaan-kegiatan.index') }}" class="fs-6">Pertanyaan
                                    Kegiatan</a></li>
                        @endif

                        @if ($user->hasPermissionTo('master.persyaratan.view'))
                            <li><a href="{{ route('administrator.master.persyaratan.index') }}" class="fs-6">Persyaratan
                                    Dokumen</a></li>
                        @endif
                    </ul>
                </li>
            @endif

            <!-- Pelaporan (Monev) -->
            @if ($user->hasAnyPermission(['monev.laporan.view', 'monev.review.view', 'monitoring.riwayat.view', 'monitoring.tarik-data.view']))
                <li>
                    <a class="has-arrow ai-icon" href="javascript:void(0);" aria-expanded="false">
                        <i class="fas fa-file-alt fw-bold"></i>
                        <span class="nav-text">Monev</span>
                    </a>
                    <ul aria-expanded="false">
                        @if ($user->hasPermissionTo('monev.laporan.view'))
                            <li><a href="{{ route('administrator.monev.laporan.index') }}" class="fs-6">Buat
                                    Laporan</a></li>
                        @endif

                        @if ($user->hasPermissionTo('monev.review.view'))
                            <li><a href="{{ route('administrator.monev.review.index') }}" class="fs-6">Review
                                    Laporan</a></li>
                        @endif

                        @if ($user->hasPermissionTo('monitoring.riwayat.view'))
                            <li><a href="{{ route('administrator.monitoring.riwayat.index') }}" class="fs-6">Riwayat
                                    Laporan</a></li>
                        @endif

                        @if ($user->hasPermissionTo('monitoring.tarik-data.view'))
                            <li><a href="{{ route('administrator.monitoring.tarik-data.index') }}" class="fs-6">Tarik
                                    Data</a></li>
                        @endif
                    </ul>
                </li>
            @endif

            <!-- Scoring -->
            @if ($user->hasAnyPermission(['monitoring.scoring.desa.view', 'monitoring.scoring.kecamatan.view']))
                <li>
                    <a class="has-arrow ai-icon" href="javascript:void(0);" aria-expanded="false">
                        <i class="fas fa-award fw-bold"></i>
                        <span class="nav-text">Scoring</span>
                    </a>
                    <ul aria-expanded="false">
                        @if ($user->hasPermissionTo('monitoring.scoring.desa.view'))
                            <li><a href="{{ route('administrator.monitoring.scoring.desa.index') }}" class="fs-6">Desa</a></li>
                        @endif
                        @if ($user->hasPermissionTo('monitoring.scoring.kecamatan.view'))
                            <li><a href="{{ route('administrator.monitoring.scoring.kecamatan.index') }}"
                                    class="fs-6">Kecamatan</a></li>
                        @endif
                    </ul>
                </li>
            @endif

            <!-- Pengaturan Sistem (RBAC) -->
            @if (
                    $user->hasAnyPermission([
                        'rbac.permission.view',
                        'rbac.role.view',
                        'rbac.user.view',
                    ])
                )
                <li>
                    <a class="has-arrow ai-icon" href="javascript:void(0);" aria-expanded="false">
                        <i class="fas fa-users-cog fw-bold"></i>
                        <span class="nav-text">RBAC</span>
                    </a>
                    <ul aria-expanded="false">
                        @if ($user->hasPermissionTo('rbac.permission.view'))
                            <li><a href="{{ route('administrator.rbac.permission.index') }}" class="fs-6">Permission</a></li>
                        @endif

                        @if ($user->hasPermissionTo('rbac.role.view'))
                            <li><a href="{{ route('administrator.rbac.role.index') }}" class="fs-6">Role</a></li>
                        @endif

                        @if ($user->hasPermissionTo('rbac.user.view'))
                            <li><a href="{{ route('administrator.rbac.user.index') }}" class="fs-6">User</a></li>
                        @endif
                    </ul>
                </li>
            @endif

            <!-- Sistem -->
            @if ($user->hasAnyPermission(['system.log-activity.view', 'log-viewer::dashboard']))
                <li>
                    <a class="has-arrow ai-icon" href="javascript:void(0);" aria-expanded="false">
                        <i class="fas fa-server fw-bold"></i>
                        <span class="nav-text">Sistem</span>
                    </a>
                    <ul aria-expanded="false">
                        @if ($user->hasPermissionTo('system.log-activity.view'))
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
            <p><strong>SIDESA-SAE</strong> © <span class="current-year"></span> All Rights Reserved</p>
            <p>Developed by <a href="#" target="_blank">FAKULTAS TEKNIK UNUJA</a></p>
        </div>
    </div>
</div>
<!-- Sidebar end -->