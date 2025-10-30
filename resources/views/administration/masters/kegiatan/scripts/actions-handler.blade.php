<script>
    $('#example').on('click', '.dropdown-item', function() {
        const action = $(this).data('action');
        const dataId = $(this).data('id');
        const url = '{{ route('administrator.master.kegiatan.show', ':id') }}'.replace(':id', dataId);

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
        const kegiatan = data.kegiatan;
        const pertanyaan = data.pertanyaan || [];

        $('#detail_kode_jenis').text(kegiatan.kode_jenis || 'N/A');
        $('#detail_nama_jenis').text(kegiatan.nama_jenis || 'N/A');
        $('#detail_tahun').text(kegiatan.tahun || 'N/A');
        $('#detail_nama_kegiatan').text(kegiatan.nama_kegiatan || 'N/A');
        $('#detail_status').text(kegiatan.status === 'active' ? 'Aktif' : 'Tidak Aktif');
        $('#detail_nama_bulan').text(kegiatan.nama_bulan || 'N/A');

        // Format tanggal dengan prefix "Tgl. "
        $('#detail_tanggal_mulai').text(kegiatan.tanggal_mulai ? `Tgl. ${kegiatan.tanggal_mulai}` : 'N/A');
        $('#detail_tanggal_selesai').text(kegiatan.tanggal_selesai ? `Tgl. ${kegiatan.tanggal_selesai}` : 'N/A');
        $('#detail_batas_upload').text(kegiatan.batas_akhir_upload ? `Tgl. ${kegiatan.batas_akhir_upload}` : 'N/A');

        $('#detail_dasar_hukum').text(kegiatan.dasar_hukum || 'N/A');

        if (pertanyaan.length > 0) {
            let listHTML = '<ul class="mb-0">';
            pertanyaan.forEach(item => {
                listHTML += `<li>${item.pertanyaan}</li>`;
            });
            listHTML += '</ul>';
            $('#detail_list_pertanyaan').html(listHTML);
        } else {
            $('#detail_list_pertanyaan').text('Tidak ada pertanyaan');
        }

        $('#modalDetail').modal('show');
    }

    function handleEdit(data) {
        const kegiatan = data.kegiatan;
        $('#editForm').attr('data-id', kegiatan.id_kegiatan);

        // Set Tahun Anggaran terlebih dahulu
        $('#edit_tahun_anggaran_id').val(kegiatan.tahun_anggaran_id).selectpicker('refresh');

        // Bind Jenis Kegiatan berdasarkan Tahun Anggaran, dan auto-select jenis kegiatan yang sesuai
        DropdownHelper.bindDependentDropdown(
            '#edit_tahun_anggaran_id',
            '#edit_jenis_kegiatan_id',
            '{{ route('administrator.master.jenis-kegiatan.list-by-tahun', ':id') }}',
            function(item, selectedId) {
                const selected = selectedId == item.id_jenis_kegiatan ? 'selected' : '';
                return `<option value="${item.id_jenis_kegiatan}" ${selected}>${item.kode_jenis} - ${item.nama_jenis}</option>`;
            },
            kegiatan.jenis_kegiatan_id
        );

        $('#edit_kode_kegiatan').val(kegiatan.kode_kegiatan || '');
        $('#edit_nama_kegiatan').val(kegiatan.nama_kegiatan || '');
        $('#edit_bulan').val(kegiatan.bulan || '').selectpicker('refresh');
        $('#edit_tanggal_mulai').val(kegiatan.tanggal_mulai || '');
        $('#edit_tanggal_selesai').val(kegiatan.tanggal_selesai || '');
        $('#edit_batas_akhir_upload').val(kegiatan.batas_akhir_upload || '');
        $('#edit_dasar_hukum').val(kegiatan.dasar_hukum || '');

        if (kegiatan.status === 'active') {
            $('#edit_statusActive').prop('checked', true);
        } else {
            $('#edit_statusInactive').prop('checked', true);
        }

        $('.selectpicker').selectpicker('refresh');
        $('#modalEdit').modal('show');
    }

    // Tambahkan handler tambahan jika ada case baru
</script>
