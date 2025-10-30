@extends('administration.layouts.app')

@section('title', 'Edit Role Permissions | Sistem Pelaporan')
@section('meta-description', 'Halaman untuk mengatur permissions pada role.')

@section('this-page-style')
    <style>
        .accordion-button:not(.collapsed) {
            background-color: #f8f9fa;
            color: #000;
        }

        .permission-card {
            transition: all 0.3s ease;
        }

        .permission-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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
    </style>
@endsection

@section('content')
    <div class="content-body default-height">
        <div class="container-fluid">
            <div class="form-head mb-4 d-flex align-items-center gap-2">
                <small class="text-muted">RBAC -</small>
                <h4 class="text-dark fw-semibold mb-0">Role Permission Management</h4>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-sm-flex d-block border-0 pb-0 flex-wrap align-items-center">
                            <div class="pr-3 me-auto mb-sm-0 mb-3">
                                <h4 class="fs-20 text-black mb-1">Kelola Permissions Role</h4>
                                <span class="fs-12 text-muted">Atur hak akses dan permissions untuk role tertentu</span>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <a href="{{ route('administrator.rbac.role.index') }}"
                                    class="btn btn-outline-primary btn-sm btn-rounded" title="Kembali">
                                    <i class="las la-arrow-left scale5 me-1"></i> Kembali
                                </a>
                            </div>
                        </div>

                        <div class="card-body">
                            <!-- Loading Indicator -->
                            <div id="loadingIndicator" class="text-center py-4">
                                <div class="spinner-border " role="status">
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
                            <div id="mainContent" class="d-none">
                                <form id="permissionForm" method="POST" class="form">
                                    @csrf
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <div class="card">
                                                <div class="card-header text-white py-3">
                                                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Role
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <span class="fw-bold ">Nama Role</span><br>
                                                            <span id="detail_p_role_name"
                                                                class="badge light badge-success">-</span>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <span class="fw-bold ">Deskripsi Role</span><br>
                                                            <span id="detail_p_role_description"
                                                                class="text-dark fs-6">-</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div>
                                                <span class="fs-5 fw-bold text-dark">Daftar Permissions</span>
                                                <small class="text-muted d-block">Pilih permissions yang akan diberikan
                                                    kepada role</small>
                                            </div>
                                        </div>
                                        <!-- Search + Selected Count -->
                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <div class="alert alert-light border mb-0">
                                                    <div class="row align-items-center">
                                                        <!-- Search -->
                                                        <div class="col-md-6 mb-2 mb-md-0">
                                                            <div class="input-group">
                                                                <span class="input-group-text bg-light border-end-0">
                                                                    <i class="fas fa-search text-muted"></i>
                                                                </span>
                                                                <input type="text" id="searchPermissions"
                                                                    class="form-control border-start-0"
                                                                    placeholder="Cari permissions...">
                                                            </div>
                                                        </div>

                                                        <!-- Selected Count -->
                                                        <div class="col-md-6">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <small class="text-muted">
                                                                    <i class="fas fa-check-circle me-1 text-success"></i>
                                                                    <span id="selectedCount">0</span> of <span
                                                                        id="totalCount">0</span>
                                                                    permissions selected
                                                                </small>
                                                                <button type="button" id="resetSelection"
                                                                    class="btn btn-sm btn-outline-secondary">
                                                                    <i class="fas fa-undo me-1"></i>Reset
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Permissions List -->
                                        <div id="permissions_list" class="accordion border rounded p-2"
                                            style="max-height: 60vh; overflow-y: auto;">
                                            <!-- Structured permission groups will be injected here -->
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center border-top pt-4 mt-3">
                                        <div>
                                            <a href="{{ route('administrator.rbac.role.index') }}"
                                                class="btn btn-secondary">
                                                <i class="las la-arrow-left me-2"></i>Kembali
                                            </a>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="button" id="resetForm" class="btn btn-outline-warning">
                                                <i class="fas fa-undo me-2"></i>Reset All
                                            </button>
                                            <button type="submit" id="savePermissions" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>Simpan Permissions
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- Information Alert -->
                            <div class="alert alert-info mt-4">
                                <div class="d-flex">
                                    <i class="fas fa-info-circle mt-1 me-3"></i>
                                    <div>
                                        <strong class="d-block mb-2">Informasi Penting:</strong>
                                        <ul class="mb-0 ps-3">
                                            <li>Permissions menentukan hak akses dan kemampuan yang dimiliki oleh role</li>
                                            <li>Pilih permissions sesuai dengan kebutuhan dan tanggung jawab role</li>
                                            <li>Perubahan permissions akan langsung berlaku setelah disimpan</li>
                                            <li>Gunakan fitur pencarian untuk menemukan permissions tertentu dengan cepat
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
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
