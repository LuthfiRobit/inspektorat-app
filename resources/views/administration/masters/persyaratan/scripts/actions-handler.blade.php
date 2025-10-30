<script>
    $('#example').on('click', '.dropdown-item', function() {
        const action = $(this).data('action');
        const dataId = $(this).data('id');
        const url = '{{ route('administrator.master.persyaratan.show', ':id') }}'.replace(':id',
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
        $('#detail_nama_persyaratan').text(data.nama_persyaratan || 'N/A');
        $('#detail_pertanyaan').text(data.pertanyaan || 'N/A');
        $('#detail_deskripsi').text(data.deskripsi || 'N/A');
        $('#detail_tipe').text(data.tipe === 'wajib' ? 'Wajib' : 'Opsional');
        $('#detail_urutan').text(data.urutan || 'N/A');
        $('#detail_status').text(data.status === 'active' ? 'Aktif' : 'Tidak Aktif');
        $('#detail_kode_kegiatan').text(data.kode_kegiatan || 'N/A');
        $('#detail_nama_kegiatan').text(data.nama_kegiatan || 'N/A');

        // Tampilkan link template jika ada
        if (data.template_persyaratan) {
            const filePath = "{{ asset('') }}" + 'uploads/' + data.template_persyaratan;
            $('#detail_template').html(`
            <a href="${filePath}" target="_blank" class="d-flex align-items-center text-decoration-none">
                <i class="fas fa-eye me-2"></i> Lihat Template
            </a>
        `);
        } else {
            $('#detail_template').text('-');
        }

        $('#modalDetail').modal('show');
    }

    function handleEdit(data) {
        console.log(data);

        $('#editForm').attr('data-id', data.id_persyaratan);

        $('#edit_kegiatan_id').val(data.kegiatan_id).selectpicker('refresh');

        // Bind Jenis Kegiatan berdasarkan Tahun Anggaran, dan auto-select jenis kegiatan yang sesuai
        DropdownHelper.bindDependentDropdown(
            '#edit_kegiatan_id',
            '#edit_pertanyaan_kegiatan_id',
            '{{ route('administrator.master.pertanyaan-kegiatan.list-by-kegiatan', ':id') }}',
            function(item, selectedId) {
                const selected = selectedId == item.id_pertanyaan ? 'selected' : '';
                return `<option value="${item.id_pertanyaan}" ${selected}>${item.pertanyaan}</option>`;
            },
            data.pertanyaan_kegiatan_id // <= ini harus angka/id yang cocok
        );

        if (data.template_persyaratan) {
            const filePath = "{{ asset('') }}" + 'uploads/' + data.template_persyaratan;
            $('#link-container').html(
                `<a href="${filePath}" class="d-flex align-items-center" id="lihat_foto" target="_blank">
                    <i class="fas fa-eye me-2"></i><span>Lihat</span>
                </a>`
            );
        } else {
            $('#link-container').html('-');
        }

        $('.selectpicker').selectpicker('refresh');
        $('#modalEdit').modal('show');
    }

    // Tambahkan handler tambahan jika ada case baru
</script>
