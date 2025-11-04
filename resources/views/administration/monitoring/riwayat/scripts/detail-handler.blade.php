<script>
    /**
     * LAPORAN DETAIL HANDLER
     * Script untuk menangani tampilan detail riwayat laporan kegiatan
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
                infoKecamatan: $('#info_kecamatan'),
                infoDesa: $('#info_desa'),
                infoKegiatan: $('#info_kegiatan'),
                infoPeriode: $('#info_periode'),
                infoJenisKegiatan: $('#info_jenis_kegiatan'),
                infoBatasUpload: $('#info_batas_upload'),
                infoKodeKegiatan: $('#info_kode_kegiatan'),
                infoDasarHukum: $('#info_dasar_hukum'),
                infoTahunAnggaran: $('#info_tahun_anggaran'),
                infoRentangWaktu: $('#info_rentang_waktu'),

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
                <span class="text-muted">${message}</span>
            </div>
        `);
        }

        showEmptyState(container, message = 'Tidak ada data') {
            container.html(`<p class="text-muted text-center">${message}</p>`);
        }
    }

    /**
     * Utility Functions
     */
    class Utils {
        static formatDateTime(dateTimeString) {
            if (!dateTimeString) return '-';
            const date = new Date(dateTimeString);
            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        static formatDateOnly(dateTimeString) {
            if (!dateTimeString) return '-';
            const date = new Date(dateTimeString);
            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
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

        static formatRentangWaktu(tanggalMulai, tanggalSelesai) {
            if (!tanggalMulai || !tanggalSelesai) return '-';
            return `${tanggalMulai} - ${tanggalSelesai}`;
        }

        static calculateBatasUpload(tanggalSelesai, batasHari, tahun, bulan) {
            if (!batasHari) return '-';

            let tanggalSelesaiDate = null;

            if (tanggalSelesai) {
                tanggalSelesaiDate = new Date(tahun, bulan - 1, tanggalSelesai);
            } else if (tahun && bulan) {
                // Fallback: akhir bulan
                tanggalSelesaiDate = new Date(tahun, bulan, 0);
            }

            if (tanggalSelesaiDate) {
                const tanggalBatasAkhir = new Date(tanggalSelesaiDate);
                tanggalBatasAkhir.setDate(tanggalBatasAkhir.getDate() + batasHari);

                return `${this.formatDateOnly(tanggalBatasAkhir)} (${batasHari} Hari setelah ${this.formatDateOnly(tanggalSelesaiDate)})`;
            }

            return `${batasHari} Hari (tanggal selesai tidak tersedia)`;
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

        /**
         * Load laporan data from server
         */
        async loadLaporanData(laporanId) {
            console.log("🔄 Loading laporan data for detail:", laporanId);

            const url = CONFIG.ROUTES.GET_LAPORAN_DATA.replace(':id', laporanId);

            try {
                const response = await $.ajax({
                    url: url,
                    method: 'GET',
                    dataType: 'json'
                });

                if (response?.status === 200 && response.data) {
                    console.log("✅ Laporan data loaded for detail:", response.data);
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

        /**
         * Process and update all sections with laporan data
         */
        processLaporanData(data) {
            this.updateInformasiLaporan(data);
            this.updateInformasiKegiatan(data);
            this.updateHistoryLaporan(data.history_laporan);
            this.updateQuestionsAndDocuments(data);
            this.updateProgressKelengkapan(data.completeness);
        }

        /**
         * Update Informasi Laporan section
         */
        updateInformasiLaporan(data) {
            const elements = this.dom.elements;

            // Status Information
            elements.infoStatusBadge
                .text(data.status_display)
                .removeClass('bg-success bg-warning bg-info bg-secondary bg-danger')
                .addClass(Utils.getStatusBadgeClass(data.status));

            elements.infoTimelineStatus
                .text(data.timeline_status)
                .removeClass('bg-primary bg-warning bg-danger')
                .addClass(Utils.getTimelineStatusClass(data.timeline_status));

            // Date Information
            elements.infoTanggalTarget.text(Utils.formatDateOnly(data.tanggal_target));
            elements.infoTanggalSubmit.text(Utils.formatDateTime(data.tanggal_submit));
            elements.infoTanggalApprove.text(Utils.formatDateTime(data.tanggal_approve));
            elements.infoApprovedBy.text(data.approved_by_petugas || '-');
            elements.infoCatatanApproval.text(data.catatan_approval || 'Tidak ada catatan');
            elements.infoCreatedBy.text(data.created_by_petugas || '-');
            elements.infoCreatedAt.text(Utils.formatDateTime(data.created_at));
        }

        /**
         * Update Informasi Kegiatan section
         */
        updateInformasiKegiatan(data) {
            const elements = this.dom.elements;

            elements.infoKecamatan.text(data.nama_kecamatan || '-');
            elements.infoDesa.text(data.nama_desa || '-');
            elements.infoKegiatan.text(data.nama_kegiatan || '-');
            elements.infoPeriode.text(`${data.tahun} - ${Utils.getBulanName(data.bulan)}`);
            elements.infoJenisKegiatan.text(data.nama_jenis || '-');

            elements.infoBatasUpload.text(
                Utils.calculateBatasUpload(data.tanggal_selesai, data.batas_akhir_upload, data.tahun, data
                    .bulan)
            );

            elements.infoKodeKegiatan.text(data.kode_kegiatan || '-');
            elements.infoDasarHukum.text(data.dasar_hukum || '-');
            elements.infoTahunAnggaran.text(data.tahun_anggaran || '-');
            elements.infoRentangWaktu.text(
                Utils.formatRentangWaktu(data.tanggal_mulai, data.tanggal_selesai)
            );
        }

        /**
         * Update History Laporan timeline
         */
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
                    <h6 class="mb-1">${item.event}</h6>
                    <small class="text-muted">${formattedTime}</small>
                    <p class="mb-0">${item.user}</p>
                    ${item.catatan ? `<small class="text-muted">${item.catatan}</small>` : ''}
                </div>
            </div>
        `;
        }

        /**
         * Update Questions and Documents section
         */
        updateQuestionsAndDocuments(data) {
            const container = this.dom.elements.questionsContainer;

            if (!data.pertanyaan || data.pertanyaan.length === 0) {
                container.html(`
                <div class="alert alert-warning">
                    <h6><i class="las la-exclamation-triangle me-2"></i>Tidak Ada Pertanyaan</h6>
                    <p class="mb-0">Belum ada pertanyaan yang ditetapkan untuk kegiatan ini.</p>
                </div>
            `);
                return;
            }

            const questionsHtml = data.pertanyaan.map((question, index) =>
                this.renderQuestion(question, data.jawaban, data.dokumen, index)
            ).join('');

            container.html(questionsHtml);
            this.initializeRevisionHistory();
        }

        renderQuestion(question, jawaban, dokumen, index) {
            const jawabanPertanyaan = jawaban.find(j => j.pertanyaan_id === question.id_pertanyaan);
            const statusJawaban = jawabanPertanyaan ? jawabanPertanyaan.jawaban_text : 'belum';
            const statusColor = statusJawaban === 'sudah' ? 'success' : 'danger';
            const statusIcon = statusJawaban === 'sudah' ? 'check-circle' : 'times-circle';
            const statusText = statusJawaban === 'sudah' ? 'SUDAH' : 'BELUM';

            return `
            <div class="question-section" data-question-id="${question.id_pertanyaan}">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                    <div class="flex-grow-1">
                        <h6 class="mb-1 text-${statusColor}">
                            <i class="las la-${statusIcon} me-2"></i>
                            <span class="badge bg-primary me-2">${index + 1}</span>
                            ${question.pertanyaan} - <span class="text-${statusColor}">${statusText}</span>
                        </h6>
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
                return `<div class="alert alert-info py-2"><small>Tidak ada persyaratan dokumen untuk pertanyaan ini.</small></div>`;
            }

            return requirements.map(req => this.renderRequirementDetail(req, jawaban, dokumen)).join('');
        }

        renderRequirementDetail(req, jawaban, dokumen) {
            const dokumenList = dokumen.filter(d =>
                d.persyaratan_id === req.id_persyaratan &&
                d.jawaban_id === jawaban?.id_jawaban
            );

            const currentDokumen = dokumenList.find(d => d.is_current) || dokumenList[0];
            const hasPreviousRevisions = dokumenList.length > 1;

            const statusBadge = currentDokumen ?
                `<span class="badge ${Utils.getStatusBadgeClass(currentDokumen.status)} ms-2">${Utils.getStatusDisplay(currentDokumen.status)}</span>` :
                '';

            return `
            <div class="requirement-item" data-requirement-id="${req.id_persyaratan}">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <div class="row">
                            <div class="col-12 mb-2">
                                <small class="text-muted">Persyaratan:</small>
                                <div class="fw-bold fs-6">
                                    ${req.nama_persyaratan}
                                    <span class="badge ${req.tipe === 'wajib' ? 'bg-danger' : 'bg-secondary'} ms-2">
                                        ${req.tipe === 'wajib' ? 'WAJIB' : 'TAMBAHAN'}
                                    </span>
                                    ${statusBadge}
                                </div>
                            </div>
                            <div class="col-12 mb-2">
                                <small class="text-muted">Deskripsi:</small>
                                <div class="fw-bold fs-6">${req.deskripsi || '-'}</div>
                            </div>
                            ${req.template_persyaratan ? `
                            <div class="col-12 mb-2">
                                <small class="text-muted">Template:</small>
                                <div class="fw-bold fs-6">
                                    <a href="/uploads/${req.template_persyaratan}" target="_blank" class="text-decoration-none">
                                        <i class="las la-download me-1"></i>Download Template
                                    </a>
                                </div>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="row">
                            <div class="col-12 mb-2">
                                <small class="text-muted">File:</small>
                                <div class="fw-bold fs-6">
                                    ${currentDokumen ?
                                        `<div class="d-flex align-items-center">
                                            <i class="las la-file-pdf text-danger me-2"></i>
                                            <a href="/uploads/${currentDokumen.path_file}" target="_blank" class="text-decoration-none">
                                                ${currentDokumen.nama_file}
                                            </a>
                                            <small class="text-muted ms-2 file-version">V.${currentDokumen.version}</small>
                                        </div>` :
                                        '<span class="text-danger"><i class="las la-times-circle me-1"></i>File tidak ditemukan</span>'
                                    }
                                </div>
                            </div>
                            <div class="col-12 mb-2">
                                <small class="text-muted">Catatan Revisi:</small>
                                <div class="fw-bold fs-6">
                                    ${currentDokumen?.catatan_revisi || 'Tidak ada catatan revisi'}
                                </div>
                            </div>
                            <div class="col-12 mb-2">
                                <small class="text-muted">Tanggal Upload:</small>
                                <div class="fw-bold fs-6">
                                    ${currentDokumen ? Utils.formatDateTime(currentDokumen.created_at) : '-'}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                ${hasPreviousRevisions ? this.renderRevisionHistory(req.id_persyaratan, dokumenList) : ''}
            </div>
        `;
        }

        renderRevisionHistory(requirementId, dokumenList) {
            const revisions = dokumenList.filter(d => !d.is_current).sort((a, b) => b.version - a.version);
            if (!revisions.length) return '';

            const historyHtml = revisions.map(rev => `
            <div class="revision-item">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <strong class="d-block">v${rev.version} - ${Utils.getStatusDisplay(rev.status)}</strong>
                        <a href="/uploads/${rev.path_file}" target="_blank" class="text-decoration-none small">
                            <i class="las la-download me-1"></i>${rev.nama_file}
                        </a>
                        ${rev.catatan_revisi ? `
                            <div class="mt-1">
                                <small class="text-muted">Catatan:</small>
                                <div class="small text-warning">${rev.catatan_revisi}</div>
                            </div>
                        ` : ''}
                        <div class="mt-1">
                            <small class="text-muted">Upload: ${Utils.formatDateTime(rev.created_at)}</small>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');

            return `
            <div class="mt-3">
                <button class="btn btn-sm btn-outline-secondary" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#revisionHistory-${requirementId}"
                    aria-expanded="false">
                    <i class="las la-history me-1"></i>Riwayat Revisi (${revisions.length})
                </button>
                <div class="collapse mt-2" id="revisionHistory-${requirementId}">
                    <div class="revision-history-container">${historyHtml}</div>
                </div>
            </div>
        `;
        }

        initializeRevisionHistory() {
            $('.collapse').on('show.bs.collapse', function() {
                $(this).prev().find('i').removeClass('la-history').addClass('la-chevron-up');
            }).on('hide.bs.collapse', function() {
                $(this).prev().find('i').removeClass('la-chevron-up').addClass('la-history');
            });
        }

        /**
         * Update progress kelengkapan dokumen
         */
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
            this.dom.elements.downloadLaporanBtn.on('click', () => this.handleDownloadLaporan());

            // Print Laporan
            this.dom.elements.printLaporanBtn.on('click', () => this.handlePrintLaporan());
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
            console.log('🚀 Initializing Laporan Detail App...');

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
    $(document).ready(function() {
        const app = new LaporanDetailApp();
        app.initialize();
    });
</script>
