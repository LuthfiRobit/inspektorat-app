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
        const url = "{{ route('administrator.master.kegiatan.update', ':id') }}".replace(':id', id);
        const submitBtn = $(`button[type="submit"][form="editForm"]`);

        // Aktifkan loading di tombol
        submitBtn.prop('disabled', true).html(
            `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Memproses...`
        );

        AjaxHandler.sendUpdateRequest(url, this,
            () => {
                $('#modalEdit').modal('hide');
                table.ajax.reload();
                resetSubmitButton();
            },
            res => {
                const errors = res.responseJSON?.data || {};
                ResponseHandler.handleValidationErrors(errors, form);
                resetSubmitButton();
            }
        );

        function resetSubmitButton() {
            submitBtn.prop('disabled', false).html('Simpan Perubahan');
        }
    });

    $('#modalEdit').on('hidden.bs.modal', function() {
        const form = $('#editForm');
        form[0].reset();
        $('.selectpicker').selectpicker('refresh');
        form.find('.invalid-feedback').remove();
        form.find('.form-control').removeClass('is-invalid');
    });
</script>
