<script defer>
    $('#modalEdit').on('show.bs.modal', function() {
        const form = $('#editForm');
        form.find('.invalid-feedback').remove();
        form.find('.form-control').removeClass('is-invalid');
    });

    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const id = form.attr('data-id');
        const url = "{{ route('administrator.rbac.user.update', ':id') }}".replace(':id', id);

        AjaxHandler.sendUpdateRequest(
            url, this,
            () => {
                $('#modalEdit').modal('hide');
                table.ajax.reload();
            },
            res => {
                const errors = res.responseJSON?.data || {};
                ResponseHandler.handleValidationErrors(errors, form);
            }
        );
    });

    $('#modalEdit').on('hidden.bs.modal', function() {
        const form = $('#editForm');
        form[0].reset();
        $('.selectpicker').selectpicker('refresh');
        form.find('.invalid-feedback').remove();
        form.find('.form-control').removeClass('is-invalid');
    });
</script>
