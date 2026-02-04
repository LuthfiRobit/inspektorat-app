<script>
    $(document).ready(function () {
        // Handler untuk aksi dropdown di datatable
        $('#example').on('click', '.dropdown-item', function () {
            const action = $(this).data('action');
            const desaId = $(this).data('desa-id');
            const kegiatanId = $(this).data('kegiatan-id');
            const laporanId = $(this).data('laporan-id');
            const bulan = $(this).data('bulan'); // NEW
            const tahun = $(this).data('tahun'); // NEW

            const $row = $(this).closest('tr');
            const rowData = table.row($row).data();

            // Build URL dengan parameter query
            const url = '{{ route("administrator.monev.laporan.show-request") }}' +
                '?desa_id=' + desaId +
                '&kegiatan_id=' + kegiatanId +
                '&laporan_id=' + (laporanId || '') +
                '&bulan=' + (bulan || '') + // NEW
                '&tahun=' + (tahun || ''); // NEW

            const handlers = {
                'action_show': handleShow
            };

            if (handlers[action]) {
                // Tampilkan modal dan loading state
                $('#modalDetail').modal('show');
                showLoadingState();

                console.log('Sending request to:', url);

                AjaxHandler.sendGetRequest(url, response => {
                    console.log('Response received:', response);

                    if (response.status === 200 && response.data) {
                        hideLoadingState();
                        handlers[action](response.data, rowData, desaId, kegiatanId);
                    } else {
                        ResponseHandler.handleError("Data tidak ditemukan.");
                        $('#modalDetail').modal('hide');
                    }
                }, error => {
                    console.error("Error loading detail:", error);
                    ResponseHandler.handleError("Gagal memuat data detail.");
                    $('#modalDetail').modal('hide');
                });
            }
        });

        function showLoadingState() {
            $('#modalLoading').show();
            $('#modalContent').hide();
            $('.btn-laporkan').hide();
        }

        function hideLoadingState() {
            $('#modalLoading').hide();
            $('#modalContent').show();
        }

        function handleShow(data, rowData, desaId, kegiatanId) {
            console.log('Handling show data:', data);

            // Pastikan data yang diperlukan ada
            if (!data || !data.desa || !data.kegiatan) {
                console.error("Invalid response data:", data);
                ResponseHandler.handleError("Data desa atau kegiatan tidak ditemukan!");
                $('#modalDetail').modal('hide');
                return;
            }

            const desa = data.desa;
            const kegiatan = data.kegiatan;
            const context = data.context || {}; // NEW
            const laporan = data.laporan; // bisa null
            const tanggalTarget = data.tanggal_target;
            const timelineStatus = data.timeline_status;
            const daysUntilDeadline = data.days_until_deadline;

            // ============================
            // 1. INFORMASI DESA
            // ============================
            $('#detail_desa').text(desa.nama_desa || 'N/A');
            $('#detail_kecamatan').text(desa.nama_kecamatan || 'N/A');

            // ============================
            // 2. INFORMASI KEGIATAN (dengan tahun dan periode)
            // ============================
            $('#detail_tahun').text(context.tahun || kegiatan.tahun_anggaran || 'N/A');

            // Logic Display Periode
            let periodeText = kegiatan.bulan_master || 'N/A';
            if (context.bulan_nama) {
                periodeText = context.bulan_nama;
            }
            if (kegiatan.frekuensi_pelaporan) {
                // Tambahkan info frekuensi jika ada
                periodeText += ` (Rutin: Setiap ${kegiatan.frekuensi_pelaporan} Bulan)`;
            }
            $('#detail_bulan').text(periodeText);

            $('#detail_jenis_kegiatan').text(kegiatan.jenis_kegiatan || 'N/A');
            $('#detail_kode_kegiatan').text(kegiatan.kode_kegiatan || 'N/A');
            $('#detail_nama_kegiatan').text(kegiatan.nama_kegiatan || 'N/A');
            $('#detail_dasar_hukum').text(kegiatan.dasar_hukum || 'Tidak ada dasar hukum');

            // Timeline Kegiatan Logic (Rutin vs Insidentil)
            const tahun = kegiatan.tahun_anggaran;
            let startBulan, endBulan;

            if (kegiatan.frekuensi_pelaporan) {
                // RUTIN LOGIC
                // Tanggal Mulai: Tgl. X (col) + Bulan Mulai (col) + Tahun
                // Tanggal Selesai: Tgl. Y (col) + Bulan Selesai (col) + Tahun
                startBulan = kegiatan.bulan_mulai;
                endBulan = kegiatan.bulan_selesai;
            } else {
                // INSIDENTIL LOGIC
                // Tanggal Mulai: Tgl. X (col) + Bulan (col) + Tahun
                // Tanggal Selesai: Tgl. Y (col) + Bulan (col) + Tahun
                startBulan = kegiatan.bulan;
                endBulan = kegiatan.bulan;
            }

            // Render Tanggal Mulai
            $('#detail_tanggal_mulai').text(
                formatDateSpecific(kegiatan.tanggal_mulai, startBulan, tahun)
            );

            // Render Tanggal Selesai
            $('#detail_tanggal_selesai').text(
                formatDateSpecific(kegiatan.tanggal_selesai, endBulan, tahun)
            );

            // Render Batas Upload
            // Logic: + X hari dari tanggal selesai (which uses endBulan)
            $('#detail_batas_upload').text(
                formatBatasUploadSpecific(kegiatan.batas_akhir_upload, kegiatan.tanggal_selesai, endBulan, tahun)
            );

            // ============================
            // 3. INFORMASI LAPORAN
            // ============================
            $('#detail_status').html(getStatusBadge(laporan ? laporan.status : null));
            $('#detail_tanggal_target').text(formatDateLong(tanggalTarget) || 'N/A');

            // Tanggal Submit & Approve dengan format baru
            $('#detail_tanggal_submit').text(
                laporan && laporan.tanggal_submit ? formatDateLong(laporan.tanggal_submit) : 'Belum Submit'
            );
            $('#detail_tanggal_approve').text(
                laporan && laporan.tanggal_approve ? formatDateLong(laporan.tanggal_approve) : 'Belum Approve'
            );
            $('#detail_catatan_approval').text(
                laporan && laporan.catatan_approval ? laporan.catatan_approval : 'Tidak ada catatan'
            );

            // ============================
            // 4. STATUS TIMELINE - TERINTEGRASI DALAM CONTAINER
            // ============================
            updateTimelineStatus(timelineStatus, daysUntilDeadline);

            // ============================
            // 5. TOMBOL LAPORKAN/EDIT - GUNAKAN PARAMETER YANG SUDAH DIVALIDASI
            // ============================
            toggleLaporkanButton(
                laporan ? laporan.status : null,
                laporan ? laporan.id_laporan : null,
                desaId, // Gunakan desaId dari parameter yang sudah divalidasi
                kegiatanId // Gunakan kegiatanId dari parameter yang sudah divalidasi
            );
        }

        // Helper: Format Date Specific (Tgl + Bulan(Int) + Tahun)
        function formatDateSpecific(tanggal, bulanInt, tahun) {
            if (!tanggal || !bulanInt || !tahun) return 'N/A';

            try {
                const monthIndex = parseInt(bulanInt) - 1; // 0-indexed
                const date = new Date(parseInt(tahun), monthIndex, parseInt(tanggal));

                if (isNaN(date.getTime())) return 'N/A';

                return "Tgl. " + date.getDate() + " " + date.toLocaleDateString('id-ID', { month: 'long' }) + " " + date.getFullYear();
            } catch (error) {
                console.error("Error formatting date specific:", error);
                return 'N/A';
            }
        }

        // Helper: Format Batas Upload Specific
        function formatBatasUploadSpecific(batasHari, tanggalSelesai, bulanInt, tahun) {
            if (!batasHari) return 'Tidak ada batas';
            if (!tanggalSelesai || !bulanInt || !tahun) return `${batasHari} hari setelah selesai`;

            try {
                const monthIndex = parseInt(bulanInt) - 1;
                const selesaiDate = new Date(parseInt(tahun), monthIndex, parseInt(tanggalSelesai));

                if (isNaN(selesaiDate.getTime())) return `${batasHari} hari setelah selesai`;

                // Add days
                const deadlineDate = new Date(selesaiDate);
                deadlineDate.setDate(selesaiDate.getDate() + parseInt(batasHari));

                return "Tgl. " + deadlineDate.getDate() + " " + deadlineDate.toLocaleDateString('id-ID', { month: 'long' }) + " " + deadlineDate.getFullYear();
            } catch (error) {
                console.error("Error formatting batas upload specific:", error);
                return `${batasHari} hari setelah selesai`;
            }
        }

        // REFACTORED: Update Timeline Status - Terintegrasi dalam container yang sudah disediakan
        function updateTimelineStatus(status, days) {
            const $container = $('#timeline_status_container');
            const $statusElement = $('#detail_timeline_status');
            const $daysElement = $('#detail_timeline_days');

            // Jika tidak ada status timeline, sembunyikan container
            if (!status) {
                $container.hide();
                return;
            }

            // Tampilkan container
            $container.show();

            // Konfigurasi badge berdasarkan status
            let badgeClass = '';
            let statusText = status;

            switch (status) {
                case 'Selesai':
                    badgeClass = 'bg-success';
                    break;
                case 'Menunggu':
                    badgeClass = 'bg-primary';
                    break;
                case 'Tenggang':
                    badgeClass = 'bg-warning';
                    break;
                case 'Terlambat':
                    badgeClass = 'bg-danger';
                    break;
                default:
                    badgeClass = 'bg-secondary';
            }

            // Update status badge
            $statusElement.html(`<span class="badge ${badgeClass}">${statusText}</span>`);

            // Update informasi hari jika ada
            if (days !== null && days !== undefined) {
                const dayCount = Math.abs(Math.round(days));
                const dayText = days < 0 ? `${dayCount} hari yang lalu` : `${dayCount} hari lagi`;
                $daysElement.text(`(${dayText})`);
            } else {
                $daysElement.text('');
            }
        }

        function toggleLaporkanButton(status, laporanId, desaId, kegiatanId) {
            const $btnLaporkan = $('.btn-laporkan');

            console.log('Toggle button with:', { status, laporanId, desaId, kegiatanId });

            // Pastikan desaId dan kegiatanId valid
            if (!desaId || !kegiatanId) {
                console.error("Invalid data for button:", { desaId, kegiatanId, laporanId, status });
                $btnLaporkan.hide();
                return;
            }

            // Jika belum ada laporan sama sekali
            if (!status || !laporanId) {
                $btnLaporkan.show().html('<i class="las la-plus me-1"></i> Buat Laporan');
                $btnLaporkan.off('click').on('click', function () {
                    console.log('Creating report with:', { desaId, kegiatanId });
                    const url = '{{ route("administrator.monev.laporan.create") }}?desa_id=' + desaId + '&kegiatan_id=' + kegiatanId;
                    window.location.href = url;
                });
                return;
            }

            // Tampilkan tombol hanya untuk status tertentu
            if (['draft', 'revision'].includes(status)) {
                $btnLaporkan.show().html('<i class="las la-edit me-1"></i> Edit Laporan');
                $btnLaporkan.off('click').on('click', function () {
                    console.log('Editing report with:', { desaId, kegiatanId, laporanId });
                    const url = '{{ route("administrator.monev.laporan.edit") }}?desa_id=' + desaId + '&kegiatan_id=' + kegiatanId + '&id_laporan=' + laporanId;
                    window.location.href = url;
                });
            } else {
                $btnLaporkan.hide();
            }
        }

        function getStatusBadge(status) {
            const statusConfig = {
                'draft': {
                    class: 'bg-secondary',
                    text: 'Draft'
                },
                'submitted': {
                    class: 'bg-primary',
                    text: 'Terkirim'
                },
                'revision': {
                    class: 'bg-warning',
                    text: 'Perlu Revisi'
                },
                'approved': {
                    class: 'bg-success',
                    text: 'Disetujui'
                },
                'rejected': {
                    class: 'bg-danger',
                    text: 'Ditolak'
                },
                '': {
                    class: 'bg-dark',
                    text: 'Belum Dilaporkan'
                },
                null: {
                    class: 'bg-dark',
                    text: 'Belum Dilaporkan'
                },
                undefined: {
                    class: 'bg-dark',
                    text: 'Belum Dilaporkan'
                }
            };

            const config = statusConfig[status] || {
                class: 'bg-dark',
                text: status || 'Belum Dilaporkan'
            };
            return `<span class="badge ${config.class}">${config.text}</span>`;
        }

        // Format tanggal baru: "14 agustus 2025"
        function formatDateLong(dateString) {
            if (!dateString) return 'N/A';

            try {
                const date = new Date(dateString);
                if (isNaN(date.getTime())) return 'N/A';

                const options = {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                };
                return date.toLocaleDateString('id-ID', options);
            } catch (error) {
                console.error("Error formatting date:", error, dateString);
                return 'N/A';
            }
        }

        // Reset modal ketika ditutup
        $('#modalDetail').on('hidden.bs.modal', function () {
            // Reset tombol laporkan
            $('.btn-laporkan').hide();
            // Reset timeline status container
            $('#timeline_status_container').hide();
            $('#detail_timeline_status').html('-');
            $('#detail_timeline_days').text('');
            // Reset loading state
            hideLoadingState();
        });

        // Reset modal ketika dibuka
        $('#modalDetail').on('show.bs.modal', function () {
            showLoadingState();
        });
    });
</script>