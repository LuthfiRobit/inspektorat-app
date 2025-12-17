<script>
    $(document).ready(function() {
        // Handler untuk aksi dropdown di datatable
        $('#example').on('click', '.dropdown-item', function() {
            const action = $(this).data('action');
            const desaId = $(this).data('desa-id');
            const kegiatanId = $(this).data('kegiatan-id');
            const laporanId = $(this).data('laporan-id');

            // console.log('Dropdown clicked:', { action, desaId, kegiatanId, laporanId });

            // if (!desaId || !kegiatanId) {
            //     console.error("Missing required data:", { desaId, kegiatanId });
            //     ResponseHandler.handleError("Data desa atau kegiatan tidak ditemukan!");
            //     return;
            // }

            const $row = $(this).closest('tr');
            const rowData = table.row($row).data();

            // Build URL dengan parameter query
            const url = '{{ route("administrator.monev.laporan.show-request") }}' + 
                       '?desa_id=' + desaId + 
                       '&kegiatan_id=' + kegiatanId + 
                       '&laporan_id=' + (laporanId || '');

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
            $('#detail_tahun').text(kegiatan.tahun_anggaran || 'N/A');
            $('#detail_bulan').text(kegiatan.bulan || 'N/A');
            $('#detail_jenis_kegiatan').text(kegiatan.jenis_kegiatan || 'N/A');
            $('#detail_kode_kegiatan').text(kegiatan.kode_kegiatan || 'N/A');
            $('#detail_nama_kegiatan').text(kegiatan.nama_kegiatan || 'N/A');
            $('#detail_dasar_hukum').text(kegiatan.dasar_hukum || 'Tidak ada dasar hukum');

            // Timeline Kegiatan dengan format yang benar
            $('#detail_tanggal_mulai').text(
                formatTanggalKegiatan(kegiatan.tanggal_mulai, kegiatan.bulan, kegiatan.tahun_anggaran)
            );
            $('#detail_tanggal_selesai').text(
                formatTanggalKegiatan(kegiatan.tanggal_selesai, kegiatan.bulan, kegiatan.tahun_anggaran)
            );
            $('#detail_batas_upload').text(
                formatBatasUpload(kegiatan.batas_akhir_upload, kegiatan.tanggal_selesai, kegiatan.bulan, kegiatan.tahun_anggaran)
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
            // 4. TOMBOL LAPORKAN/EDIT - GUNAKAN PARAMETER YANG SUDAH DIVALIDASI
            // ============================
            toggleLaporkanButton(
                laporan ? laporan.status : null,
                laporan ? laporan.id_laporan : null,
                desaId, // Gunakan desaId dari parameter yang sudah divalidasi
                kegiatanId // Gunakan kegiatanId dari parameter yang sudah divalidasi
            );

            // Tambahkan info timeline status
            updateTimelineStatus(timelineStatus, daysUntilDeadline);
        }

        // Fungsi untuk format tanggal kegiatan (tanggal_mulai dan tanggal_selesai)
        function formatTanggalKegiatan(tanggal, bulan, tahun) {
            if (!tanggal || !bulan || !tahun) return 'N/A';
            
            try {
                // Konversi angka tanggal ke format "1 November 2025"
                const bulanMap = {
                    'Januari': 0, 'Februari': 1, 'Maret': 2, 'April': 3, 'Mei': 4, 'Juni': 5,
                    'Juli': 6, 'Agustus': 7, 'September': 8, 'Oktober': 9, 'November': 10, 'Desember': 11
                };
                
                const monthIndex = bulanMap[bulan];
                if (monthIndex === undefined) return 'N/A';
                
                const date = new Date(parseInt(tahun), monthIndex, parseInt(tanggal));
                if (isNaN(date.getTime())) return 'N/A';
                
                return date.toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                });
            } catch (error) {
                console.error("Error formatting kegiatan date:", error);
                return 'N/A';
            }
        }

        // Fungsi untuk format batas upload
        function formatBatasUpload(batasHari, tanggalSelesai, bulan, tahun) {
            if (!batasHari) return 'Tidak ada batas';
            if (!tanggalSelesai || !bulan || !tahun) return `${batasHari} hari setelah selesai`;
            
            try {
                // Hitung tanggal batas upload (tanggal_selesai + batas_akhir_upload)
                const bulanMap = {
                    'Januari': 0, 'Februari': 1, 'Maret': 2, 'April': 3, 'Mei': 4, 'Juni': 5,
                    'Juli': 6, 'Agustus': 7, 'September': 8, 'Oktober': 9, 'November': 10, 'Desember': 11
                };
                
                const monthIndex = bulanMap[bulan];
                if (monthIndex === undefined) return `${batasHari} hari setelah selesai`;
                
                const tanggalSelesaiDate = new Date(parseInt(tahun), monthIndex, parseInt(tanggalSelesai));
                if (isNaN(tanggalSelesaiDate.getTime())) return `${batasHari} hari setelah selesai`;
                
                const batasUploadDate = new Date(tanggalSelesaiDate);
                batasUploadDate.setDate(tanggalSelesaiDate.getDate() + parseInt(batasHari));
                
                return batasUploadDate.toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                });
            } catch (error) {
                console.error("Error formatting batas upload:", error);
                return `${batasHari} hari setelah selesai`;
            }
        }

        function updateTimelineStatus(status, days) {
            // Hapus elemen timeline status lama jika ada
            $('.timeline-status-info').remove();
            
            let statusClass = 'badge-';
            let statusText = status;
            
            switch(status) {
                case 'Selesai':
                    statusClass += 'success';
                    break;
                case 'Menunggu':
                    statusClass += 'primary';
                    break;
                case 'Tenggang':
                    statusClass += 'warning';
                    break;
                case 'Terlambat':
                    statusClass += 'danger';
                    break;
                default:
                    statusClass += 'dark';
            }
            
            // Tambahkan elemen timeline status di bagian Informasi Laporan
            const timelineHtml = `
                <div class="row timeline-status-info">
                    <dt class="col-sm-4">Status Timeline</dt>
                    <dd class="col-sm-8">
                        <span class="badge ${statusClass}">${statusText}</span>
                        ${days !== null ? ` (${Math.abs(Math.round(days))} hari)` : ''}
                    </dd>
                </div>
            `;
            
            // Sisipkan setelah status laporan
            $('#detail_status').closest('.row').after(timelineHtml);
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
                $btnLaporkan.off('click').on('click', function() {
                    console.log('Creating report with:', { desaId, kegiatanId });
                    const url = '{{ route("administrator.monev.laporan.create") }}?desa_id=' + desaId + '&kegiatan_id=' + kegiatanId;
                    window.location.href = url;
                });
                return;
            }

            // Tampilkan tombol hanya untuk status tertentu
            if (['draft', 'revision'].includes(status)) {
                $btnLaporkan.show().html('<i class="las la-edit me-1"></i> Edit Laporan');
                $btnLaporkan.off('click').on('click', function() {
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
                    class: 'badge-secondary',
                    text: 'Draft'
                },
                'submitted': {
                    class: 'badge-primary',
                    text: 'Terkirim'
                },
                'revision': {
                    class: 'badge-warning',
                    text: 'Perlu Revisi'
                },
                'approved': {
                    class: 'badge-success',
                    text: 'Disetujui'
                },
                'rejected': {
                    class: 'badge-danger',
                    text: 'Ditolak'
                },
                '': {
                    class: 'badge-dark',
                    text: 'Belum Dilaporkan'
                },
                null: {
                    class: 'badge-dark',
                    text: 'Belum Dilaporkan'
                },
                undefined: {
                    class: 'badge-dark',
                    text: 'Belum Dilaporkan'
                }
            };

            const config = statusConfig[status] || {
                class: 'badge-dark',
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
        $('#modalDetail').on('hidden.bs.modal', function() {
            // Reset tombol laporkan
            $('.btn-laporkan').hide();
            // Reset loading state
            hideLoadingState();
        });

        // Reset modal ketika dibuka
        $('#modalDetail').on('show.bs.modal', function() {
            showLoadingState();
        });
    });
</script>