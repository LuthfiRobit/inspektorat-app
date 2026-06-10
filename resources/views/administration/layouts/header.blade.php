<!-- Nav header start -->
<div class="nav-header">
    <!-- Brand logo with different images for various screen sizes -->
    <a href="/" class="brand-logo">
        <img class="logo-abbr" src="{{ asset('templates/administration/images/Logo_Kabupaten_Probolinggo.svg') }}"
            alt="" />
        <img class="logo-compact" src="{{ asset('templates/administration/images/Logo_Kabupaten_Probolinggo.svg') }}"
            alt="" />
        <div class="brand-title ms-3" style="max-width: none;">
            <h2 class="mb-0 fs-20 fw-bold" style="letter-spacing: 0.5px; white-space: nowrap;"><span class="text-black">SIDESA-</span><span class="text-primary">SAE</span></h2>
            <small class="text-muted fw-medium d-block" style="font-size: 8px; margin-top: -5px; letter-spacing: 0.5px;">INSPEKTORAT KAB. PROBOLINGGO</small>
        </div>
    </a>

    <!-- Navigation control for mobile view -->
    <div class="nav-control">
        <div class="hamburger">
            <!-- Hamburger menu lines for toggling navigation -->
            <span class="line"></span><span class="line"></span><span class="line"></span>
        </div>
    </div>
</div>
<!-- Nav header end -->

<!-- Header start -->
<div class="header">
    <div class="header-content">
        <!-- Navbar with expandable and collapsible features -->
        <nav class="navbar navbar-expand">
            <div class="collapse navbar-collapse justify-content-between">
                <!-- Left side of the header -->
                <div class="header-left">
                    <!-- Dashboard bar placeholder -->
                    <div class="dashboard_bar"></div>
                </div>
                <!-- Right side of the header with navigation items -->
                <ul class="navbar-nav header-right">
                    <!-- Weather detail item -->
                    <li class="nav-item">
                        <div class="d-flex weather-detail">
                            <span><i class="las la-clock p-0"></i></span>
                            <div id="current-time"></div>
                        </div>
                    </li>
                    <!-- Theme mode toggle dropdown -->
                    <li class="nav-item dropdown notification_dropdown">
                        <a class="nav-link bell dz-theme-mode" href="javascript:void(0);">
                            <i id="icon-light" class="fas fa-sun"></i>
                            <i id="icon-dark" class="fas fa-moon"></i>
                        </a>
                    </li>
                    <!-- User profile dropdown -->
                    <li class="nav-item dropdown header-profile">
                        <a class="nav-link" href="javascript:void(0)" role="button" data-bs-toggle="dropdown">
                            <div class="header-info">
                                @if (Auth::check())
                                    @php
                                        $userPetugas = Auth::user()->petugas ?? null;
                                    @endphp
                                    <span class="text-black"><strong>{{ Auth::user()->name }}</strong></span>
                                    <p class="fs-12 mb-0 overflow-auto text-nowrap" style="white-space: nowrap;">
                                        @foreach (Auth::user()->roles as $index => $role)
                                            {{ ucwords(str_replace('_', ' ', $role->role_name)) }}
                                            @if (!$loop->last)
                                                |
                                            @endif
                                        @endforeach
                                        @if ($userPetugas)
                                            @if ($userPetugas->desa)
                                                | {{ $userPetugas->desa->nama_desa }}
                                            @elseif ($userPetugas->kecamatan)
                                                | {{ $userPetugas->kecamatan->nama_kecamatan }}
                                            @endif
                                        @endif
                                    </p>
                                @else
                                    <span class="text-black">Hello,<strong>Guest</strong></span>
                                    <p class="fs-12 mb-0">Please log in</p>
                                @endif
                            </div>
                            @php
                                $avatarPath = asset('templates/administration/images/avatar/1.png');
                                if (Auth::check() && Auth::user()->petugas && Auth::user()->petugas->foto_petugas) {
                                    $photoFileName = Auth::user()->petugas->foto_petugas;
                                    $fullPath = public_path('uploads/' . $photoFileName);
                                    if (file_exists($fullPath)) {
                                        $avatarPath = asset('uploads/' . $photoFileName);
                                    }
                                }
                            @endphp
                            <img src="{{ $avatarPath }}" width="20" alt="User Profile Picture" />
                        </a>
                        <!-- Dropdown menu with profile and logout options -->
                        <div class="dropdown-menu dropdown-menu-end">
                            @if (Auth::check())
                                @php
                                    $userPetugas = Auth::user()->petugas ?? null;
                                @endphp
                                <div class="dropdown-header pb-3 pt-3 border-bottom mb-2 text-center" style="background: linear-gradient(135deg, rgba(30, 170, 231, 0.05) 0%, rgba(30, 170, 231, 0.01) 100%); border-radius: 8px 8px 0 0; margin-top: -15px; padding-left: 20px; padding-right: 20px;">
                                    <!-- User Avatar (Centered inside the dropdown header) -->
                                    <div class="mb-2 position-relative d-inline-block">
                                        <img src="{{ $avatarPath }}" width="55" height="55" class="rounded-circle shadow-sm border border-2 border-white" alt="User Profile Picture" />
                                        <span class="position-absolute bottom-0 end-0 bg-success border border-2 border-white rounded-circle" style="width: 12px; height: 12px;"></span>
                                    </div>
                                    
                                    <h6 class="fs-15 mb-0 fw-bold text-black" style="letter-spacing: 0.2px;">{{ Auth::user()->name }}</h6>
                                    
                                    <!-- Roles List -->
                                    <div class="d-flex flex-wrap justify-content-center gap-1 mt-1">
                                        @foreach (Auth::user()->roles as $role)
                                            <span class="badge badge-xs bg-light text-dark border fw-medium" style="font-size: 10px; padding: 2px 6px; border-radius: 4px;">
                                                {{ ucwords(str_replace('_', ' ', $role->role_name)) }}
                                            </span>
                                        @endforeach
                                    </div>

                                    <!-- Location details if petugas -->
                                    @if ($userPetugas && ($userPetugas->desa || $userPetugas->kecamatan))
                                        <div class="mt-2 pt-2 border-top border-light d-flex align-items-center justify-content-center">
                                            <div class="d-inline-flex align-items-center px-2 py-1 rounded-pill text-primary fs-11 fw-semibold" style="background: rgba(30, 170, 231, 0.1); border: 1px solid rgba(30, 170, 231, 0.15);">
                                                @if ($userPetugas->desa)
                                                    <i class="fas fa-map-marker-alt me-1 text-primary"></i>
                                                    <span>{{ $userPetugas->desa->nama_desa }}</span>
                                                    <span class="mx-1 text-muted">•</span>
                                                    <!-- <span class="text-muted fw-normal">Desa</span> -->
                                                @elseif ($userPetugas->kecamatan)
                                                    <i class="fas fa-city me-1 text-primary"></i>
                                                    <span>{{ $userPetugas->kecamatan->nama_kecamatan }}</span>
                                                    <span class="mx-1 text-muted">•</span>
                                                    <!-- <span class="text-muted fw-normal">Kecamatan</span> -->
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <a href="{{ route('administrator.profile.index') }}" class="dropdown-item ai-icon">
                                    <i class="fas fa-user text-primary" style="font-size: 18px"></i>
                                    <span class="ms-2">Profile</span>
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                                <a href="javascript:void(0)" class="dropdown-item ai-icon"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fas fa-sign-out-alt text-danger" style="font-size: 18px"></i>
                                    <span class="ms-2">Logout</span>
                                </a>
                            @else
                                <a href="" class="dropdown-item ai-icon">
                                    <i class="fas fa-sign-in-alt text-success" style="font-size: 18px"></i>
                                    <span class="ms-2">Login</span>
                                </a>
                            @endif
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</div>
<!-- Header end -->