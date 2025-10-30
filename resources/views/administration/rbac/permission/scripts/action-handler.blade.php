<script>
    $('.selectpicker').selectpicker();

    $('#example').on('click', '.dropdown-item', function() {
        const action = $(this).data('action');
        const dataId = $(this).data('id');
        const url = '{{ route('administrator.rbac.permission.show', ':id') }}'.replace(':id', dataId);

        if (!dataId) return ResponseHandler.handleError("ID tidak ditemukan!");

        const handlers = {
            'action_show': handleShow,
            'action_edit': handleEdit,
            // Tambahkan case baru di sini jika perlu
        };

        if (handlers[action]) {
            AjaxHandler.sendGetRequest(url, response => {
                if (response.status === 200 && response.data) {
                    handlers[action](response.data);
                } else {
                    ResponseHandler.handleError("Data tidak ditemukan.");
                }
            });
        }
    });

    function handleShow(data) {
        $('#detail_permission_name').text(data.permission_name || 'N/A');
        $('#detail_permission_description').text(data.permission_description || 'N/A');
        $('#modalDetail').modal('show');
    }

    function handleEdit(data) {
        $('#editForm').attr('data-id', data.id_permission);
        $('.selectpicker').selectpicker('refresh');
        $('#modalEdit').modal('show');
    }
</script>
