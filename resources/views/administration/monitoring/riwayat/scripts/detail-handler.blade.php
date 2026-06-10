<script>
    /**
     * LAPORAN DETAIL HANDLER - REFACTORED VERSION
     * Script untuk menangani tampilan detail riwayat laporan kegiatan
     * Optimized for compact and professional layout
     */

    // Configuration constants
    const CONFIG = {
        ROUTES: {
            GET_LAPORAN_DATA: '{{ route('administrator.monitoring.riwayat.get-data', ':id') }}'
        },
        STATUS_DISPLAY: {
            'draft': 'Draft',
            'submitted': 'Terkirim - Butuh Review',
            'approved': 'Disetujui',
            'rejected': 'Ditolak',
            'revision': 'Perlu Revisi'
        },
        BULAN_NAMES: {
            1: 'Januari',
            2: 'Februari',
            3: 'Maret',
            4: 'April',
            5: 'Mei',
            6: 'Juni',
            7: 'Juli',
            8: 'Agustus',
            9: 'September',
            10: 'Oktober',
            11: 'November',
            12: 'Desember'
        },
        STATUS_BADGE_CLASSES: {
            'approved': 'bg-success',
            'revision': 'bg-warning',
            'submitted': 'bg-info',
            'draft': 'bg-secondary',
            'rejected': 'bg-danger'
        },
        TIMELINE_STATUS_CLASSES: {
            'Tepat Waktu': 'bg-primary',
            'Terlambat': 'bg-danger',
            'default': 'bg-warning'
        },
        COMPLETENESS_STATUS: {
            100: {
                class: 'success',
                text: 'Lengkap'
            },
            75: {
                class: 'primary',
                text: 'Hampir Lengkap'
            },
            50: {
                class: 'warning',
                text: 'Cukup'
            },
            default: {
                class: 'danger',
                text: 'Kurang'
            }
        }
    };

    /**
     * Application State Management
     */
    class AppStateManager {
        constructor() {
            this.currentLaporanId = null;
            this.laporanData = null;
        }

        setLaporanId(id) {
            this.currentLaporanId = id;
        }

        setLaporanData(data) {
            this.laporanData = data;
        }

        getLaporanData() {
            return this.laporanData;
        }

        hasData() {
            return this.laporanData !== null;
        }
    }

    /**
     * DOM Elements Manager
     */
    class DOMManager {
        constructor() {
            this.elements = {};
            this.initialize();
        }

        initialize() {
            this.elements = {
                // Informasi Laporan
                infoStatusBadge: $('#info_status_badge'),
                infoTimelineStatus: $('#info_timeline_status'),
                infoTanggalTarget: $('#info_tanggal_target'),
                infoTanggalSubmit: $('#info_tanggal_submit'),
                infoTanggalApprove: $('#info_tanggal_approve'),
                infoApprovedBy: $('#info_approved_by'),
                infoCatatanApproval: $('#info_catatan_approval'),
                infoCreatedBy: $('#info_created_by'),
                infoCreatedAt: $('#info_created_at'),

                // Informasi Kegiatan
                detailDesa: $('#detail_desa'),
                detailKecamatan: $('#detail_kecamatan'),
                detailNamaKegiatan: $('#detail_nama_kegiatan'),
                detailTahun: $('#detail_tahun'),
                detailKodeKegiatan: $('#detail_kode_kegiatan'),
                detailJenisKegiatan: $('#detail_jenis_kegiatan'),
                detailBulan: $('#detail_bulan'),
                detailDasarHukum: $('#detail_dasar_hukum'),
                detailTanggalMulai: $('#detail_tanggal_mulai'),
                detailTanggalSelesai: $('#detail_tanggal_selesai'),
                detailBatasUpload: $('#detail_batas_upload'),

                // Containers
                timelineContainer: $('#timelineContainer'),
                questionsContainer: $('#questionsContainer'),
                completenessBadge: $('#completeness_badge'),
                completenessProgress: $('#completeness_progress'),

                // Action Buttons
                downloadLaporanBtn: $('#downloadLaporanBtn'),
                printLaporanBtn: $('#printLaporanBtn')
            };
        }

        getElement(key) {
            return this.elements[key];
        }

        showLoading(container, message = 'Memuat data...') {
            container.html(`
            <div class="text-center py-3">
                <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                <span class="text-muted small">${message}</span>
            </div>
        `);
        }

        showEmptyState(container, message = 'Tidak ada data') {
            container.html(`<p class="text-muted text-center small">${message}</p>`);
        }
    }

    /**
     * Utility Functions
     */
    class Utils {
        static formatDateTime(dateTimeString) {
            if (!dateTimeString) return '-';
            const date = new Date(dateTimeString);
            const day = date.getDate();
            const month = date.toLocaleDateString('id-ID', { month: 'long' });
            const year = date.getFullYear();
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');

            return `Tgl. ${day} ${month} ${year} - Jm. ${hours}:${minutes}`;
        }

        static formatDateOnly(dateTimeString) {
            if (!dateTimeString) return '-';
            const date = new Date(dateTimeString);
            const day = date.getDate();
            const month = date.toLocaleDateString('id-ID', { month: 'long' });
            const year = date.getFullYear();

            return `Tgl. ${day} ${month} ${year}`;
        }

        static getStatusDisplay(status) {
            return CONFIG.STATUS_DISPLAY[status] || status;
        }

        static getBulanName(bulanNumber) {
            return CONFIG.BULAN_NAMES[bulanNumber] || bulanNumber;
        }

        static getStatusBadgeClass(status) {
            return CONFIG.STATUS_BADGE_CLASSES[status] || 'bg-secondary';
        }

        static getTimelineStatusClass(status) {
            return CONFIG.TIMELINE_STATUS_CLASSES[status] || CONFIG.TIMELINE_STATUS_CLASSES.default;
        }

        static getCompletenessStatus(percentage) {
            if (percentage === 100) return CONFIG.COMPLETENESS_STATUS[100];
            if (percentage >= 75) return CONFIG.COMPLETENESS_STATUS[75];
            if (percentage >= 50) return CONFIG.COMPLETENESS_STATUS[50];
            return CONFIG.COMPLETENESS_STATUS.default;
        }

        static showErrorAlert(message) {
            const alertHtml = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="las la-exclamation-circle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`;
            $('.container-fluid').prepend(alertHtml);
        }

        static showSuccessAlert(message) {
            const alertHtml = `
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="las la-check-circle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`;
            $('.container-fluid').prepend(alertHtml);
        }

        static formatDateSpecific(tanggal, bulanInt, tahun) {
            if (!tanggal || !bulanInt || !tahun) return 'N/A';

            try {
                const monthIndex = parseInt(bulanInt) - 1;
                const date = new Date(parseInt(tahun), monthIndex, parseInt(tanggal));

                if (isNaN(date.getTime())) return 'N/A';

                return "Tgl. " + date.getDate() + " " + date.toLocaleDateString('id-ID', { month: 'long' }) + " " + date.getFullYear();
            } catch (error) {
                console.error("Error formatting date specific:", error);
                return 'N/A';
            }
        }

        static formatBatasUploadSpecific(batasHari, tanggalSelesai, bulanInt, tahun) {
            if (!batasHari) return 'Tidak ada batas';
            if (!tanggalSelesai || !bulanInt || !tahun) return `${batasHari} hari setelah selesai`;

            try {
                const monthIndex = parseInt(bulanInt) - 1;
                const selesaiDate = new Date(parseInt(tahun), monthIndex, parseInt(tanggalSelesai));

                if (isNaN(selesaiDate.getTime())) return `${batasHari} hari setelah selesai`;

                const deadlineDate = new Date(selesaiDate);
                deadlineDate.setDate(selesaiDate.getDate() + parseInt(batasHari));

                return "Tgl. " + deadlineDate.getDate() + " " + deadlineDate.toLocaleDateString('id-ID', { month: 'long' }) + " " + deadlineDate.getFullYear();
            } catch (error) {
                console.error("Error formatting batas upload specific:", error);
                return `${batasHari} hari setelah selesai`;
            }
        }
    }

    /**
     * Data Loader and Processor
     */
    class DataHandler {
        constructor(domManager, appState) {
            this.dom = domManager;
            this.appState = appState;
        }

        async loadLaporanData(laporanId) {
            // console.log("🔄 Loading laporan data for detail:", laporanId);

            const url = CONFIG.ROUTES.GET_LAPORAN_DATA.replace(':id', laporanId);

            try {
                const response = await $.ajax({
                    url: url,
                    method: 'GET',
                    dataType: 'json'
                });

                if (response?.status === 200 && response.data) {
                    // console.log("✅ Laporan data loaded for detail:", response.data);
                    this.appState.setLaporanData(response.data);
                    this.processLaporanData(response.data);
                    Utils.showSuccessAlert('Data laporan berhasil dimuat');
                } else {
                    throw new Error(response?.message || 'Response tidak valid');
                }
            } catch (error) {
                Utils.showErrorAlert('Gagal memuat data laporan: ' + error.message);
                console.error('Error loading laporan data:', error);
            }
        }

        processLaporanData(data) {
            this.updateInformasiLaporan(data);
            this.updateInformasiKegiatan(data);
            this.updateHistoryLaporan(data.history_laporan);
            this.updateQuestionsAndDocuments(data);
            this.updateProgressKelengkapan(data.completeness);
        }

        updateInformasiLaporan(data) {
            const elements = this.dom.elements;

            elements.infoStatusBadge
                .text(data.status_display)
                .removeClass('bg-success bg-warning bg-info bg-secondary bg-danger')
                .addClass(Utils.getStatusBadgeClass(data.status));

            elements.infoTimelineStatus
                .text(data.timeline_status)
                .removeClass('bg-primary bg-warning bg-danger')
                .addClass(Utils.getTimelineStatusClass(data.timeline_status));

            elements.infoTanggalTarget.text(Utils.formatDateOnly(data.tanggal_target));
            elements.infoTanggalSubmit.text(Utils.formatDateTime(data.tanggal_submit));
            elements.infoTanggalApprove.text(Utils.formatDateTime(data.tanggal_approve));

            const approvedBy = data.approved_by_petugas || data.approved_by_name || '-';
            elements.infoApprovedBy.text(approvedBy);

            elements.infoCatatanApproval.text(data.catatan_approval || 'Tidak ada catatan');

            const createdBy = data.created_by_petugas || data.created_by_name || '-';
            elements.infoCreatedBy.text(createdBy);

            elements.infoCreatedAt.text(Utils.formatDateTime(data.created_at));
        }

        updateInformasiKegiatan(data) {
            const elements = this.dom.elements;

            elements.detailDesa.text(data.nama_desa || '-');
            elements.detailKecamatan.text(data.nama_kecamatan || '-');

            elements.detailNamaKegiatan.text(data.nama_kegiatan || '-');
            elements.detailTahun.text(data.tahun_anggaran || '-');
            elements.detailKodeKegiatan.text(data.kode_kegiatan || '-');
            elements.detailJenisKegiatan.text(data.nama_jenis || '-');
            elements.detailDasarHukum.text(data.dasar_hukum || 'Tidak ada dasar hukum');

            let periodeText = data.bulan_kegiatan || 'N/A';

            if (data.bulan) {
                periodeText = Utils.getBulanName(data.bulan);
            }

            if (data.frekuensi_pelaporan) {
                periodeText += ` (Rutin: Setiap ${data.frekuensi_pelaporan} Bulan)`;
            }
            elements.detailBulan.text(periodeText);

            const tahun = data.tahun_anggaran;
            let startBulan, endBulan;

            if (data.frekuensi_pelaporan) {
                startBulan = data.bulan_mulai;
                endBulan = data.bulan_selesai;
            } else {
                startBulan = data.bulan;
                endBulan = data.bulan;
            }

            elements.detailTanggalMulai.text(
                Utils.formatDateSpecific(data.tanggal_mulai, startBulan, tahun)
            );
            elements.detailTanggalSelesai.text(
                Utils.formatDateSpecific(data.tanggal_selesai, endBulan, tahun)
            );
            elements.detailBatasUpload.text(
                Utils.formatBatasUploadSpecific(data.batas_akhir_upload, data.tanggal_selesai, endBulan, tahun)
            );
        }

        updateHistoryLaporan(history) {
            const container = this.dom.elements.timelineContainer;

            if (!history || history.length === 0) {
                this.dom.showEmptyState(container, 'Tidak ada history laporan');
                return;
            }

            const timelineHtml = history.map((item, index) =>
                this.createTimelineItem(item, index === history.length - 1)
            ).join('');

            container.html(timelineHtml);
        }

        createTimelineItem(item, isLast = false) {
            const borderClass = isLast ? 'border-left: 2px solid transparent;' : '';
            const formattedTime = Utils.formatDateTime(item.timestamp);

            return `
            <div class="timeline-item">
                <div class="timeline-marker bg-${item.color}"></div>
                <div class="timeline-content" style="${borderClass}">
                    <h6 class="mb-1 small fw-bold">${item.event}</h6>
                    <small class="text-muted" style="font-size: 0.7rem;">${formattedTime}</small>
                    <p class="mb-0 small">${item.user}</p>
                    ${item.catatan ? `<small class="text-muted" style="font-size: 0.75rem;">${item.catatan}</small>` : ''}
                </div>
            </div>
        `;
        }

        /**
         * REFACTORED: Update Questions and Documents section - COMPACT VERSION
         */
        updateQuestionsAndDocuments(data) {
            const container = this.dom.elements.questionsContainer;

            if (!data.pertanyaan || data.pertanyaan.length === 0) {
                container.html(`
                <div class="alert alert-warning mb-0 py-2">
                    <h6 class="mb-1 small"><i class="las la-exclamation-triangle me-2"></i>Tidak Ada Pertanyaan</h6>
                    <p class="mb-0 small">Belum ada pertanyaan yang ditetapkan untuk kegiatan ini.</p>
                </div>
            `);
                return;
            }

            const questionsHtml = data.pertanyaan.map((question, index) =>
                this.renderQuestion(question, data.jawaban, data.dokumen, index)
            ).join('');

            container.html(questionsHtml);
            this.initializeRevisionHistory();

            // Re-parse dynamic dFlip elements
            if (window.DEARFLIP && typeof window.DEARFLIP.parseBooks === 'function') {
                window.DEARFLIP.parseBooks();
            }
        }

        /**
         * REFACTORED: Render Question - COMPACT VERSION
         */
        renderQuestion(question, jawaban, dokumen, index) {
            const jawabanPertanyaan = jawaban.find(j => j.pertanyaan_id === question.id_pertanyaan);
            const statusJawaban = jawabanPertanyaan ? jawabanPertanyaan.jawaban_text : 'belum';
            const statusColor = statusJawaban === 'sudah' ? 'success' : 'danger';
            const statusIcon = statusJawaban === 'sudah' ? 'check-circle' : 'times-circle';
            const statusText = statusJawaban === 'sudah' ? 'SUDAH' : 'BELUM';

            return `
            <div class="question-section mb-3" data-question-id="${question.id_pertanyaan}">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center mb-1">
                            <span class="badge bg-primary me-2" style="font-size: 0.7rem;">${index + 1}</span>
                            <h6 class="mb-0 fw-bold" style="font-size: 0.9rem;">
                                <i class="las la-${statusIcon} me-1 text-${statusColor}"></i>
                                ${question.pertanyaan}
                            </h6>
                        </div>
                        <span class="badge bg-${statusColor}" style="font-size: 0.65rem;">${statusText}</span>
                    </div>
                </div>
                <div class="requirements-section">
                    ${this.renderRequirementsForDetail(question.persyaratan, jawabanPertanyaan, dokumen)}
                </div>
            </div>
        `;
        }

        renderRequirementsForDetail(requirements, jawaban, dokumen) {
            if (!requirements?.length) {
                return `<div class="alert alert-info mb-0 py-2"><small>Tidak ada persyaratan dokumen untuk pertanyaan ini.</small></div>`;
            }

            return requirements.map(req => this.renderRequirementDetail(req, jawaban, dokumen)).join('');
        }

        /**
         * REFACTORED: Render Requirement Detail - COMPACT VERSION
         */
        renderRequirementDetail(req, jawaban, dokumen) {
            const dokumenList = dokumen.filter(d =>
                d.persyaratan_id === req.id_persyaratan &&
                d.jawaban_id === jawaban?.id_jawaban
            );

            const currentDokumen = dokumenList.find(d => d.is_current) || dokumenList[0];
            const hasPreviousRevisions = dokumenList.length > 1;

            const statusBadge = currentDokumen ?
                `<span class="badge ${Utils.getStatusBadgeClass(currentDokumen.status)} ms-2" style="font-size: 0.65rem;">${Utils.getStatusDisplay(currentDokumen.status)}</span>` :
                '';

            return `
            <div class="requirement-item mb-2" data-requirement-id="${req.id_persyaratan}">
                <div class="row g-2">
                    <!-- Left Column: Requirement Info -->
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label class="info-label mb-0">Persyaratan</label>
                            <div class="info-value d-flex align-items-center flex-wrap gap-1">
                                ${req.nama_persyaratan}
                                <span class="badge ${req.tipe === 'wajib' ? 'bg-danger' : 'bg-secondary'}" style="font-size: 0.65rem;">
                                    ${req.tipe === 'wajib' ? 'WAJIB' : 'TAMBAHAN'}
                                </span>
                                ${statusBadge}
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="info-label mb-0">Deskripsi</label>
                            <div class="info-value">${req.deskripsi || '-'}</div>
                        </div>
                        ${req.template_persyaratan ? `
                        <div>
                            <label class="info-label mb-0">Template</label>
                            <div class="info-value">
                                <a href="/uploads/${req.template_persyaratan}" target="_blank" class="text-decoration-none small">
                                    <i class="las la-download me-1"></i>Download Template
                                </a>
                            </div>
                        </div>
                        ` : ''}
                    </div>

                    <!-- Right Column: Document Info -->
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label class="info-label mb-0">File Dokumen</label>
                            <div class="info-value">
                                ${currentDokumen ?
                    `<div class="d-flex align-items-center flex-wrap gap-1">
                                        <i class="las la-file-pdf text-danger"></i>
                                        <a href="javascript:void(0)" class="_df_custom text-decoration-none small fw-semibold" source="/uploads/${currentDokumen.path_file}">
                                            ${currentDokumen.nama_file}
                                        </a>
                                        <span class="badge bg-secondary" style="font-size: 0.65rem;">V.${currentDokumen.version}</span>
                                    </div>` :
                    '<span class="text-danger small"><i class="las la-times-circle me-1"></i>File tidak ditemukan</span>'
                }
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="info-label mb-0">Tanggal Upload</label>
                            <div class="info-value small">
                                ${currentDokumen ? Utils.formatDateTime(currentDokumen.created_at) : '-'}
                            </div>
                        </div>
                        <div>
                            <label class="info-label mb-0">Catatan Revisi</label>
                            <div class="small text-muted" style="font-size: 0.8rem;">
                                ${currentDokumen?.catatan_revisi || 'Tidak ada catatan revisi'}
                            </div>
                        </div>
                    </div>
                </div>
                ${hasPreviousRevisions ? this.renderRevisionHistory(req.id_persyaratan, dokumenList) : ''}
            </div>
        `;
        }

        /**
         * REFACTORED: Render Revision History - COMPACT VERSION
         */
        renderRevisionHistory(requirementId, dokumenList) {
            const revisions = dokumenList.filter(d => !d.is_current).sort((a, b) => b.version - a.version);
            if (!revisions.length) return '';

            const historyHtml = revisions.map(rev => `
            <div class="revision-item">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <strong class="d-block small">v${rev.version} - ${Utils.getStatusDisplay(rev.status)}</strong>
                        <a href="javascript:void(0)" class="_df_custom text-decoration-none" style="font-size: 0.75rem;" source="/uploads/${rev.path_file}">
                            <i class="las la-book-open me-1"></i>${rev.nama_file}
                        </a>
                        ${rev.catatan_revisi ? `
                            <div class="mt-1">
                                <small class="text-muted" style="font-size: 0.7rem;">Catatan:</small>
                                <div class="text-warning" style="font-size: 0.75rem;">${rev.catatan_revisi}</div>
                            </div>
                        ` : ''}
                        <div class="mt-1">
                            <small class="text-muted" style="font-size: 0.7rem;">Upload: ${Utils.formatDateTime(rev.created_at)}</small>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');

            return `
            <div class="mt-2">
                <button class="btn btn-sm btn-outline-secondary py-1 px-2" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#revisionHistory-${requirementId}"
                    aria-expanded="false"
                    style="font-size: 0.75rem;">
                    <i class="las la-history me-1"></i>Riwayat Revisi (${revisions.length})
                </button>
                <div class="collapse mt-2" id="revisionHistory-${requirementId}">
                    <div class="revision-history-container">${historyHtml}</div>
                </div>
            </div>
        `;
        }

        initializeRevisionHistory() {
            $('.collapse').on('show.bs.collapse', function () {
                $(this).prev().find('i').removeClass('la-history').addClass('la-chevron-up');
            }).on('hide.bs.collapse', function () {
                $(this).prev().find('i').removeClass('la-chevron-up').addClass('la-history');
            });
        }

        updateProgressKelengkapan(completeness) {
            const percentage = completeness || 0;
            const status = Utils.getCompletenessStatus(percentage);
            const elements = this.dom.elements;

            elements.completenessBadge
                .text(`${percentage}% ${status.text}`)
                .removeClass('bg-success bg-primary bg-warning bg-danger')
                .addClass(`bg-${status.class}`);

            elements.completenessProgress
                .css('width', `${percentage}%`)
                .removeClass('bg-success bg-primary bg-warning bg-danger')
                .addClass(`bg-${status.class}`);
        }
    }

    /**
     * Event Handlers Manager
     */
    class EventHandler {
        constructor(domManager, appState, dataHandler) {
            this.dom = domManager;
            this.appState = appState;
            this.dataHandler = dataHandler;
        }

        initialize() {
            this.bindEvents();
        }

        bindEvents() {
            // Download Laporan
            this.dom.elements.downloadLaporanBtn?.on('click', () => this.handleDownloadLaporan());

            // Print Laporan
            this.dom.elements.printLaporanBtn?.on('click', () => this.handlePrintLaporan());
        }

        handleDownloadLaporan() {
            if (!this.appState.hasData()) {
                this.showWarningAlert('Data Belum Siap', 'Data laporan belum siap untuk didownload.');
                return;
            }

            this.showInfoAlert('Download Laporan', 'Fitur download laporan akan segera tersedia.');
        }

        handlePrintLaporan() {
            window.print();
        }

        showWarningAlert(title, text) {
            Swal.fire({
                icon: 'warning',
                title: title,
                text: text,
                confirmButtonText: 'Mengerti'
            });
        }

        showInfoAlert(title, text) {
            Swal.fire({
                icon: 'info',
                title: title,
                text: text,
                confirmButtonText: 'Mengerti'
            });
        }
    }

    /**
     * Main Application Controller
     */
    class LaporanDetailApp {
        constructor() {
            this.appState = new AppStateManager();
            this.domManager = new DOMManager();
            this.dataHandler = new DataHandler(this.domManager, this.appState);
            this.eventHandler = new EventHandler(this.domManager, this.appState, this.dataHandler);
        }

        initialize() {
            // console.log('🚀 Initializing Laporan Detail App (Refactored)...');

            this.eventHandler.initialize();
            this.loadLaporanData();
        }

        loadLaporanData() {
            const laporanId = this.getLaporanIdFromURL();

            if (laporanId) {
                this.appState.setLaporanId(laporanId);
                this.dataHandler.loadLaporanData(laporanId);
            } else {
                Utils.showErrorAlert('ID Laporan tidak ditemukan. Silakan pilih laporan dari halaman daftar.');
            }
        }

        getLaporanIdFromURL() {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get('id_laporan');
        }
    }

    // Initialize application when DOM is ready
    $(document).ready(function () {
        if (window.DFLIP) {
            window.DFLIP.defaults.onReady = function (app) {
                if (app.numPages === 1) {
                    app.setViewMode(window.DFLIP.PAGE_MODE.SINGLE);
                }
            };
        }

        const app = new LaporanDetailApp();
        app.initialize();
    });
</script>