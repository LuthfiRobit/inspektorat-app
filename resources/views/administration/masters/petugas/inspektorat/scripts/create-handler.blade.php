<!-- Handler submit untuk form -->
<script defer>
    $("#createForm").on("submit", function(e) {
        e.preventDefault();
        let form = $(this);
        let url = "{{ route('administrator.master.petugas.inspektorat.store') }}";
        const submitBtn = $(`button[type="submit"][form="createForm"]`);

        // Store original button content and state
        const originalBtnHtml = submitBtn.html();
        const originalBtnDisabledState = submitBtn.prop('disabled');

        // Aktifkan loading di tombol
        submitBtn.prop('disabled', true).html(
            `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Menyimpan...`
        );

        AjaxHandler.sendStoreRequest(url, this, function() {
            window.location.href = "{{ route('administrator.master.petugas.inspektorat.index') }}";
        }, function(response) {
            let errors = response.responseJSON?.data || {};
            ResponseHandler.handleValidationErrors(errors, form);
            // Re-enable the button and restore its original content
            submitBtn.prop('disabled', originalBtnDisabledState).html(originalBtnHtml);
        });
    });
</script>
