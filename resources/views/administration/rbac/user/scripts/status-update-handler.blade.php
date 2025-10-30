<script>
    $('.btn-update-status').click(function() {
        const $btn = $(this);
        const status = $(this).data('status');
        const url = '{{ route('administrator.rbac.user.update-status-multiple') }}';
        const selectedIds = $('.table-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (!selectedIds.length) {
            return ResponseHandler.handleError('Anda harus memilih setidaknya satu data terlebih dahulu.');
        }

        const originalHtml = $btn.html();
        $btn.prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...'
        );

        AjaxHandler.sendRequest(
            url, 'POST', JSON.stringify({
                ids: selectedIds,
                status
            }),
            () => {
                table.ajax.reload();
                $('#selectAll').prop('checked', false);
                $btn.prop('disabled', false).html(originalHtml); // Reset tombol
            },
            xhr => {
                const errors = xhr.responseJSON?.data || {};
                ResponseHandler.handleValidationErrors(errors, '');
                $btn.prop('disabled', false).html(originalHtml); // Reset tombol
            },
            null, {
                'Content-Type': 'application/json'
            }
        );
    });
</script>
