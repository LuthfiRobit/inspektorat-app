<script>
    $('.selectpicker').selectpicker();

    $('#example').on('click', '.dropdown-item', function () {
        const action = $(this).data('action');
        const dataId = $(this).data('id');
        const url = '{{ route('administrator.master.petugas.inspektorat.show', ':id') }}'.replace(':id',
            dataId);

        if (!dataId) return ResponseHandler.handleError("ID tidak ditemukan!");

        const handlers = {
            'action_show': handleShow,
            'action_edit': handleEdit,
            // Tambahkan handler lain di sini
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
        const baseUrl = "{{ asset('') }}";

        // Helper date formatter
        const formatDate = (dateString) => {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });
        };

        // Isi data ke modal
        $('#detail_nama_lengkap').text(data.nama_lengkap || 'N/A');
        $('#detail_nip').text(data.nip || 'N/A');
        $('#detail_jabatan').text(data.jabatan || 'N/A');
        $('#detail_unit_kerja').text(data.unit_kerja || 'N/A');
        $('#detail_alamat').text(data.alamat || 'N/A');
        $('#detail_no_telp').text(data.no_telp || 'N/A');
        $('#detail_email').text(data.email || 'N/A');

        // Status Petugas
        const statusMap = {
            'active': '<span class="badge badge-success">AKTIF</span>',
            'inactive': '<span class="badge badge-danger">TIDAK AKTIF</span>'
        };
        $('#detail_status').html(statusMap[data.status] || '<span class="badge badge-dark">-</span>');

        // Akses Login
        const userStatusMap = {
            'active': '<span class="badge badge-success">AKTIF</span>',
            'inactive': '<span class="badge badge-danger">TIDAK AKTIF</span>'
        };
        $('#detail_user_status').html(userStatusMap[data.user_status] || '<span class="badge badge-dark">NONAKTIF</span>');

        $('#detail_tanggal_awal').text(formatDate(data.tanggal_awal));
        $('#detail_tanggal_akhir').text(formatDate(data.tanggal_akhir));
        $('#detail_nama_kecamatan').text(data.nama_kecamatan || '-');
        $('#detail_nama_desa').text(data.nama_desa || '-');

        // Tampilkan foto
        if (data.foto_petugas) {
            $('#detail_foto_petugas')
                .attr('src', baseUrl + 'uploads/' + data.foto_petugas)
                .show();
        } else {
            $('#detail_foto_petugas')
                .attr('src', baseUrl + 'templates/assets/media/avatars/no-image.png') // fallback default image
                .show();
        }

        // Tampilkan modal
        $('#modalDetail').modal('show');
    }

    function handleEdit(data) {
        const editUrl = '{{ route('administrator.master.petugas.inspektorat.edit', ':id') }}'.replace(':id', data
            .id_petugas);
        window.location.href = editUrl;
    }

    // Tambahkan handler tambahan di sini jika ada action baru
</script>