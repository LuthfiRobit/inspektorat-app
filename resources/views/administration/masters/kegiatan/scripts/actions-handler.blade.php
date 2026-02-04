<script>
    $(document).ready(function () {
        const MONTH_NAMES = [
            "", "Januari", "Februari", "Maret", "April", "Mei", "Juni",
            "Juli", "Agustus", "September", "Oktober", "November", "Desember"
        ];

        // Gunakan e untuk preventDefault
        $(document).on('click', '.action-trigger', function (e) {
            e.preventDefault();

            const action = $(this).data('action');
            const dataId = $(this).data('id');
            const url = '{{ route('administrator.master.kegiatan.show', ':id') }}'.replace(':id', dataId);

            console.log('Action Clicked:', action, 'ID:', dataId); // Debug logging

            if (!dataId) {
                console.error('ID not found on element');
                return ResponseHandler.handleError("ID tidak ditemukan!");
            }

            const handlers = {
                'action_show': handleShow,
                'action_edit': handleEdit,
                // Tambahkan case baru di sini
            };

            if (handlers[action]) {
                // Show loading/processing state if needed
                AjaxHandler.sendGetRequest(url, response => {
                    console.log('AJAX Response:', response); // Debug logging
                    if (response.status === 200 && response.data) {
                        handlers[action](response.data);
                    } else {
                        console.error('Invalid response structure:', response);
                        ResponseHandler.handleError("Data tidak ditemukan.");
                    }
                });
            } else {
                console.warn('No handler for action:', action);
            }
        });

        function handleShow(data) {
            try {
                console.log('Rendering Detail Modal', data);
                const kegiatan = data.kegiatan || {};
                const pertanyaan = data.pertanyaan || [];
                const year = kegiatan.tahun || '-';

                $('#detail_kode_jenis').text(kegiatan.kode_jenis || '-');
                $('#detail_nama_jenis').text(kegiatan.nama_jenis || '-');
                $('#detail_tahun').text(year);
                $('#detail_nama_kegiatan').text(kegiatan.nama_kegiatan || '-');
                $('#detail_status').text(kegiatan.status === 'active' ? 'Aktif' : 'Tidak Aktif');

                let startMonthName = '', endMonthName = '', startDate = '', endDate = '', limitDays = '';

                // Safe safe parsing
                const mapMonth = (idx) => MONTH_NAMES[idx] || '-';

                if (kegiatan.frekuensi_pelaporan) {
                    // Rutin
                    startMonthName = mapMonth(kegiatan.bulan_mulai);
                    endMonthName = mapMonth(kegiatan.bulan_selesai);
                    $('#detail_nama_bulan').text(`Rutin (Setiap ${kegiatan.frekuensi_pelaporan} Bulan)`);

                    startDate = `Tgl. ${kegiatan.tanggal_mulai || '?'} ${startMonthName} ${year}`;
                    endDate = `Tgl. ${kegiatan.tanggal_selesai || '?'} ${endMonthName} ${year}`;
                    limitDays = kegiatan.batas_akhir_upload;
                } else {
                    // Insidentil
                    startMonthName = mapMonth(kegiatan.bulan);
                    endMonthName = startMonthName;
                    $('#detail_nama_bulan').text(startMonthName);

                    startDate = `Tgl. ${kegiatan.tanggal_mulai || '?'} ${startMonthName} ${year}`;
                    endDate = `Tgl. ${kegiatan.tanggal_selesai || '?'} ${endMonthName} ${year}`;
                    limitDays = kegiatan.batas_akhir_upload;
                }

                $('#detail_tanggal_mulai').text(startDate);
                $('#detail_tanggal_selesai').text(endDate);

                const limitInfo = limitDays ? `+ ${limitDays} hari dari tanggal selesai` : '-';
                $('#detail_batas_upload').text(limitInfo);

                $('#detail_dasar_hukum').text(kegiatan.dasar_hukum || '-');

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

                // FIX: Stacking Context Issue - Move modal to body
                const modalEl = document.getElementById('modalDetail');
                if (modalEl) {
                    document.body.appendChild(modalEl);

                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        const modal = new bootstrap.Modal(modalEl);
                        modal.show();
                    } else {
                        $(modalEl).modal('show');
                    }
                } else {
                    console.error('Modal element #modalDetail not found!');
                }
            } catch (err) {
                console.error('Error in handleShow:', err);
                ResponseHandler.handleError("Terjadi kesalahan saat menampilkan detail.");
            }
        }

        function handleEdit(data) {
            try {
                const kegiatan = data.kegiatan;
                $('#editForm').attr('data-id', kegiatan.id_kegiatan);

                // Set Tahun Anggaran terlebih dahulu
                $('#edit_tahun_anggaran_id').val(kegiatan.tahun_anggaran_id).selectpicker('refresh');

                // Bind Jenis Kegiatan berdasarkan Tahun Anggaran
                DropdownHelper.bindDependentDropdown(
                    '#edit_tahun_anggaran_id',
                    '#edit_jenis_kegiatan_id',
                    '{{ route('administrator.master.jenis-kegiatan.list-by-tahun', ':id') }}',
                    function (item, selectedId) {
                        const selected = selectedId == item.id_jenis_kegiatan ? 'selected' : '';
                        return `<option value="${item.id_jenis_kegiatan}" ${selected}>${item.kode_jenis} - ${item.nama_jenis}</option>`;
                    },
                    kegiatan.jenis_kegiatan_id
                );

                $('#edit_kode_kegiatan').val(kegiatan.kode_kegiatan || '');
                $('#edit_nama_kegiatan').val(kegiatan.nama_kegiatan || '');

                // Handle Logic Frequency vs Bulan (Rutin vs Insidentil)
                if (kegiatan.frekuensi_pelaporan) {
                    // Mode Rutin
                    $('#edit_jenisRutin').prop('checked', true);
                    $('#edit_groupInsidentil').addClass('d-none');
                    $('#edit_groupRutin').removeClass('d-none');

                    // Populate Rutin Fields
                    $('#edit_frekuensi_pelaporan').val(kegiatan.frekuensi_pelaporan).selectpicker('refresh');
                    $('#edit_bulan_mulai').val(kegiatan.bulan_mulai || 1).selectpicker('refresh');
                    $('#edit_bulan_selesai').val(kegiatan.bulan_selesai || 12).selectpicker('refresh');
                    $('#edit_tanggal_mulai_rutin').val(kegiatan.tanggal_mulai || '');
                    $('#edit_tanggal_selesai_rutin').val(kegiatan.tanggal_selesai || '');

                    // Reset Insidentil
                    $('#edit_bulan').val('').selectpicker('refresh');
                    $('#edit_tanggal_mulai_insidentil').val('');
                    $('#edit_tanggal_selesai_insidentil').val('');
                } else {
                    // Mode Insidentil
                    $('#edit_jenisInsidentil').prop('checked', true);
                    $('#edit_groupInsidentil').removeClass('d-none');
                    $('#edit_groupRutin').addClass('d-none');

                    // Populate Insidentil Fields
                    $('#edit_bulan').val(kegiatan.bulan || '').selectpicker('refresh');
                    $('#edit_tanggal_mulai_insidentil').val(kegiatan.tanggal_mulai || '');
                    $('#edit_tanggal_selesai_insidentil').val(kegiatan.tanggal_selesai || '');

                    // Reset Rutin
                    $('#edit_frekuensi_pelaporan').val('').selectpicker('refresh');
                    $('#edit_bulan_mulai').val(1).selectpicker('refresh');
                    $('#edit_bulan_selesai').val(12).selectpicker('refresh');
                    $('#edit_tanggal_mulai_rutin').val('');
                    $('#edit_tanggal_selesai_rutin').val('');
                }

                $('#edit_batas_akhir_upload').val(kegiatan.batas_akhir_upload || '');
                $('#edit_dasar_hukum').val(kegiatan.dasar_hukum || '');

                if (kegiatan.status === 'active') {
                    $('#edit_statusActive').prop('checked', true);
                } else {
                    $('#edit_statusInactive').prop('checked', true);
                }

                $('.selectpicker').selectpicker('refresh');

                // FIX: Stacking Context Issue - Move modal to body
                const modalEl = document.getElementById('modalEdit');
                if (modalEl) {
                    document.body.appendChild(modalEl);

                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        const modal = new bootstrap.Modal(modalEl);
                        modal.show();
                    } else {
                        $(modalEl).modal('show');
                    }
                } else {
                    console.error('Modal element #modalEdit not found!');
                }
            } catch (err) {
                console.error('Error in handleEdit:', err);
                ResponseHandler.handleError("Terjadi kesalahan saat menampilkan form edit.");
            }
        }
    });
</script>