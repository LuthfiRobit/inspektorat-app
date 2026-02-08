@section('this-page-scripts')
    <script>
        // Configuration constants
        const CONFIG = {
            ROUTES: {
                GET_LAPORAN_DATA: '{{ route('administrator.monev.laporan.get-data', ':id') }}',
                GET_KEGIATAN_DATA: '{{ route('administrator.monev.laporan.get-kegiatan-data') }}',
                SUBMIT_REVIEW: '{{ route('administrator.monev.review.submit') }}',
                REVIEW_INDEX: '{{ route('administrator.monev.review.index') }}'
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
            }
        };

        // Application state
        const AppState = {
            currentLaporanId: {{ $id_laporan ?? 'null' }},
            kegiatanData: null,
            desaData: null,
            questionsData: [],
            dokumenData: {}
        };

        // DOM Elements cache
        const DOM = {
            elements: {},
            initialize() {
                this.elements = {
                    // Info displays (Updated to match New Professional Layout)
                    detailDesa: $('#detail_desa'),
                    detailKecamatan: $('#detail_kecamatan'),
                    detailNamaKegiatan: $('#detail_nama_kegiatan'),
                    detailTahun: $('#detail_tahun'),
                    detailKodeKegiatan: $('#detail_kode_kegiatan'),
                    detailJenisKegiatan: $('#detail_jenis_kegiatan'),
                    detailBulan: $('#detail_bulan'), // Periode
                    detailDasarHukum: $('#detail_dasar_hukum'),

                    // Timeline Displays
                    detailTanggalMulai: $('#detail_tanggal_mulai'),
                    detailTanggalSelesai: $('#detail_tanggal_selesai'),
                    detailBatasUpload: $('#detail_batas_upload'),

                    // Old Info displays (Kept for compatibility if needed, but primary is above)
                    infoCreatedAt: $('#info_created_at'),
                    infoSubmittedAt: $('#info_submitted_at'),
                    infoCurrentStatus: $('#info_current_status'),
                    infoCreatedBy: $('#info_created_by'),
                    infoCatatanLaporan: $('#info_catatan_laporan'),

                    // Form elements
                    questionsContainer: $('#questionsContainer'),
                    catatanApproval: $('#catatan_approval'),
                    statusRevision: $('#statusRevision'),
                    statusApproved: $('#statusApproved'),

                    // Buttons
                    requestRevisionBtn: $('#requestRevisionBtn, #requestRevisionBottom'),
                    approveLaporanBtn: $('#approveLaporanBtn, #approveLaporanBottom')
                };
            }
        };

        // Utility functions
        const Utils = {
            formatDateTime(dateTimeString) {
                if (!dateTimeString) return '-';
                const date = new Date(dateTimeString);
                return date.toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            },

            getStatusDisplay(status) {
                return CONFIG.STATUS_DISPLAY[status] || status;
            },

            getBulanName(bulanNumber) {
                return CONFIG.BULAN_NAMES[bulanNumber] || bulanNumber;
            },

            // Helper: Format Date Specific (Tgl + Bulan(Int) + Tahun)
            formatDateSpecific(tanggal, bulanInt, tahun) {
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
            },

            // Helper: Format Batas Upload Specific
            formatBatasUploadSpecific(batasHari, tanggalSelesai, bulanInt, tahun) {
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
            },

            showErrorAlert(message) {
                const alertHtml = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="las la-exclamation-circle me-2"></i>
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>`;
                $('#formAlerts').html(alertHtml);
            },

            setButtonsLoading(loading) {
                const buttons = DOM.elements.requestRevisionBtn.add(DOM.elements.approveLaporanBtn);
                buttons.prop('disabled', loading);

                if (loading) {
                    buttons.html('<span class="spinner-border spinner-border-sm me-1"></span>Memproses...');
                } else {
                    DOM.elements.requestRevisionBtn.html('<i class="las la-redo-alt me-1"></i>Minta Revisi');
                    DOM.elements.approveLaporanBtn.html('<i class="las la-check-circle me-1"></i>Setujui Laporan');
                }
            },

            // Fix: Proper async sleep function
            sleep(ms) {
                return new Promise(resolve => setTimeout(resolve, ms));
            }
        };

        // Data loading functions
        const DataLoader = {
            async loadLaporanData(laporanId) {
                console.log("🔄 Loading laporan data for review:", laporanId);

                const url = CONFIG.ROUTES.GET_LAPORAN_DATA.replace(':id', laporanId);

                try {
                    const response = await AjaxHandler.sendGetRequestAsync(url);

                    if (response?.status === 200 && response.data) {
                        console.log("✅ Laporan data loaded for review:", {
                            status: response.data.laporan.status,
                            total_dokumen: response.data.dokumen?.length || 0
                        });

                        this.processLaporanData(response.data);
                        // Store laporan context for detailed info population
                        AppState.laporanContext = response.data.laporan;
                        await this.loadKegiatanData(response.data.laporan.desa_id, response.data.laporan
                            .kegiatan_id);
                    } else {
                        Utils.showErrorAlert('Gagal memuat data laporan: ' + (response?.message ||
                            'Response tidak valid'));
                    }
                } catch (error) {
                    Utils.showErrorAlert('Terjadi kesalahan saat memuat data laporan');
                    console.error('Error loading laporan data:', error);
                }
            },

            processLaporanData(data) {
                if (!data.laporan) return;

                const laporan = data.laporan;

                // Update info displays (Status Laporan)
                DOM.elements.infoCreatedAt.text(Utils.formatDateTime(laporan.created_at));
                DOM.elements.infoSubmittedAt.text(Utils.formatDateTime(laporan.tanggal_submit || laporan.updated_at));
                DOM.elements.infoCurrentStatus.text(Utils.getStatusDisplay(laporan.status));
                DOM.elements.infoCreatedBy.text(laporan.created_by_petugas || 'Tidak diketahui');
                DOM.elements.infoCatatanLaporan.text(laporan.catatan_laporan || 'Tidak ada catatan');
                DOM.elements.catatanApproval.val(laporan.catatan_approval || '');

                // Set status radio
                laporan.status === 'approved' ?
                    DOM.elements.statusApproved.prop('checked', true) :
                    DOM.elements.statusRevision.prop('checked', true);

                // Store dokumen data dengan status persetujuan
                if (data.dokumen?.length > 0) {
                    data.dokumen.forEach(dokumen => {
                        const key = dokumen.persyaratan_id;
                        if (!AppState.dokumenData[key]) {
                            AppState.dokumenData[key] = [];
                        }
                        AppState.dokumenData[key].push(dokumen);
                    });

                    // Initialize dokumen status setelah data loaded
                    this.initializeDokumenStatus();
                }

                // Populate jawaban
                if (data.jawaban?.length > 0) {
                    data.jawaban.forEach(jawaban => {
                        $(`#jawaban-display-${jawaban.pertanyaan_id}`).text(
                            jawaban.jawaban_text === 'sudah' ? '✅ Sudah' : '❌ Belum'
                        );
                    });
                }
            },

            initializeDokumenStatus() {
                // Set initial state untuk semua dokumen berdasarkan data yang ada
                setTimeout(() => {
                    $('.dokumen-status:checked').each(function () {
                        const questionId = $(this).data('question-id');
                        const requirementId = $(this).data('requirement-id');
                        const status = $(this).val();
                        DocumentStatusHandler.updateDokumenStatus(questionId, requirementId, status);
                    });
                }, 100);
            },

            async loadKegiatanData(desaId, kegiatanId) {
                const url = `${CONFIG.ROUTES.GET_KEGIATAN_DATA}?desa_id=${desaId}&kegiatan_id=${kegiatanId}`;
                // Add context from laporan if available (untuk support insidentil)
                if (AppState.laporanContext) {
                    if (AppState.laporanContext.bulan) {
                        // url += `&bulan=${AppState.laporanContext.bulan}`;
                        // Note: Review logic currently expects kegiatan master data primarily.
                        // We will use AppState.laporanContext.bulan during population if needed.
                    }
                }

                try {
                    const response = await $.ajax({
                        url: url,
                        method: 'GET',
                        dataType: 'json'
                    });

                    if (response.status === 200 && response.data) {
                        AppState.kegiatanData = response.data.kegiatan;
                        AppState.desaData = response.data.desa;
                        AppState.questionsData = response.data.pertanyaan || [];
                        AppState.kegiatanContext = response.data.context || {};

                        this.populateKegiatanInfo();
                        Renderer.renderQuestionsForReview();
                    } else {
                        Utils.showErrorAlert('Gagal memuat data kegiatan: ' + (response.message ||
                            'Unknown error'));
                    }
                } catch (error) {
                    const errorMessage = error.responseJSON?.message ||
                        'Terjadi kesalahan saat memuat data kegiatan';
                    Utils.showErrorAlert(errorMessage);
                    console.error('Error loading kegiatan data:', error);
                }
            },

            populateKegiatanInfo() {
                if (!AppState.kegiatanData || !AppState.desaData) return;

                const kegiatan = AppState.kegiatanData;
                const desa = AppState.desaData;
                const context = AppState.kegiatanContext || {};
                const laporan = AppState.laporanContext || {}; // Fallback if context missing

                // Use bulan from laporan record effectively as the context month
                const effectiveBulan = laporan.bulan || context.bulan_nama; // integer or string depending on source

                // 1. Info Header
                DOM.elements.detailDesa.text(desa.nama_desa || '-');
                DOM.elements.detailKecamatan.text(desa.nama_kecamatan || '-');

                // 2. Info Kegiatan
                DOM.elements.detailNamaKegiatan.text(kegiatan.nama_kegiatan || '-');
                DOM.elements.detailTahun.text(context.tahun || kegiatan.tahun_anggaran || '-');
                DOM.elements.detailKodeKegiatan.text(kegiatan.kode_kegiatan || '-');
                DOM.elements.detailJenisKegiatan.text(kegiatan.nama_jenis || '-');

                // Logic Display Periode
                let periodeText = kegiatan.bulan_master || 'N/A';
                if (effectiveBulan) {
                    // If effectiveBulan is integer, convert name
                    if (Number.isInteger(parseInt(effectiveBulan))) {
                        periodeText = Utils.getBulanName(effectiveBulan);
                    } else {
                        periodeText = effectiveBulan;
                    }
                }

                if (kegiatan.frekuensi_pelaporan) {
                    periodeText += ` (Rutin: Setiap ${kegiatan.frekuensi_pelaporan} Bulan)`;
                }
                DOM.elements.detailBulan.text(periodeText);
                DOM.elements.detailDasarHukum.text(kegiatan.dasar_hukum || 'Tidak ada dasar hukum');

                // 3. Timeline Logic
                const tahun = context.tahun || kegiatan.tahun_anggaran;
                let startBulan, endBulan;

                if (kegiatan.frekuensi_pelaporan) {
                    // Rutin (Use Master Data)
                    startBulan = kegiatan.bulan_mulai;
                    endBulan = kegiatan.bulan_selesai;
                } else {
                    // Insidentil (Use Laporan Month as Context)
                    // Logic: If insidentil, dates are usually relative or specific to the month reported
                    // Here we try to use the reported month as the start/end month for formatting consistency
                    // OR fallback to master data logic if available
                    const reportedMonth = laporan.bulan || context.bulan || kegiatan.bulan;
                    startBulan = reportedMonth;
                    endBulan = reportedMonth;
                }

                // Render Dates
                DOM.elements.detailTanggalMulai.text(
                    Utils.formatDateSpecific(kegiatan.tanggal_mulai, startBulan, tahun)
                );
                DOM.elements.detailTanggalSelesai.text(
                    Utils.formatDateSpecific(kegiatan.tanggal_selesai, endBulan, tahun)
                );
                DOM.elements.detailBatasUpload.text(
                    Utils.formatBatasUploadSpecific(kegiatan.batas_akhir_upload, kegiatan.tanggal_selesai, endBulan, tahun)
                );
            }
        };

        // Rendering functions
        const Renderer = {
            renderQuestionsForReview() {
                if (!AppState.questionsData?.length) {
                    DOM.elements.questionsContainer.html(`
                        <div class="alert alert-warning">
                            <h6>Tidak Ada Pertanyaan</h6>
                            <p>Belum ada pertanyaan yang ditetapkan untuk kegiatan ini.</p>
                        </div>
                    `);
                    return;
                }

                const questionsHtml = AppState.questionsData.map((question, index) =>
                    this.renderQuestion(question, index)
                ).join('');

                DOM.elements.questionsContainer.html(questionsHtml);
            },

            renderQuestion(question, index) {
                return `
                    <div class="question-section mb-4 border rounded p-3" data-question-id="${question.id_pertanyaan}">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">
                                    <span class="badge bg-primary me-2">${index + 1}</span>
                                    ${question.pertanyaan}
                                </h6>
                                <div class="jawaban-display">
                                    <strong>Jawaban:</strong>
                                    <span id="jawaban-display-${question.id_pertanyaan}" class="badge bg-secondary">-</span>
                                </div>
                            </div>
                        </div>
                        <div class="requirements-section">
                            <label class="form-label mb-2"><strong>Dokumen Persyaratan:</strong></label>
                            ${this.renderRequirementsForReview(question.persyaratan, question.id_pertanyaan)}
                        </div>
                    </div>
                `;
            },

            renderRequirementsForReview(requirements, questionId) {
                if (!requirements?.length) {
                    return `<div class="alert alert-info py-2"><small>Tidak ada persyaratan dokumen untuk pertanyaan ini.</small></div>`;
                }

                return requirements.map(req => this.renderRequirement(req, questionId)).join('');
            },

            renderRequirement(req, questionId) {
                const dokumenList = AppState.dokumenData[req.id_persyaratan] || [];
                const currentDokumen = dokumenList.find(d => d.is_current) || dokumenList[0];
                const hasPreviousRevisions = dokumenList.length > 1;

                return `
                    <div class="alert alert-primary requirement-item" data-requirement-id="${req.id_persyaratan}">
                        ${this.renderRequirementInfo(req, currentDokumen)}
                        <hr>
                        ${this.renderReviewActions(req, questionId, currentDokumen, hasPreviousRevisions, dokumenList)}
                    </div>
                `;
            },

            renderRequirementInfo(req, currentDokumen) {
                const statusPersetujuan = currentDokumen?.status_persetujuan;
                const statusBadge = statusPersetujuan === 'approved' ?
                    '<span class="badge bg-success ms-2">APPROVED</span>' :
                    statusPersetujuan === 'revision' ?
                        '<span class="badge bg-warning ms-2">PERLU REVISI</span>' :
                        '';

                return `
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
                                                                <small class="text-muted ms-2">V.(${currentDokumen.version})</small>
                                                            </div>` :
                        '<span class="text-danger"><i class="las la-times-circle me-1"></i>File tidak ditemukan</span>'
                    }
                            </div>
                        </div>
                        <div class="col-12 mb-2">
                            <small class="text-muted">Catatan Revisi Sebelumnya:</small>
                            <div class="fw-bold fs-6">
                                ${currentDokumen?.catatan_revisi || 'Tidak ada catatan revisi sebelumnya'}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
            },

            renderReviewActions(req, questionId, currentDokumen, hasPreviousRevisions, dokumenList) {
                // Set status sebelumnya jika ada
                const previousStatus = currentDokumen?.status_persetujuan;
                const isApprovedChecked = previousStatus === 'approved' ? 'checked' : 'checked';
                const isRevisionChecked = previousStatus === 'revision' ? 'checked' : '';

                // Jika ada status sebelumnya, gunakan itu sebagai default
                const defaultChecked = previousStatus ?
                    (previousStatus === 'approved' ? 'checked' : '') :
                    'checked';

                return `
            <div class="row">
                <div class="col-md-6 mb-2">
                    <small class="text-muted">Status Dokumen:</small>
                    <div class="mt-1">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input dokumen-status" type="radio" 
                                name="dokumen_status[${questionId}][${req.id_persyaratan}]" 
                                id="status_approved_${questionId}_${req.id_persyaratan}" 
                                value="approved" ${previousStatus === 'approved' ? 'checked' : defaultChecked}
                                data-question-id="${questionId}"
                                data-requirement-id="${req.id_persyaratan}">
                            <label class="form-check-label text-success fw-bold" for="status_approved_${questionId}_${req.id_persyaratan}">
                                <i class="las la-check-circle me-1"></i>Approved
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input dokumen-status" type="radio" 
                                name="dokumen_status[${questionId}][${req.id_persyaratan}]" 
                                id="status_revision_${questionId}_${req.id_persyaratan}" 
                                value="revision" ${previousStatus === 'revision' ? 'checked' : ''}
                                data-question-id="${questionId}"
                                data-requirement-id="${req.id_persyaratan}">
                            <label class="form-check-label text-warning fw-bold" for="status_revision_${questionId}_${req.id_persyaratan}">
                                <i class="las la-redo-alt me-1"></i>Perlu Revisi
                            </label>
                        </div>
                        <div class="mt-1">
                            <small class="text-muted" id="status-description-${questionId}-${req.id_persyaratan}">
                                ${previousStatus === 'approved' ? 'Dokumen sudah disetujui' :
                        previousStatus === 'revision' ? 'Dokumen memerlukan revisi' :
                            'Dokumen menunggu review'}
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-2">
                    <small class="text-muted">
                        Catatan Revisi:
                        <span class="text-danger revision-required-${questionId}-${req.id_persyaratan}" 
                              style="display: ${previousStatus === 'revision' ? 'inline' : 'none'};">*</span>
                    </small>
                    <textarea class="form-control form-control-sm catatan-revisi mt-1" 
                        id="catatan_${questionId}_${req.id_persyaratan}" 
                        name="catatan_revisi[${questionId}][${req.id_persyaratan}]" 
                        rows="3" 
                        placeholder="Berikan catatan revisi yang jelas dan spesifik..."
                        ${previousStatus === 'revision' ? 'required' : ''}>${currentDokumen?.catatan_revisi || ''}</textarea>

                    <div class="mt-2">
                        <small class="text-muted">
                            Status saat ini: 
                            <span id="status-indicator-${questionId}-${req.id_persyaratan}" 
                                  class="fw-bold ${previousStatus === 'approved' ? 'text-success' :
                        previousStatus === 'revision' ? 'text-warning' : 'text-info'}">
                                ${previousStatus === 'approved' ? 'Approved' :
                        previousStatus === 'revision' ? 'Perlu Revisi' : 'Menunggu Review'}
                            </span>
                        </small>
                    </div>

                    ${hasPreviousRevisions ? this.renderRevisionHistoryButton(questionId, req.id_persyaratan, dokumenList) : ''}
                </div>
            </div>
        `;
            },

            renderStatusRadioButtons(questionId, requirementId) {
                return `
                    <div class="form-check form-check-inline">
                        <input class="form-check-input dokumen-status" type="radio" 
                            name="dokumen_status[${questionId}][${requirementId}]" 
                            id="status_approved_${questionId}_${requirementId}" 
                            value="approved" checked
                            data-question-id="${questionId}"
                            data-requirement-id="${requirementId}">
                        <label class="form-check-label text-success fw-bold" for="status_approved_${questionId}_${requirementId}">
                            <i class="las la-check-circle me-1"></i>Approved
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input dokumen-status" type="radio" 
                            name="dokumen_status[${questionId}][${requirementId}]" 
                            id="status_revision_${questionId}_${requirementId}" 
                            value="revision"
                            data-question-id="${questionId}"
                            data-requirement-id="${requirementId}">
                        <label class="form-check-label text-warning fw-bold" for="status_revision_${questionId}_${requirementId}">
                            <i class="las la-redo-alt me-1"></i>Perlu Revisi
                        </label>
                    </div>
                `;
            },

            renderRevisionHistoryButton(questionId, requirementId, dokumenList) {
                return `
                    <div class="mt-2">
                        <button class="btn btn-sm btn-outline-secondary" type="button" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#revisionHistory-${questionId}-${requirementId}" 
                            aria-expanded="false">
                            <i class="las la-history me-1"></i>Riwayat Revisi (${dokumenList.length - 1})
                        </button>
                        <div class="collapse mt-2" id="revisionHistory-${questionId}-${requirementId}">
                            ${this.renderRevisionHistory(dokumenList)}
                        </div>
                    </div>
                `;
            },

            renderRevisionHistory(dokumenList) {
                const revisions = dokumenList.filter(d => !d.is_current).sort((a, b) => b.version - a.version);
                if (!revisions.length) return '';

                const historyHtml = revisions.map(rev => `
                    <div class="revision-item border-start border-3 border-warning ps-2 mb-2 py-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <strong class="d-block">v${rev.version}</strong>
                                <a href="/uploads/${rev.path_file}" target="_blank" class="text-decoration-none small">
                                    <i class="las la-download me-1"></i>${rev.nama_file}
                                </a>
                                ${rev.catatan_revisi ? `
                                                                        <div class="mt-1">
                                                                            <small class="text-muted">Catatan:</small>
                                                                            <div class="small text-warning">${rev.catatan_revisi}</div>
                                                                        </div>
                                                                    ` : ''}
                            </div>
                            <small class="text-muted text-nowrap ms-2">
                                ${Utils.formatDateTime(rev.created_at)}
                            </small>
                        </div>
                    </div>
                `).join('');

                return `<div class="revision-history-container">${historyHtml}</div>`;
            }
        };

        // Validation functions
        const Validator = {
            validateReviewSubmission() {
                const status = $('input[name="status"]:checked').val();
                const catatanApproval = DOM.elements.catatanApproval.val().trim();

                // Validate main review note for revision status
                if (status === 'revision' && !catatanApproval) {
                    this.showValidationError('Catatan Review Diperlukan',
                        'Untuk status "Minta Revisi", harap berikan catatan review terlebih dahulu.');
                    return false;
                }

                // Validate document revision notes - lebih informatif
                const invalidDocuments = this.getDocumentsWithoutRevisionNotes();
                if (invalidDocuments.length > 0) {
                    const docNames = invalidDocuments.map(doc => doc.name).join(', ');
                    this.showValidationError('Catatan Revisi Dokumen Diperlukan',
                        `Dokumen berikut memerlukan revisi tetapi belum diberikan catatan: ${docNames}`);
                    return false;
                }

                return true;
            },

            getDocumentsWithoutRevisionNotes() {
                const invalidDocuments = [];

                $('.dokumen-status:checked').each(function () {
                    const questionId = $(this).data('question-id');
                    const requirementId = $(this).data('requirement-id');
                    const status = $(this).val();
                    const catatan = $(`#catatan_${questionId}_${requirementId}`).val().trim();

                    if (status === 'revision' && !catatan) {
                        // Dapatkan nama persyaratan untuk pesan error yang lebih informatif
                        const requirementName = $(this).closest('.requirement-item')
                            .find('.fw-bold.fs-6')
                            .first()
                            .text()
                            .split('WAJIB')[0]
                            .split('TAMBAHAN')[0]
                            .trim();

                        invalidDocuments.push({
                            questionId,
                            requirementId,
                            name: requirementName
                        });
                    }
                });

                return invalidDocuments;
            },

            showValidationError(title, message) {
                Swal.fire({
                    icon: 'warning',
                    title: title,
                    text: message,
                    confirmButtonText: 'Mengerti',
                    confirmButtonColor: '#ffc107'
                });
            }
        };

        // Review submission functions - FIXED VERSION
        const ReviewSubmitter = {
            async submitReview() {
                const status = $('input[name="status"]:checked').val();

                if (!Validator.validateReviewSubmission()) {
                    return;
                }

                const confirmationConfig = this.getConfirmationConfig(status);
                const result = await Swal.fire(confirmationConfig);

                if (result.isConfirmed) {
                    await this.processReviewSubmission(status);
                } else {
                    Utils.setButtonsLoading(false);
                }
            },

            getConfirmationConfig(status) {
                const dokumenRevisiCount = $('.dokumen-status:checked[value="revision"]').length;
                const totalDokumen = $('.dokumen-status:checked').length;
                const catatanApproval = DOM.elements.catatanApproval.val();

                const baseConfig = {
                    showCancelButton: true,
                    cancelButtonText: 'Batal',
                    cancelButtonColor: '#6c757d',
                    reverseButtons: true
                };

                if (status === 'approved') {
                    return {
                        ...baseConfig,
                        icon: 'success',
                        title: 'Setujui Laporan?',
                        html: this.getApprovalConfirmationHTML(dokumenRevisiCount, totalDokumen, catatanApproval),
                        confirmButtonText: 'Ya, Setujui Laporan',
                        confirmButtonColor: '#198754'
                    };
                } else {
                    return {
                        ...baseConfig,
                        icon: 'warning',
                        title: 'Minta Revisi Laporan?',
                        html: this.getRevisionConfirmationHTML(dokumenRevisiCount, totalDokumen, catatanApproval),
                        confirmButtonText: 'Ya, Minta Revisi',
                        confirmButtonColor: '#ffc107'
                    };
                }
            },

            getApprovalConfirmationHTML(dokumenRevisiCount, totalDokumen, catatanApproval) {
                return `
                    <div class="text-start">
                        <p>Anda akan menyetujui laporan kegiatan ini. Tindakan ini tidak dapat dibatalkan.</p>
                        ${dokumenRevisiCount > 0 ?
                        `<div class="alert alert-warning py-2">
                                                                    <i class="las la-exclamation-triangle me-1"></i>
                                                                    <strong>Perhatian:</strong> ${dokumenRevisiCount} dari ${totalDokumen} dokumen ditandai perlu revisi, tetapi status laporan akan disetujui.
                                                                </div>` :
                        '<p>Semua dokumen telah disetujui.</p>'
                    }
                        ${this.getCatatanReviewHTML(catatanApproval)}
                    </div>
                `;
            },

            getRevisionConfirmationHTML(dokumenRevisiCount, totalDokumen, catatanApproval) {
                return `
                    <div class="text-start">
                        <p>Anda akan mengirim permintaan revisi untuk laporan ini.</p>
                        ${dokumenRevisiCount > 0 ?
                        `<div class="alert alert-info py-2">
                                                                    <i class="las la-info-circle me-1"></i>
                                                                    <strong>Info:</strong> ${dokumenRevisiCount} dari ${totalDokumen} dokumen memerlukan revisi.
                                                                </div>` :
                        '<div class="alert alert-warning py-2">Tidak ada dokumen yang ditandai perlu revisi, tetapi status laporan akan diubah menjadi revisi.</div>'
                    }
                        ${this.getCatatanReviewHTML(catatanApproval)}
                    </div>
                `;
            },

            getCatatanReviewHTML(catatanApproval) {
                return catatanApproval ? `
                    <div class="mt-3">
                        <strong>Catatan Review:</strong>
                        <div class="border rounded p-2 mt-1 bg-light">${catatanApproval}</div>
                    </div>
                ` : '';
            },

            async processReviewSubmission(status) {
                Utils.setButtonsLoading(true);

                try {
                    // FIX: Remove the problematic showLoadingAlert and use simpler approach
                    console.log("🔄 Processing review submission...");

                    const formData = this.prepareFormData(status);
                    const response = await this.sendReviewData(formData);

                    await this.handleSuccessResponse(status);
                } catch (error) {
                    console.error("❌ Error in processReviewSubmission:", error);
                    this.handleErrorResponse(error);
                }
            },

            prepareFormData(status) {
                const formData = new FormData();
                formData.append('laporan_id', AppState.currentLaporanId);
                formData.append('status', status);
                formData.append('catatan_approval', DOM.elements.catatanApproval.val());
                formData.append('_token', '{{ csrf_token() }}');

                // Add document status and revision notes
                $('.dokumen-status:checked').each(function () {
                    const questionId = $(this).data('question-id');
                    const requirementId = $(this).data('requirement-id');
                    const status = $(this).val();
                    const catatan = $(`#catatan_${questionId}_${requirementId}`).val().trim();

                    formData.append(`dokumen_status[${questionId}][${requirementId}]`, status);
                    if (catatan) {
                        formData.append(`catatan_revisi[${questionId}][${requirementId}]`, catatan);
                    }
                });

                return formData;
            },

            async sendReviewData(formData) {
                return await $.ajax({
                    url: CONFIG.ROUTES.SUBMIT_REVIEW,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    timeout: 30000 // 30 second timeout
                });
            },

            async handleSuccessResponse(status) {
                // FIX: Use simpler success handling without nested Swal alerts
                console.log("✅ Review submitted successfully");

                const successMessage = status === 'approved' ?
                    'Laporan berhasil disetujui!' :
                    'Permintaan revisi berhasil dikirim!';

                // Show success message
                await Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: successMessage,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#198754'
                });

                // Redirect to review index
                window.location.href = CONFIG.ROUTES.REVIEW_INDEX;
            },

            handleErrorResponse(error) {
                console.error("❌ Error in handleErrorResponse:", error);
                Utils.setButtonsLoading(false);

                let errorMessage = 'Gagal menyimpan review';

                if (error.status === 422) {
                    // Validation errors
                    const errors = error.responseJSON?.errors;
                    if (errors) {
                        errorMessage = Object.values(errors).flat().join('<br>');
                    }
                } else if (error.status === 500) {
                    errorMessage = 'Terjadi kesalahan server. Silakan coba lagi.';
                } else if (error.status === 0 || error.status === 404) {
                    errorMessage = 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.';
                } else if (error.responseJSON?.message) {
                    errorMessage = error.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi Kesalahan',
                    html: `
                        <div class="text-start">
                            <p>${errorMessage}</p>
                            <small class="text-muted">Silakan coba lagi atau hubungi administrator.</small>
                        </div>
                    `,
                    confirmButtonText: 'Mengerti',
                    confirmButtonColor: '#dc3545'
                });
            }
        };

        // Event handlers
        const EventHandlers = {
            initialize() {
                // Review action buttons
                DOM.elements.requestRevisionBtn.on('click', () => {
                    DOM.elements.statusRevision.prop('checked', true);
                    ReviewSubmitter.submitReview();
                });

                DOM.elements.approveLaporanBtn.on('click', () => {
                    DOM.elements.statusApproved.prop('checked', true);
                    ReviewSubmitter.submitReview();
                });

                // Document status change
                $(document).on('change', '.dokumen-status', function () {
                    const questionId = $(this).data('question-id');
                    const requirementId = $(this).data('requirement-id');
                    DocumentStatusHandler.updateDokumenStatus(questionId, requirementId, $(this).val());
                });
            }
        };

        // Document status handler
        const DocumentStatusHandler = {
            updateDokumenStatus(questionId, requirementId, status) {
                const catatanField = $(`#catatan_${questionId}_${requirementId}`);
                const requiredIndicator = $(`.revision-required-${questionId}-${requirementId}`);
                const statusIndicator = $(`#status-indicator-${questionId}-${requirementId}`);
                const statusDescription = $(`#status-description-${questionId}-${requirementId}`);

                if (status === 'approved') {
                    catatanField.prop('required', false);
                    requiredIndicator.hide();
                    statusIndicator.text('Approved').removeClass('text-warning text-info').addClass('text-success');
                    statusDescription.text('Dokumen sudah memenuhi persyaratan').removeClass('text-warning').addClass(
                        'text-muted');
                } else {
                    catatanField.prop('required', true);
                    requiredIndicator.show();
                    statusIndicator.text('Perlu Revisi').removeClass('text-success text-info').addClass('text-warning');
                    statusDescription.text('Dokumen perlu diperbaiki').removeClass('text-muted').addClass(
                        'text-warning');
                }
            }
        };

        // Main initialization
        $(document).ready(function () {
            DOM.initialize();
            EventHandlers.initialize();

            // Initialize the review form
            const urlParams = new URLSearchParams(window.location.search);
            const laporanId = urlParams.get('id_laporan') || AppState.currentLaporanId;

            if (laporanId) {
                DataLoader.loadLaporanData(laporanId);
            } else {
                Utils.showErrorAlert('ID Laporan tidak ditemukan. Silakan pilih laporan dari halaman daftar.');
            }
        });

        // Add AjaxHandler.sendGetRequestAsync for compatibility
        if (typeof AjaxHandler !== 'undefined' && AjaxHandler.sendGetRequest && !AjaxHandler.sendGetRequestAsync) {
            AjaxHandler.sendGetRequestAsync = function (url) {
                return new Promise((resolve, reject) => {
                    AjaxHandler.sendGetRequest(url, resolve, reject);
                });
            };
        }
    </script>
@endsection