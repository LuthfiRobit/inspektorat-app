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
        }
    });
</script>
