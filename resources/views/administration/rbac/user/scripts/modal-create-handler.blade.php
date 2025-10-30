<script defer>
    $('#modalCreate').on('show.bs.modal', function() {
        const form = $('#createForm');
        form[0].reset();
        form.find('.selectpicker').selectpicker('refresh');
        form.find('.invalid-feedback').remove();
        form.find('.form-control').removeClass('is-invalid');
    });

    $('#createForm').on('submit', function(e) {
        e.preventDefault();
        AjaxHandler.sendStoreRequest(
            "{{ route('administrator.rbac.user.store') }}", this,
            () => {
                $('#modalCreate').modal('hide');
                table.ajax.reload();
            },
            res => {
                const errors = res.responseJSON?.data || {};
                ResponseHandler.handleValidationErrors(errors, $(this));
            }
        );
    });
</script>
