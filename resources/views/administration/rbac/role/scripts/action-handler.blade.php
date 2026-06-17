<script>
    // ===========================
    // = Existing Functions (Keep as is) =
    // ===========================
    function handleActionShow(url) {
        AjaxHandler.sendGetRequest(url, function(response) {
            if (response.status === 200 && response.data) {
                $('#detail_role_scope').text(response.data.role_scope || 'N/A');
                $('#detail_role_name').text(response.data.role_name || 'N/A');
                $('#detail_role_description').text(response.data.role_description || 'N/A');
                $('#modalDetail').modal('show');
            } else {
                ResponseHandler.handleError("Data tidak ditemukan.");
            }
        });
    }

    function handleActionEdit(url) {
        AjaxHandler.sendGetRequest(url, function(response) {
            if (response.status === 200 && response.data) {
                $('#editForm').attr('data-id', response.data.id_role);
                $('.selectpicker').selectpicker('refresh');
                $('#modalEdit').modal('show');
            } else {
                ResponseHandler.handleError("Data tidak ditemukan.");
            }
        });
    }

    function handleActionPermission(dataId) {
        const editUrl = '{{ route('administrator.rbac.role.edit', ':id') }}'.replace(':id', dataId);
        window.location.href = editUrl;
    }

    function handleActionDelete(dataId) {
        let routeTemplate = '{{ route('administrator.rbac.role.destroy', ':id') }}';
        const url = routeTemplate.replace(':id', dataId);

        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data role/jabatan ini akan dihapus!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function (response) {
                        if (response.status === 200 || response.success) {
                            ResponseHandler.handleSuccess("Data berhasil dihapus!");
                            $('#example').DataTable().ajax.reload(null, false);
                        } else {
                            ResponseHandler.handleError(response.message || "Gagal menghapus data.");
                        }
                    },
                    error: function (xhr) {
                        ResponseHandler.handleError(xhr.responseJSON?.message || "Terjadi kesalahan.");
                    }
                });
            }
        });
    }

    // ========================
    // = Event Handlers =
    // ========================
    $('#example').on('click', '.dropdown-item', function() {
        const action = $(this).data('action');
        const dataId = $(this).data('id');

        if (!dataId) {
            ResponseHandler.handleError("ID tidak ditemukan!");
            return;
        }

        switch (action) {
            case 'action_show':
                handleActionShow('{{ route('administrator.rbac.role.show', ':id') }}'.replace(':id', dataId));
                break;
            case 'action_edit':
                handleActionEdit('{{ route('administrator.rbac.role.show', ':id') }}'.replace(':id', dataId));
                break;
            case 'action_permission':
                handleActionPermission(dataId);
                break;
            case 'action_delete':
                handleActionDelete(dataId);
                break;
        }
    });
</script>
