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
                    <!-- Notification Bell Icon -->
                    @if (Auth::check())
                        @php
                            $unreadNotifications = Auth::user()->unreadNotifications;
                            $notifications = Auth::user()->notifications()->take(5)->get();
                        @endphp
                        <li class="nav-item dropdown notification_dropdown d-none d-sm-flex">
                            <a class="nav-link ai-icon" href="javascript:void(0)" role="button" data-bs-toggle="dropdown">
                                <svg width="20" height="20" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12.6001 4.3008V1.4C12.6001 0.627199 13.2273 0 14.0001 0C14.7715 0 15.4001 0.627199 15.4001 1.4V4.3008C17.4805 4.6004 19.4251 5.56639 20.9287 7.06999C22.7669 8.90819 23.8001 11.4016 23.8001 14V19.2696L24.9327 21.5348C25.4745 22.6198 25.4171 23.9078 24.7787 24.9396C24.1417 25.9714 23.0147 26.6 21.8023 26.6H15.4001C15.4001 27.3728 14.7715 28 14.0001 28C13.2273 28 12.6001 27.3728 12.6001 26.6H6.19791C4.98411 26.6 3.85714 25.9714 3.22014 24.9396C2.58174 23.9078 2.52433 22.6198 3.06753 21.5348L4.20011 19.2696V14C4.20011 11.4016 5.23194 8.90819 7.07013 7.06999C8.57513 5.56639 10.5183 4.6004 12.6001 4.3008ZM14.0001 6.99998C12.1423 6.99998 10.3629 7.73779 9.04973 9.05099C7.73653 10.3628 7.00011 12.1436 7.00011 14V19.6C7.00011 19.817 6.94833 20.0312 6.85173 20.2258C6.85173 20.2258 6.22871 21.4718 5.57072 22.7864C5.46292 23.0034 5.47412 23.2624 5.60152 23.4682C5.72892 23.674 5.95431 23.8 6.19791 23.8H21.8023C22.0445 23.8 22.2699 23.674 22.3973 23.4682C22.5247 23.2624 22.5359 23.0034 22.4281 22.7864C21.7701 21.4718 21.1471 20.2258 21.1471 20.2258C21.0505 20.0312 21.0001 19.817 21.0001 19.6V14C21.0001 12.1436 20.2623 10.3628 18.9491 9.05099C17.6359 7.73779 15.8565 6.99998 14.0001 6.99998Z" fill="#3E4954"></path>
                                </svg>
                                @if($unreadNotifications->count() > 0)
                                    <span class="badge light text-white bg-primary rounded-circle">{{ $unreadNotifications->count() }}</span>
                                @endif
                            </a>
                            <div class="dropdown-menu dropdown-menu-end" data-bs-popper="static" style="width: 320px; padding: 0; border: none; border-radius: 8px;">
                                <div class="dropdown-header pb-3 pt-3 border-bottom d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, rgba(30, 170, 231, 0.05) 0%, rgba(30, 170, 231, 0.01) 100%); border-radius: 8px 8px 0 0;">
                                    <h6 class="fs-14 mb-0 fw-bold text-black" style="letter-spacing: 0.2px;">Notifikasi</h6>
                                    @if($unreadNotifications->count() > 0)
                                        <button type="button" id="mark-all-read-btn" class="btn btn-xs btn-outline-primary py-1 px-2 border-0" style="font-size: 11px;">Tandai Semua Dibaca</button>
                                    @endif
                                </div>
                                <div id="DZ_W_Notification1" class="dz-scroll p-3 height380" style="overflow-y: auto; max-height: 380px;">
                                    <ul>
                                        @forelse($notifications as $notification)
                                            <li>
                                                <a href="{{ route('administrator.system.notifications.read', $notification->id) }}" class="text-decoration-none d-block">
                                                    <div class="d-flex align-items-center border-bottom pb-3 mb-3 position-relative">
                                                        @if(!$notification->read_at)
                                                            <span class="position-absolute start-0 top-0 bottom-0 bg-primary" style="width: 3px; height: 100%; border-radius: 2px;"></span>
                                                        @endif
                                                        
                                                        @php
                                                            $avatarClass = 'bg-info text-bold text-white';
                                                            $avatarIcon = 'fa-info';
                                                            if(isset($notification->data['type']) && $notification->data['type'] == 'success') {
                                                                $avatarClass = 'bg-success text-bold text-white';
                                                                $avatarIcon = 'fa-check';
                                                            } elseif(isset($notification->data['type']) && $notification->data['type'] == 'warning') {
                                                                $avatarClass = 'bg-warning text-bold text-white';
                                                                $avatarIcon = 'fa-exclamation';
                                                            }
                                                        @endphp
                                                        <div class="rounded-3 overflow-hidden text-center d-flex align-items-center justify-content-center fw-bold me-3 ms-2 avatar avatar-sm {{ $avatarClass }}" style="width: 40px; height: 40px; flex-shrink: 0;">
                                                            <i class="fa {{ $avatarIcon }}"></i>
                                                        </div>
                                                        <div class="media-body pe-2">
                                                            <h6 class="mb-1 text-black {{ $notification->read_at ? 'fw-normal' : 'fw-bold' }}" style="font-size: 13px; line-height: 1.3;">{{ $notification->data['title'] ?? 'Pemberitahuan' }}</h6>
                                                            <p class="mb-1 text-muted text-wrap" style="font-size: 11px; line-height: 1.2;">{{ $notification->data['message'] ?? '' }}</p>
                                                            <small class="d-block text-primary" style="font-size: 10px;">{{ $notification->created_at->diffForHumans() }}</small>
                                                        </div>
                                                    </div>
                                                </a>
                                            </li>
                                        @empty
                                            <div class="p-4 text-center">
                                                <div class="mb-3">
                                                    <i class="fas fa-bell-slash text-muted" style="font-size: 32px; opacity: 0.5;"></i>
                                                </div>
                                                <p class="mb-0 text-muted fw-medium" style="font-size: 13px;">Belum ada notifikasi.</p>
                                            </div>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </li>
                    @endif
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const markAllReadBtn = document.getElementById('mark-all-read-btn');
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function (e) {
            e.preventDefault();
            
            // Set loading state
            markAllReadBtn.disabled = true;
            markAllReadBtn.textContent = 'Memproses...';
            
            fetch("{{ route('administrator.system.notifications.read-all') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // 1. Remove the unread badge from the bell icon
                    const badge = document.querySelector('.notification_dropdown .badge');
                    if (badge) {
                        badge.remove();
                    }
                    
                    // 2. Remove the "Tandai Semua Dibaca" button
                    markAllReadBtn.remove();
                    
                    // 3. Remove all blue bar status indicators from list items
                    const unreadBars = document.querySelectorAll('.notification_dropdown ul li span.bg-primary');
                    unreadBars.forEach(bar => bar.remove());
                    
                    // 4. Change font weights of unread item titles to normal
                    const unreadTitles = document.querySelectorAll('.notification_dropdown ul li h6.fw-bold');
                    unreadTitles.forEach(title => {
                        title.classList.remove('fw-bold');
                        title.classList.add('fw-normal');
                    });
                } else {
                    markAllReadBtn.disabled = false;
                    markAllReadBtn.textContent = 'Tandai Semua Dibaca';
                    alert(data.message || 'Gagal menandai notifikasi.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                markAllReadBtn.disabled = false;
                markAllReadBtn.textContent = 'Tandai Semua Dibaca';
                alert('Terjadi kesalahan koneksi.');
            });
        });
    }
});
</script>