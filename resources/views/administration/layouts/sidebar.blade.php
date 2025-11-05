<!-- Sidebar start -->
<div class="deznav">
    <div class="deznav-scroll">
        <!-- Sidebar menu -->
        <ul class="metismenu" id="menu">
            <!-- Dashboard (Accessible to all roles) -->
            <li>
                <a class="ai-icon" href="{{ route('administrator.dashboard.index') }}">
                    <i class="fas fa-tachometer-alt fw-bold"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>

            <!-- Master Data (Only accessible to Developer) -->
            <li>
                <a class="has-arrow ai-icon" href="javascript:void(0);" aria-expanded="false">
                    <i class="fas fa-database fw-bold"></i>
                    <span class="nav-text">Master Data</span>
                </a>
                <ul aria-expanded="false">
                    <li class="nav-label mt-2 text-uppercase small text-muted px-2">Wilayah</li>
                    <li><a href="{{ route('administrator.master.kecamatan.index') }}" class="fs-6">Kecamatan</a></li>
                    <li><a href="{{ route('administrator.master.desa.index') }}" class="fs-6">Desa</a></li>

                    <li class="nav-label mt-2 text-uppercase small text-muted px-2">Petugas</li>
                    <li><a href="{{ route('administrator.master.petugas.inspektorat.index') }}"
                            class="fs-6">Inspektorat</a></li>
                    <li><a href="{{ route('administrator.master.petugas.kecamatan.index') }}"
                            class="fs-6">Kecamatan</a></li>
                    <li><a href="{{ route('administrator.master.petugas.desa.index') }}" class="fs-6">Desa</a></li>

                    <li class="nav-label mt-2 text-uppercase small text-muted px-2">Kegiatan & Kriteria</li>
                    <li><a href="{{ route('administrator.master.tahun-anggaran.index') }}" class="fs-6">Tahun
                            Anggaran</a></li>
                    <li><a href="{{ route('administrator.master.jenis-kegiatan.index') }}" class="fs-6">Jenis
                            Kegiatan</a></li>
                    <li><a href="{{ route('administrator.master.kegiatan.index') }}" class="fs-6">Kegiatan</a></li>
                    <li><a href="{{ route('administrator.master.pertanyaan-kegiatan.index') }}"
                            class="fs-6">Pertanyaan Kegiatan</a></li>
                    <li><a href="{{ route('administrator.master.persyaratan.index') }}" class="fs-6">Persyaratan
                            Dokumen</a></li>
                </ul>
            </li>

            <!-- Pelaporan (Accessible to Petugas and Developer) -->
            <li>
                <a class="has-arrow ai-icon" href="javascript:void(0);" aria-expanded="false">
                    <i class="fas fa-file-alt fw-bold"></i>
                    <span class="nav-text">Monev</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('administrator.monev.laporan.index') }}" class="fs-6">Buat Laporan</a></li>
                    <li><a href="{{ route('administrator.monev.review.index') }}" class="fs-6">Review Laporan</a>
                    </li>
                </ul>
            </li>

            <!-- Monitoring & Evaluasi (Accessible to Petugas and Developer) -->
            <li>
                <a class="has-arrow ai-icon" href="javascript:void(0);" aria-expanded="false">
                    <i class="fas fa-chart-line fw-bold"></i>
                    <span class="nav-text">Monitoring</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('administrator.monitoring.riwayat.index') }}" class="fs-6">Riwayat
                            Laporan</a></li>
                    {{-- <li><a href="#" class="fs-6">Keterlambatan</a></li> --}}
                    <li><a href="#" class="fs-6">Scoring Desa</a></li>
                </ul>
            </li>

            <!-- Pengaturan Sistem (Only accessible to Developer) -->
            <li>
                <a class="has-arrow ai-icon" href="javascript:void(0);" aria-expanded="false">
                    <i class="fas fa-users-cog fw-bold"></i>
                    <span class="nav-text">RBAC</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('administrator.rbac.permission.index') }}" class="fs-6">Permission</a>
                    </li>
                    <li><a href="{{ route('administrator.rbac.role.index') }}" class="fs-6">Role</a></li>
                    <li><a href="{{ route('administrator.rbac.user.index') }}" class="fs-6">User</a></li>
                </ul>
            </li>

            <!-- Sistem -->
            <li>
                <a class="has-arrow ai-icon" href="javascript:void(0);" aria-expanded="false">
                    <i class="fas fa-server fw-bold"></i>
                    <span class="nav-text">Sistem</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('administrator.system.log-activity.index') }}" class="fs-6">Log
                            Activity</a>
                    </li>
                    <li><a href="{{ route('log-viewer::dashboard') }}" class="fs-6">Log Viewer</a></li>
                </ul>
            </li>
        </ul>

        <!-- Footer with copyright information -->
        <div class="copyright">
            <p><strong>Payment App</strong> © <span class="current-year"></span> All Rights Reserved</p>
            <p>Developed by <a href="#" target="_blank">FAKULTAS TEKNIK UNUJA</a></p>
        </div>
    </div>
</div>
<!-- Sidebar end -->
