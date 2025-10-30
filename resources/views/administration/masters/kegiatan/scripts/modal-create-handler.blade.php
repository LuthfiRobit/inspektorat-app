<script>
    $(document).ready(function() {
        DropdownHelper.bindDependentDropdown(
            '#tahun_anggaran_id',
            '#jenis_kegiatan_id',
            '{{ route('administrator.master.jenis-kegiatan.list-by-tahun', ':id') }}',
            (item, selectedValue) => {
                const selected = selectedValue == item.id_jenis_kegiatan ? 'selected' : '';
                return `<option value="${item.id_jenis_kegiatan}" ${selected}>${item.kode_jenis} - ${item.nama_jenis}</option>`;
            }
        );
    });
</script>

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
        const form = $(this);
        const url = "{{ route('administrator.master.kegiatan.store') }}";
        const submitBtn = $(`button[type="submit"][form="createForm"]`);

        // Aktifkan loading di tombol
        submitBtn.prop('disabled', true).html(
            `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Menyimpan...`
        );

        AjaxHandler.sendStoreRequest(url, this,
            () => {
                $('#modalCreate').modal('hide');
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
            submitBtn.prop('disabled', false).html('Simpan');
        }
    });
</script>
