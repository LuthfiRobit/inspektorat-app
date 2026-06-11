<script>
    $(document).ready(function() {
        const dataUrl = "{{ route('administrator.master.wilayah-binaan.data', $petugas->id_petugas) }}";

        // Initialize DataTable for assigned areas list
        const table = $('#table-assigned').DataTable({
            columns: [
                { 
                    data: null,
                    className: 'text-center align-middle',
                    render: function (data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                { data: 'nama', className: 'text-start align-middle text-dark font-w600' },
                { 
                    data: 'pivot_id',
                    className: 'text-center align-middle',
                    orderable: false,
                    searchable: false,
                    render: function (data) {
                        return `<button type="button" class="btn btn-danger btn-xs btn-delete-item" data-id="${data}" data-bs-toggle="tooltip" title="Hapus wilayah binaan">
                                    <i class="las la-trash-alt me-1"></i>Hapus
                                </button>`;
                    }
                }
            ],
            language: {
                searchPlaceholder: "Cari...",
                search: '',
                emptyTable: "Belum ada wilayah binaan yang ditugaskan."
            },
            drawCallback: function () {
                $('[data-bs-toggle="tooltip"]').tooltip();
            }
        });

        // Initialize selectpicker for dropdowns
        $('.selectpicker').selectpicker({
            width: '100%'
        });

        // Function to load/reload data dynamically
        function loadWilayahData() {
            $.ajax({
                url: dataUrl,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 200) {
                        // 1. Redraw DataTable
                        table.clear();
                        table.rows.add(response.data.assigned);
                        table.draw();

                        // 2. Repopulate selectpicker
                        let optionsHtml = '';
                        response.data.available.forEach(item => {
                            optionsHtml += `<option value="${item.id}">${item.nama}</option>`;
                        });
                        $('#wilayah_ids').html(optionsHtml);
                        $('#wilayah_ids').selectpicker('refresh');
                    } else {
                        ResponseHandler.handleError(response.message);
                    }
                },
                error: function(xhr) {
                    ResponseHandler.handleHttpError(xhr);
                }
            });
        }

        // Load data on page load
        loadWilayahData();

        // Handle Add Wilayah (No reload)
        $('#form-assign').on('submit', function(e) {
            e.preventDefault();
            const url = "{{ route('administrator.master.wilayah-binaan.bulk') }}";
            const submitBtn = $('#btn-submit');

            submitBtn.prop('disabled', true).html(
                `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Menyimpan...`
            );

            // Use global AjaxHandler for post bulk store
            AjaxHandler.sendStoreRequest(url, this, 
                () => {
                    submitBtn.prop('disabled', false).html('<i class="las la-plus-circle me-1"></i>Tambahkan Area Binaan');
                    loadWilayahData();
                },
                res => {
                    submitBtn.prop('disabled', false).html('<i class="las la-plus-circle me-1"></i>Tambahkan Area Binaan');
                }
            );
        });

        // Handle Delete Wilayah (No reload)
        $('#table-assigned').on('click', '.btn-delete-item', function() {
            const id = $(this).data('id');
            const url = "{{ route('administrator.master.wilayah-binaan.destroy', ':id') }}".replace(':id', id);
            
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Wilayah ini akan dihapus dari daftar binaan petugas.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const token = $('meta[name="csrf-token"]').attr('content');
                    
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: token
                        },
                        success: function(response) {
                            if (response.status === 200) {
                                ResponseHandler.handleSuccess(response.message, () => {
                                    loadWilayahData();
                                });
                            } else {
                                ResponseHandler.handleError(response.message);
                            }
                        },
                        error: function(xhr) {
                            ResponseHandler.handleHttpError(xhr);
                        }
                    });
                }
            });
        });
    });
</script>
