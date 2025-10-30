<script>
    // ===========================
    // = Save Permission Handler =
    // ===========================
    $('#permissionForm').on('submit', function(e) {
        e.preventDefault();

        const roleId = $('#permissionForm').attr('data-id');
        const selectedPermissions = getSelectedPermissions();

        saveRolePermissions(roleId, selectedPermissions);
    });

    function getSelectedPermissions() {
        const selectedPermissions = [];
        const checkboxes = document.querySelectorAll(
            '#permissions_list input[type="checkbox"][name="permissions[]"]:checked'
        );

        checkboxes.forEach(checkbox => {
            selectedPermissions.push(checkbox.value);
        });

        return selectedPermissions;
    }

    function saveRolePermissions(roleId, permissions) {
        const url = '{{ route('administrator.rbac.role.store-role-permission', ':id') }}'.replace(':id', roleId);

        let formData = new FormData();
        permissions.forEach(permission => formData.append('permissions[]', permission));
        formData.append('_token', '{{ csrf_token() }}');

        AjaxHandler.sendRequest(
            url,
            'POST',
            formData,
            function(response) {
                ResponseHandler.handleSuccess("Role permissions berhasil disimpan.");
                $('#modalPermission').modal('hide');
                $('#example').DataTable().ajax.reload();
            },
            function(xhr) {
                let errors = xhr.responseJSON?.data || {};
                ResponseHandler.handleValidationErrors(errors, null);
            }
        );
    }
</script>
