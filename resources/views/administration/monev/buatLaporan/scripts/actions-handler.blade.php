<script>
    $(document).ready(function() {
        // Handler untuk aksi dropdown di datatable
        $('#example').on('click', '.dropdown-item', function() {
            const action = $(this).data('action');
            const dataId = $(this).data('id');

            // if (!dataId) {
            //     ResponseHandler.handleError("ID tidak ditemukan!");
            //     return;
            // }

            const $row = $(this).closest('tr');
            const rowData = table.row($row).data(); // Ambil data objek row

            const url = '{{ route('administrator.monev.laporan.show', ':id') }}'.replace(':id',
                dataId);

            const handlers = {
                'action_show': handleShow,
                // Tambahkan handler untuk aksi lain jika diperlukan
            };

            if (handlers[action]) {
                // Tampilkan loading state
                $('#modalDetail').modal('show');
                $('#detail_list_pertanyaan').html(`
                    <div class="text-center text-muted">
                        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                        Memuat data...
                    </div>
                `);

                AjaxHandler.sendGetRequest(url, response => {
                    if (response.status === 200 && response.data) {
                        handlers[action](response.data, rowData); // ✅ Perbaikan di sini
                    } else {
                        ResponseHandler.handleError("Data tidak ditemukan.");
                        $('#modalDetail').modal('hide');
                    }
                }, error => {
                    ResponseHandler.handleError("Gagal memuat data detail.");
                    $('#modalDetail').modal('hide');
                });
            }
        });

        function handleShow(data, rowData) {
            ResponseHandler.handleInfo("Dalam Pengembangan.");

            const kegiatan = data.kegiatan;
            const laporan = data.laporan || {}; // Fallback jadi objek kosong jika null
            const pertanyaan = data.pertanyaan || [];

            // // Informasi Umum
            // $('#detail_tahun_anggaran').text(kegiatan.tahun || 'N/A');
            // $('#detail_periode').text(`${laporan.tahun || kegiatan.tahun} - ${kegiatan.nama_bulan || 'N/A'}`);
            // $('#detail_desa').html(rowData.nama_desa || 'N/A');
            // $('#detail_status').html(getStatusBadge(laporan.status));

            // // Informasi Kegiatan
            // $('#detail_jenis_kegiatan').text(kegiatan.nama_jenis || 'N/A');
            // $('#detail_kode_kegiatan').text(kegiatan.kode_kegiatan || 'N/A');
            // $('#detail_nama_kegiatan').text(kegiatan.nama_kegiatan || 'N/A');
            // $('#detail_dasar_hukum').text(kegiatan.dasar_hukum || 'Tidak ada dasar hukum');

            // // Timeline
            // $('#detail_tanggal_mulai').text(
            //     kegiatan.tanggal_mulai ? `Tgl. ${kegiatan.tanggal_mulai}` : 'N/A'
            // );
            // $('#detail_tanggal_selesai').text(
            //     kegiatan.tanggal_selesai ? `Tgl. ${kegiatan.tanggal_selesai}` : 'N/A'
            // );
            // $('#detail_batas_upload').text(
            //     kegiatan.batas_akhir_upload ? `Tgl. ${kegiatan.batas_akhir_upload}` : 'N/A'
            // );
            // $('#detail_tanggal_target').text(
            //     laporan.tanggal_target ? `Tgl. ` + formatDateTime(laporan.tanggal_target) : 'N/A'
            // );

            // // Timeline Status
            // const timelineStatus = calculateTimelineStatus(laporan);
            // $('#detail_status_timeline').html(timelineStatus);

            // // Submit & Approval
            // $('#detail_tanggal_submit').text(
            //     laporan.tanggal_submit ? formatDateTime(laporan.tanggal_submit) : 'Belum Submit'
            // );
            // $('#detail_tanggal_approve').text(
            //     laporan.tanggal_approve ? formatDateTime(laporan.tanggal_approve) : 'Belum Approve'
            // );
            // $('#detail_catatan_approval').text(
            //     laporan.catatan_approval || 'Tidak ada catatan'
            // );

            // // Pertanyaan
            // renderPertanyaanList(pertanyaan);

            // // Tombol Laporkan
            // toggleLaporkanButton(laporan.status, laporan.id_laporan);
        }

        function getStatusBadge(status) {
            const statusConfig = {
                'draft': {
                    class: 'light badge-secondary',
                    text: 'Draft'
                },
                'submitted': {
                    class: 'light badge-primary',
                    text: 'Terkirim'
                },
                'revision': {
                    class: 'light badge-warning',
                    text: 'Perlu Revisi'
                },
                'approved': {
                    class: 'light badge-success',
                    text: 'Disetujui'
                },
                'rejected': {
                    class: 'light badge-danger',
                    text: 'Ditolak'
                },
                '': {
                    class: 'light badge-dark',
                    text: 'Belum Dilaporkan'
                },
                null: {
                    class: 'light badge-dark',
                    text: 'Belum Dilaporkan'
                },
                undefined: {
                    class: 'light badge-dark',
                    text: 'Belum Dilaporkan'
                }
            };

            const config = statusConfig[status] || {
                class: 'light badge=dark',
                text: status
            };
            return `<span class="badge ${config.class}">${config.text}</span>`;
        }

        function calculateTimelineStatus(laporan) {
            if (!laporan || !laporan.tanggal_target) {
                return '<span class="badge light badge-dark">Tidak ada timeline</span>';
            }

            const now = new Date();
            const targetDate = new Date(laporan.tanggal_target);
            const gracePeriod = new Date(targetDate);
            gracePeriod.setDate(gracePeriod.getDate() + 10);

            let status = '';
            let badgeClass = '';

            if (laporan.status === 'submitted' || laporan.status === 'approved') {
                const submitDate = laporan.tanggal_submit ? new Date(laporan.tanggal_submit) : now;
                if (submitDate > gracePeriod) {
                    status = 'Terlambat';
                    badgeClass = 'light badge-danger';
                } else {
                    status = 'Tepat Waktu';
                    badgeClass = 'light badge-success';
                }
            } else {
                if (now > gracePeriod) {
                    status = 'Terlambat';
                    badgeClass = 'light badge-danger';
                } else if (now > targetDate) {
                    status = 'Tenggang';
                    badgeClass = 'light badge-warning';
                } else {
                    status = 'Aman';
                    badgeClass = 'light badge-success';
                }
            }

            return `<span class="badge ${badgeClass}">${status}</span>`;
        }

        function renderPertanyaanList(pertanyaan) {
            if (pertanyaan.length > 0) {
                let listHTML = '<div class="list-group">';
                pertanyaan.forEach((item, index) => {
                    listHTML += `
                <div class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between align-items-center">
                        <h6 class="mb-1">${index + 1}. ${item.pertanyaan}</h6>
                        <span class="badge light badge-primary">${index + 1}</span>
                    </div>
                </div>
            `;
                });
                listHTML += '</div>';
                $('#detail_list_pertanyaan').html(listHTML);
            } else {
                $('#detail_list_pertanyaan').html(`
            <div class="text-center text-muted py-4">
                <i class="las la-question-circle fs-2 d-block mb-2"></i>
                Tidak ada pertanyaan untuk kegiatan ini.
            </div>
        `);
            }
        }

        function toggleLaporkanButton(status, laporanId) {
            const $btnLaporkan = $('.btn-laporkan');

            // Jika belum ada laporan sama sekali
            if (!status || !laporanId) {
                $btnLaporkan.show();
                $btnLaporkan.off('click').on('click', function() {
                    const url = '{{ route('administrator.monev.laporan.create', ':id') }}'.replace(
                        ':id', laporanId || 0);
                    window.location.href = url;
                });
                return;
            }

            // Tampilkan tombol hanya untuk status tertentu
            if (['', null, 'draft', 'revision'].includes(status)) {
                $btnLaporkan.show();
                $btnLaporkan.off('click').on('click', function() {
                    const url = '{{ route('administrator.monev.laporan.create', ':id') }}'.replace(
                        ':id', laporanId);
                    window.location.href = url;
                });
            } else {
                $btnLaporkan.hide();
            }
        }

        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        }

        function formatDateTime(dateTimeString) {
            if (!dateTimeString) return 'N/A';
            const date = new Date(dateTimeString);
            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Reset modal ketika ditutup
        $('#modalDetail').on('hidden.bs.modal', function() {
            // Reset semua konten
            $('.btn-laporkan').hide();
            $('#detail_list_pertanyaan').html(`
                <div class="text-center text-muted">
                    <i class="las la-question-circle fs-2 d-block mb-2"></i>
                    Memuat daftar pertanyaan...
                </div>
            `);
        });
    });
</script>
