<script>
    $('#syncPermissionBtn').on('click', function() {
        Swal.fire({
            title: 'Are you sure?',
            text: "This will create new permissions from undefined routes.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, sync now',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                performPermissionSync();
            }
        });
    });

    function performPermissionSync() {
        $.ajax({
            url: "{{ route('administrator.rbac.permission.sync') }}",
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            beforeSend: function() {
                $('#syncPermissionBtn').prop('disabled', true).text('Processing...');
                $('#syncResult').addClass('d-none').removeClass('alert-danger alert-info').text('');
            },
            success: function(res) {
                const output = res?.data?.output ?? '';
                const message = res?.message ?? 'Permission sync completed.';
                $('#syncResult')
                    .removeClass('d-none')
                    .addClass('alert alert-info')
                    .html('<strong>' + message + '</strong><br>' + output.replace(/\n/g, '<br>'));

                Swal.fire({
                    icon: 'success',
                    title: 'Synced!',
                    text: message
                });
            },
            error: function(xhr) {
                $('#syncResult')
                    .removeClass('d-none alert-info')
                    .addClass('alert alert-danger')
                    .text('Failed to sync permissions.');

                Swal.fire({
                    icon: 'error',
                    title: 'Sync Failed',
                    text: 'An error occurred during permission sync.'
                });
            },
            complete: function() {
                $('#syncPermissionBtn').prop('disabled', false).text('Sync Permissions');
            }
        });
    }
</script>
