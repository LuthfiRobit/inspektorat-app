<script>
    $('#example').on('click', '.dropdown-item', function() {
        const action = $(this).data('action');
        const dataId = $(this).data('id');
        const url = '{{ route('administrator.master.tahun-anggaran.show', ':id') }}'.replace(':id', dataId);

        if (!dataId) return ResponseHandler.handleError("ID tidak ditemukan!");

        const handlers = {
            'action_show': handleShow,
            'action_edit': handleEdit,
            // Tambahkan case baru di sini
        };

        if (handlers[action]) {
            AjaxHandler.sendGetRequest(url, response => {
                if (response.status === 200 && response.data) {
                    handlers[action](response.data);
                } else {
                    ResponseHandler.handleError("Data tidak ditemukan.");
                }
            });
        }
    });

    function handleShow(data) {
        $('#detail_tahun').text(data.tahun || 'N/A');
        $('#detail_keterangan').text(data.keterangan || 'N/A');
        $('#detail_status').text(data.status || 'N/A');
        $('#modalDetail').modal('show');
    }

    function handleEdit(data) {
        $('#editForm').attr('data-id', data.id_tahun_anggaran);
        $('.selectpicker').selectpicker('refresh');
        $('#modalEdit').modal('show');
    }

    // Tambahkan handler tambahan jika ada case baru
</script>
