<script>
    $(document).on('change', '.datatable-status-switcher', function() {
        const checkbox = $(this);
        const id = checkbox.data('id');
        const isChecked = checkbox.is(':checked');
        const newStatus = isChecked ? 'active' : 'inactive';
        const url = '{{ route('administrator.rbac.user.update-status') }}'; // endpoint updateStatus satuan

        Swal.fire({
            title: isChecked ? 'Aktifkan Pengguna?' : 'Nonaktifkan Pengguna?',
            text: isChecked ?
                "Apakah Anda yakin ingin mengaktifkan pengguna ini?" :
                "Apakah Anda yakin ingin menonaktifkan pengguna ini? Pengguna tidak akan dapat mengakses aplikasi.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: isChecked ? 'Ya, Aktifkan!' : 'Ya, Nonaktifkan!',
            cancelButtonText: 'Batal'
        }).then(result => {
            if (result.isConfirmed) {
                const originalState = checkbox.prop('checked');

                AjaxHandler.sendRequest(
                    url, 'POST', JSON.stringify({
                        id: id, // <- sesuai dengan validasi di updateStatus
                        status: newStatus
                    }),
                    () => {
                        table.ajax.reload(); // Reload DataTable
                    },
                    xhr => {
                        const errors = xhr.responseJSON?.data || {};
                        ResponseHandler.handleValidationErrors(errors, '');
                        checkbox.prop('checked', !originalState); // Kembalikan kondisi checkbox
                    },
                    null, {
                        'Content-Type': 'application/json'
                    }
                );
            } else {
                checkbox.prop('checked', !isChecked); // Batalkan perubahan jika user cancel
            }
        });
    });
</script>
