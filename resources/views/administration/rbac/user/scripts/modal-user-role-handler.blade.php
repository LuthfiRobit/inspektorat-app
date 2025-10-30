<script>
    $('#userRoleForm').on('submit', function(e) {
        e.preventDefault();
        const userId = $(this).attr('data-id');
        const selectedRoles = getSelectedRoles();
        const url = '{{ route('administrator.rbac.user.store-user-role', ':id') }}'.replace(':id', userId);

        const formData = new FormData();
        selectedRoles.forEach(role => formData.append('roles[]', role));
        formData.append('_token', '{{ csrf_token() }}');

        AjaxHandler.sendRequest(
            url, 'POST', formData,
            () => {
                $('#modalUserRole').modal('hide');
                table.ajax.reload();
            },
            xhr => {
                const errors = xhr.responseJSON?.data || {};
                ResponseHandler.handleValidationErrors(errors, null);
            }
        );
    });

    function getSelectedRoles() {
        return $('#roles_list input[name="roles[]"]:checked')
            .map(function() {
                return this.value;
            }).get();
    }
</script>
