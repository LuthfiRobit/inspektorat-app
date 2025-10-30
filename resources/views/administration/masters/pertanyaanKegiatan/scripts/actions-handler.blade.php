<script>
    $('#example').on('click', '.dropdown-item', function() {
        const action = $(this).data('action');
        const dataId = $(this).data('id');
        const url = '{{ route('administrator.master.pertanyaan-kegiatan.show', ':id') }}'.replace(':id',
            dataId);

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
        $('#detail_pertanyaan').text(data.pertanyaan || 'N/A');
        $('#detail_status').text(data.status || 'N/A');
        $('#detail_urutan').text(data.urutan || 'N/A');
        $('#detail_kode_kegiatan').text(data.kode_kegiatan || 'N/A');
        $('#detail_nama_kegiatan').text(data.nama_kegiatan || 'N/A');
        $('#modalDetail').modal('show');
    }

    function handleEdit(data) {
        $('#editForm').attr('data-id', data.id_pertanyaan);
        $('.selectpicker').selectpicker('refresh');
        $('#modalEdit').modal('show');
    }

    // Tambahkan handler tambahan jika ada case baru
</script>
