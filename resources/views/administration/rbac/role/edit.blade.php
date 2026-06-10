@extends('administration.layouts.app')

@section('title', 'Edit Role Permissions | Sistem Pelaporan')
@section('meta-description', 'Halaman untuk mengatur permissions pada role.')

@section('this-page-style')
    <style>
        .module-card {
            border-radius: 0.5rem;
        }

        .submodule-card {
            transition: all 0.3s ease;
        }

        .submodule-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.08)!important;
            border-color: #0d6efd !important;
        }

        #permissions_list {
            scrollbar-width: thin;
            scrollbar-color: #c1c1c1 #f1f1f1;
        }

        #permissions_list::-webkit-scrollbar {
            width: 6px;
        }

        #permissions_list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        #permissions_list::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }

        #permissions_list::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        .bg-light-primary {
            background-color: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
        }
    </style>
@endsection

@section('content')
    <div class="content-body default-height">
        <div class="container-fluid">
            <div class="form-head mb-4 d-flex align-items-center gap-2">
                <small class="text-muted">RBAC -</small>
                <h4 class="text-dark fw-semibold mb-0">Role Permission Management</h4>
            </div>

            <!-- Loading Indicator -->
            <div id="loadingIndicator" class="text-center py-5 my-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Memuat data permissions...</p>
            </div>

            <!-- Error Message -->
            <div id="errorMessage" class="alert alert-danger d-none" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <span id="errorText">Terjadi kesalahan saat memuat data.</span>
            </div>

            <!-- Main Content -->
            <div class="row g-4 d-none" id="mainContent">
                <!-- Left Column: Role Info -->
                <div class="col-12 col-lg-4" style="max-height: 100vh; overflow-y: auto;">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-shield-alt me-2 text-primary"></i>Edit Role Pengguna</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark small">Scope Role</label>
                                <input type="text" class="form-control bg-light text-capitalize" id="detail_p_role_scope" readonly>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark small">Nama Role</label>
                                <input type="text" class="form-control bg-light" id="detail_p_role_name" readonly>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark small">Deskripsi Role</label>
                                <textarea class="form-control bg-light" id="detail_p_role_description" rows="3" readonly></textarea>
                            </div>

                            <div class="alert alert-info border-0 bg-info bg-opacity-10 text-info small mb-4">
                                <i class="fas fa-info-circle me-1"></i> Centang kotak pada modul di sebelah kanan untuk memberikan akses fitur kepada role ini.
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" form="permissionForm" id="savePermissions" class="btn btn-primary shadow-sm">
                                    <i class="fas fa-save me-2"></i>Simpan Role
                                </button>
                                <a href="{{ route('administrator.rbac.role.index') }}" class="btn btn-light border shadow-sm">
                                    Batal
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Permissions -->
                <div class="col-12 col-lg-8">
                    <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-key me-2 text-warning"></i>Hak Akses (Permissions)</h5>
                                
                                <div class="d-flex align-items-center gap-2">
                                    <div class="input-group input-group-sm" style="width: 200px;">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="fas fa-search text-muted"></i>
                                        </span>
                                        <input type="text" id="searchPermissions" class="form-control border-start-0" placeholder="Cari modul...">
                                    </div>
                                    <button type="button" id="resetSelection" class="btn btn-sm btn-outline-secondary" title="Reset Selections">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <small class="text-muted fw-medium">
                                        <i class="fas fa-check-circle me-1 text-success"></i>
                                        <span id="selectedCount">0</span> of <span id="totalCount">0</span> selected
                                    </small>
                                </div>
                                <form id="permissionForm" method="POST" class="form">
                                    @csrf
                                    <!-- Permissions List -->
                                    <div id="permissions_list" class="pb-3" style="min-height: 50vh;">
                                        <!-- Structured permission groups will be injected here -->
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection

@section('this-page-scripts')
    @include('administration.rbac.role.scripts.edit-handler')
@endsection
