<script>
    $(document).ready(function() {
        DropdownHelper.bindDependentDropdown(
            '#kecamatan_id',
            '#desa_id',
            '{{ route('administrator.master.desa.list-by-kecamatan', ':id') }}',
            (item, selectedValue) => {
                const selected = selectedValue == item.id_desa ? 'selected' : '';
                return `<option value="${item.id_desa}" ${selected}>${item.nama_desa}</option>`;
            }
        );
    });
</script>

<!-- Handler submit untuk form -->
<script defer>
    $("#createForm").on("submit", function(e) {
        e.preventDefault();
        let form = $(this);
        let url = "{{ route('administrator.master.petugas.desa.store') }}";
        const submitBtn = $(`button[type="submit"][form="createForm"]`);

        // Store original button content and state
        const originalBtnHtml = submitBtn.html();
        const originalBtnDisabledState = submitBtn.prop('disabled');

        // Aktifkan loading di tombol
        submitBtn.prop('disabled', true).html(
            `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Menyimpan...`
        );

        AjaxHandler.sendStoreRequest(url, this, function() {
            window.location.href = "{{ route('administrator.master.petugas.desa.index') }}";
        }, function(response) {
            let errors = response.responseJSON?.data || {};
            ResponseHandler.handleValidationErrors(errors, form);
            // Re-enable the button and restore its original content
            submitBtn.prop('disabled', originalBtnDisabledState).html(originalBtnHtml);
        });
    });
</script>
