@extends('administration.layouts.app')

@section('this-page-style')
    <link href="{{ asset('templates/administration/vendor/datatables/css/jquery.dataTables.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('templates/administration/vendor/datatables/responsive/responsive.css') }}" rel="stylesheet" />
@endsection

@section('content')
    <!-- Content body start -->
    <div class="content-body default-height">
        <div class="container-fluid">
            <!-- Section Heading -->
            <div class="form-head mb-4 d-flex align-items-center gap-2">
                <small class="text-muted">System -</small>
                <h4 class="text-dark fw-semibold mb-0">Log Activity</h4>
            </div>

            <!-- Section content -->
            <div class="row">
                <div class="card">
                    <div class="card-header d-sm-flex d-block border-0 pb-0 flex-wrap">
                        <div class="pr-3 me-auto mb-sm-0 mb-3">
                            <h4 class="fs-20 text-black mb-1">List Log Aktivitas</h4>
                            <span class="fs-12 text-muted">Kelola data log aktivitas, filter status, dan bersihkan
                                log.</span>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <div>
                                <select id="filter_status" class="selectpicker form-control wide form-select-md"
                                    data-live-search="false" title="Pilih status" required>
                                    <option value="">Semua</option>
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Tidak Aktif</option>
                                </select>
                            </div>
                            <button id="clearLogsBtn" class="btn btn-sm btn-outline-danger" title="Clear Logs">
                                <i class="las la-trash me-1"></i>Clear Logs
                            </button>
                        </div>
                    </div>

                    <div class="card-body">

                        <!-- Aksi Tambahan -->
                        <div class="row mb-3 gy-2">
                            <div class="col-12 col-md d-flex flex-wrap gap-2">
                                <button class="btn btn-sm btn-outline-secondary">
                                    <i class="las la-file-excel me-1"></i>Import
                                </button>
                                <button class="btn btn-sm btn-outline-success">
                                    <i class="las la-file-excel me-1"></i>Export
                                </button>
                            </div>
                            <!-- Untuk log aktivitas, biasanya tidak ada aksi massal aktif/nonaktif -->
                        </div>

                        <div class="table-responsive">
                            <table id="example" class="table table-sm align-middle table-striped gs-0 gy-2 nowrap"
                                style="width:100%;">
                                <thead>
                                    <tr class="text-center text-muted text-uppercase">
                                        <th class="w-15">User</th>
                                        <th class="w-20">Action</th>
                                        <th class="w-25">Description</th>
                                        <th class="w-15">IP Address</th>
                                        <th class="w-15">User Agent</th>
                                        <th class="w-20">Timestamp</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-800 fw-bolder fs-sm-8 fs-lg-6">
                                    <!-- Data dinamis disini -->
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-primary mt-3">
                            <strong>Catatan:</strong> Fitur ini digunakan untuk memantau aktivitas pengguna sistem. Gunakan
                            filter untuk mempermudah pencarian log tertentu.
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- Content body end -->
@endsection

@section('this-page-scripts')
    <script src="{{ asset('templates/assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('templates/administration/vendor/datatables/responsive/responsive.js') }}"></script>

    <script src="{{ asset('templates/assets/plugins/datatables/lodash.min.js') }}"></script>

    @include('administration.system.logActivity.scripts.datatable-init')
    <script>
        $('#clearLogsBtn').click(function() {
            // Menampilkan SweetAlert konfirmasi
            Swal.fire({
                title: 'Are you sure?',
                text: "This will delete all log activities.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, clear it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Kirim request untuk menghapus log activity menggunakan AjaxHandler
                    AjaxHandler.sendRequest(
                        '{{ route('administrator.system.log-activity.clear') }}', // URL
                        'DELETE', // HTTP method
                        {}, // Data (kosong karena tidak ada data untuk dihapus)
                        function(response) {
                            // Callback sukses, misalnya refresh atau berikan feedback
                            $('#example').DataTable().ajax.reload();
                        },
                        function(xhr) {
                            // Callback error (opsional jika ingin melakukan sesuatu saat gagal)
                            console.error(xhr.responseText);
                        }
                    );
                }
            });
        });
    </script>
@endsection
