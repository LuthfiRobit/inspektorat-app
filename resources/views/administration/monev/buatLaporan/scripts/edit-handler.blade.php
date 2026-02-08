@section('this-page-scripts')
    <script>
        // Configuration constants
        const CONFIG = {
            ROUTES: {
                GET_LAPORAN_DATA: '{{ route('administrator.monev.laporan.get-data', ':id') }}',
                GET_KEGIATAN_DATA: '{{ route('administrator.monev.laporan.get-kegiatan-data') }}',
                UPDATE_LAPORAN: '{{ route('administrator.monev.laporan.update', ':id') }}',
                LAPORAN_INDEX: '{{ route('administrator.monev.laporan.index') }}'
            },
            STATUS_DISPLAY: {
                'draft': 'Draft',
                'submitted': 'Terkirim',
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
            uploadedFiles: {},
            dokumenStatus: {} // Untuk menyimpan status persetujuan dokumen
        };

        // DOM Elements cache
        const DOM = {
            elements: {},
            initialize() {
                this.elements = {
                    // Info displays
                    infoCreatedAt: $('#info_created_at'),
                    infoUpdatedAt: $('#info_updated_at'),
                    infoCurrentStatus: $('#info_current_status'),
                    infoCreatedBy: $('#info_created_by'),
                    infoApprovedBy: $('#info_approved_by'),
                    infoApprovedAt: $('#info_approved_at'),
                    infoApprovalNote: $('#info_approval_note'),
                    infoDesa: $('#info_desa'),
                    infoKegiatan: $('#info_kegiatan'),
                    infoPeriode: $('#info_periode'),
                    infoBatasUpload: $('#info_batas_upload'),
                    infoDasarHukum: $('#info_dasar_hukum'),

                    // Professional Layout Elements
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

                    // Form elements
                    questionsContainer: $('#questionsContainer'),
                    catatanLaporan: $('#catatan_laporan'),
                    statusDraft: $('#statusDraft'),
                    statusSubmit: $('#statusSubmit'),

                    // Buttons
                    saveDraftBtn: $('#saveDraftBtn, #saveDraftBottom'),
                    submitLaporanBtn: $('#submitLaporanBtn, #submitLaporanBottom'),

                    // Progress
                    formProgress: $('#formProgress'),
                    progressText: $('#progressText')
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

            formatDateSpecific(tanggal, bulanInt, tahun) {
                if (!tanggal || !bulanInt || !tahun) return 'N/A';

                try {
                    const monthIndex = parseInt(bulanInt) - 1;
                    const date = new Date(parseInt(tahun), monthIndex, parseInt(tanggal));

                    if (isNaN(date.getTime())) return 'N/A';

                    const day = date.getDate();
                    const month = date.toLocaleDateString('id-ID', { month: 'long' });
                    const year = date.getFullYear();

                    return `Tgl. ${day} ${month} ${year}`;
                } catch (error) {
                    console.error("Error formatting date specific:", error);
                    return 'N/A';
                }
            },

            formatBatasUploadSpecific(batasHari, tanggalSelesai, bulanInt, tahun) {
                if (!batasHari) return 'Tidak ada batas';
                if (!tanggalSelesai || !bulanInt || !tahun) return `${batasHari} hari setelah selesai`;

                try {
                    const monthIndex = parseInt(bulanInt) - 1;
                    const selesaiDate = new Date(parseInt(tahun), monthIndex, parseInt(tanggalSelesai));

                    if (isNaN(selesaiDate.getTime())) return `${batasHari} hari setelah selesai`;

                    const deadlineDate = new Date(selesaiDate);
                    deadlineDate.setDate(selesaiDate.getDate() + parseInt(batasHari));

                    const day = deadlineDate.getDate();
                    const month = deadlineDate.toLocaleDateString('id-ID', { month: 'long' });
                    const year = deadlineDate.getFullYear();

                    return `Tgl. ${day} ${month} ${year}`;
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

            showSuccessAlert(message, callback = null) {
                const alertHtml = `
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="las la-check-circle me-2"></i>
                            ${message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>`;
                $('#formAlerts').html(alertHtml);

                if (typeof callback === 'function') {
                    setTimeout(callback, 100);
                }
            },

            setButtonsLoading(loading) {
                const buttons = DOM.elements.saveDraftBtn.add(DOM.elements.submitLaporanBtn);
                buttons.prop('disabled', loading);

                if (loading) {
                    DOM.elements.saveDraftBtn.html(
                        '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');
                    DOM.elements.submitLaporanBtn.html(
                        '<span class="spinner-border spinner-border-sm me-1"></span>Mengirim...');
                } else {
                    DOM.elements.saveDraftBtn.html('<i class="las la-save me-1"></i>Simpan Draft');
                    DOM.elements.submitLaporanBtn.html('<i class="las la-paper-plane me-1"></i>Submit Laporan');
                }
            },

            // Check if dokumen is approved and should be readonly
            isDokumenReadonly(questionId, persyaratanId) {
                const key = `${questionId}-${persyaratanId}`;
                console.log("🔍 Checking dokumen readonly:", {
                    key,
                    status: AppState.dokumenStatus[key],
                    allStatuses: AppState.dokumenStatus
                });
                return AppState.dokumenStatus[key] === 'approved';
            }
        };

        // Data loading functions
        const DataLoader = {
            async initializeForm() {
                const urlParams = new URLSearchParams(window.location.search);
                const desaId = urlParams.get('desa_id');
                const kegiatanId = urlParams.get('kegiatan_id');

                if (desaId && kegiatanId && AppState.currentLaporanId) {
                    await this.loadKegiatanData(desaId, kegiatanId);
                    await this.loadExistingLaporan(AppState.currentLaporanId);
                } else {
                    Utils.showErrorAlert('Parameter tidak lengkap. Silakan pilih kegiatan dari halaman daftar.');
                }
            },

            async loadExistingLaporan(laporanId) {
                console.log("🔄 Loading laporan data for:", laporanId);

                const url = CONFIG.ROUTES.GET_LAPORAN_DATA.replace(':id', laporanId);

                try {
                    const response = await AjaxHandler.sendGetRequestAsync(url);

                    if (response?.status === 200 && response.data) {
                        console.log("✅ Laporan data loaded:", {
                            total_dokumen: response.data.dokumen?.length || 0,
                            dokumen_list: response.data.dokumen ? response.data.dokumen.map(d => ({
                                id: d.id_dokumen,
                                persyaratan_id: d.persyaratan_id,
                                is_current: d.is_current,
                                version: d.version,
                                status_persetujuan: d.status_persetujuan
                            })) : []
                        });
                        this.processLaporanData(response.data);
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

                // Update info displays
                DOM.elements.infoCreatedAt.text(Utils.formatDateTime(laporan.created_at));
                DOM.elements.infoCreatedBy.text(laporan.created_by_name || 'N/A');
                DOM.elements.infoUpdatedAt.text(Utils.formatDateTime(laporan.updated_at));
                DOM.elements.infoCurrentStatus.text(Utils.getStatusDisplay(laporan.status));
                DOM.elements.infoApprovedBy.text(laporan.approved_by_name || 'Belum di-approve');
                DOM.elements.infoApprovedAt.text(laporan.approved_at ? Utils.formatDateTime(laporan.approved_at) : '-');
                DOM.elements.infoApprovalNote.text(laporan.catatan_approval || 'Belum ada catatan');

                // Set form data
                $('#desa_id').val(laporan.desa_id);
                $('#kegiatan_id').val(laporan.kegiatan_id);
                $('#tahun').val(laporan.tahun);
                $('#bulan').val(laporan.bulan);
                DOM.elements.catatanLaporan.val(laporan.catatan_laporan || '');

                // Set status radio
                $(`input[name="status"][value="${laporan.status}"]`).prop('checked', true);

                // Reset uploadedFiles dan dokumenStatus
                AppState.uploadedFiles = {};
                AppState.dokumenStatus = {};

                // Populate dokumen - HANYA YANG is_current = true
                if (data.dokumen?.length > 0) {
                    const jawabanToPertanyaanMap = {};
                    if (data.jawaban?.length > 0) {
                        data.jawaban.forEach(jawaban => {
                            jawabanToPertanyaanMap[jawaban.id_jawaban] = jawaban.pertanyaan_id;
                        });
                    }

                    data.dokumen.forEach(dokumen => {
                        const pertanyaanId = jawabanToPertanyaanMap[dokumen.jawaban_id];
                        if (pertanyaanId && dokumen.is_current) {
                            const key = `${pertanyaanId}-${dokumen.persyaratan_id}`;

                            // Simpan file data
                            AppState.uploadedFiles[key] = [{
                                id: dokumen.id_dokumen,
                                name: dokumen.nama_file,
                                size: 'Unknown',
                                path: dokumen.path_file,
                                catatan_revisi: dokumen.catatan_revisi,
                                is_current: dokumen.is_current,
                                version: dokumen.version
                            }];

                            // Simpan status persetujuan
                            AppState.dokumenStatus[key] = dokumen.status;
                            console.log("💾 Storing dokumen status:", {
                                key,
                                status: dokumen.status,
                                pertanyaanId,
                                persyaratanId: dokumen.persyaratan_id
                            });
                        }
                    });

                    console.log("📁 UploadedFiles after populate:", AppState.uploadedFiles);
                    console.log("📊 DokumenStatus after populate:", AppState.dokumenStatus);
                }

                // RE-RENDER QUESTIONS SETELAH DATA DOKUMEN TERISI
                Renderer.renderQuestions();

                // SET ULANG RADIO BUTTON SETELAH RENDER - PERBAIKAN PENTING
                this.setRadioButtonsFromData(data.jawaban);

                // Set radio button berdasarkan file
                AppState.questionsData.forEach(question => {
                    FormHandler.checkAndSetRadioFromFiles(question.id_pertanyaan);
                });

                ProgressTracker.updateProgress();
            },

            // Fungsi baru untuk set ulang radio button setelah render
            setRadioButtonsFromData(jawabanData) {
                if (jawabanData?.length > 0) {
                    jawabanData.forEach(jawaban => {
                        $(`input[name="jawaban[${jawaban.pertanyaan_id}]"][value="${jawaban.jawaban_text}"]`)
                            .prop('checked', true);
                    });
                    console.log("✅ Radio buttons set from data:", jawabanData);
                }
            },

            async loadKegiatanData(desaId, kegiatanId) {
                const url = `${CONFIG.ROUTES.GET_KEGIATAN_DATA}?desa_id=${desaId}&kegiatan_id=${kegiatanId}`;

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

                        this.populateKegiatanInfo();
                        Renderer.renderQuestions();
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

                // Header Info
                DOM.elements.detailDesa.text(desa.nama_desa || '-');
                DOM.elements.detailKecamatan.text(`Kec. ${desa.nama_kecamatan || '-'}`);

                // Main Info
                DOM.elements.detailNamaKegiatan.text(kegiatan.nama_kegiatan || '-');
                DOM.elements.detailTahun.text(kegiatan.tahun || '-');
                DOM.elements.detailKodeKegiatan.text(kegiatan.kode_kegiatan || '-');
                DOM.elements.detailJenisKegiatan.text(kegiatan.nama_jenis || '-');
                DOM.elements.detailDasarHukum.text(kegiatan.dasar_hukum || 'Tidak ada dasar hukum');

                // Logic Display Periode
                let periodeText = kegiatan.nama_bulan || 'N/A';

                if (kegiatan.frekuensi_pelaporan) {
                    periodeText += ` (Rutin: Setiap ${kegiatan.frekuensi_pelaporan} Bulan)`;
                }
                DOM.elements.detailBulan.text(periodeText);

                // Timeline Logic
                const tahun = kegiatan.tahun;
                let startBulan, endBulan;

                if (kegiatan.frekuensi_pelaporan) {
                    // Rutin (Use Master Data)
                    startBulan = kegiatan.bulan_mulai;
                    endBulan = kegiatan.bulan_selesai;
                } else {
                    // Insidentil (Use Laporan Month as Context)
                    startBulan = kegiatan.bulan;
                    endBulan = kegiatan.bulan;
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
            renderQuestions() {
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

                // UPDATE FILE PREVIEW SETELAH RENDER
                AppState.questionsData.forEach(question => {
                    if (question.persyaratan) {
                        question.persyaratan.forEach(req => {
                            this.updateFilePreview(question.id_pertanyaan, req.id_persyaratan);
                            this.updateCatatanRevisi(question.id_pertanyaan, req.id_persyaratan);
                        });
                    }
                });
            },

            renderQuestion(question, index) {
                return `<div class="question-section mb-4" data-question-id="${question.id_pertanyaan}">
                                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0">
                                                <span class="badge bg-primary me-2">${index + 1}</span>
                                                ${question.pertanyaan}
                                            </h6>
                                        </div>
                                        <div class="d-flex align-items-center gap-3 d-none">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input answer-radio" type="radio"
                                                    name="jawaban[${question.id_pertanyaan}]"
                                                    id="jawaban-sudah-${question.id_pertanyaan}"
                                                    value="sudah">
                                                <label class="form-check-label" for="jawaban-sudah-${question.id_pertanyaan}">
                                                    Sudah
                                                </label>
                                            </div>
                                            <div class="form-check mb-0">
                                                <input class="form-check-input answer-radio" type="radio"
                                                    name="jawaban[${question.id_pertanyaan}]"
                                                    id="jawaban-belum-${question.id_pertanyaan}"
                                                    value="belum">
                                                <label class="form-check-label" for="jawaban-belum-${question.id_pertanyaan}">
                                                    Belum
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="requirements-section mt-3">
                                        <label class="form-label mb-2">Dokumen Persyaratan:</label>
                                        ${this.renderRequirements(question.persyaratan, question.id_pertanyaan)}
                                    </div>
                                </div>`;
            },

            renderRequirements(requirements, questionId) {
                if (!requirements?.length) {
                    return `
                            <div class="alert alert-info py-2">
                                <small>Tidak ada persyaratan dokumen untuk pertanyaan ini.</small>
                            </div>`;
                }

                return requirements.map(req => {
                    const key = `${questionId}-${req.id_persyaratan}`;
                    const hasTemplate = !!req.template_persyaratan;
                    const isRequired = req.tipe === 'wajib' ? 'required' : '';
                    const isReadonly = Utils.isDokumenReadonly(questionId, req.id_persyaratan);
                    const readonlyAttr = isReadonly ? 'readonly' : '';
                    const disabledAttr = isReadonly ? 'disabled' : '';

                    console.log("🎯 Rendering requirement:", {
                        questionId,
                        persyaratanId: req.id_persyaratan,
                        key,
                        isReadonly,
                        status: AppState.dokumenStatus[key]
                    });

                    return `<div class="mb-3 position-relative requirement-item" data-requirement-id="${req.id_persyaratan}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label for="file-${key}" class="form-label mb-0">
                                            ${req.nama_persyaratan}
                                            <span class="badge ${req.tipe === 'wajib' ? 'bg-danger' : 'bg-secondary'} ms-2">
                                                ${req.tipe === 'wajib' ? 'WAJIB' : 'TAMBAHAN'}
                                            </span>
                                            ${isReadonly ? '<span class="badge bg-success ms-2">APPROVED</span>' : ''}
                                            ${req.deskripsi ? `<br><small class="text-muted">${req.deskripsi}</small>` : ''}
                                        </label>
                                        <small class="form-text text-muted d-flex align-items-center gap-2">
                                            File saat ini:
                                            <div id="files-${questionId}-${req.id_persyaratan}" class="text-truncate" style="max-width: 250px;"></div>
                                        </small>
                                    </div>

                                    <div class="d-flex align-items-center mt-1">
                                        <input type="file" 
                                            class="form-control file-input"
                                            id="file-${key}"
                                            name="files[${questionId}][${req.id_persyaratan}]"
                                            data-question-id="${questionId}"
                                            data-requirement-id="${req.id_persyaratan}"
                                            accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.png"
                                            ${isRequired}
                                            ${readonlyAttr}
                                            ${disabledAttr} />

                                        <button type="button"
                                            class="btn btn-outline-primary ms-2 ${hasTemplate ? '' : 'd-none'}"
                                            id="btn_template_${key}"
                                            ${hasTemplate ? `onclick="window.open('${req.template_persyaratan}', '_blank')"` : ''}>
                                            Template
                                        </button>
                                    </div>
                                    <div class="alert alert-warning alert-dismissible alert-alt mt-1 show d-flex align-items-center gap-1 py-2 px-3">
                                        <strong class="me-1">Catatan revisi:</strong>
                                        <div id="catatan-${questionId}-${req.id_persyaratan}" class="flex-grow-1"></div>
                                    </div>
                                </div>`;
                }).join('');
            },

            updateFilePreview(questionId, requirementId) {
                const key = `${questionId}-${requirementId}`;
                const container = $(`#files-${questionId}-${requirementId}`);
                container.empty();

                if (AppState.uploadedFiles[key]?.length > 0) {
                    const currentFile = AppState.uploadedFiles[key].find(file => file.is_current);

                    if (currentFile) {
                        container.append(`
                                <span class="text-success">
                                    <i class="las la-check-circle me-1"></i>
                                    <a href="/uploads/${currentFile.path}" target="_blank" class="text-success">
                                        ${currentFile.name}
                                    </a>
                                    <small class="text-muted">(v${currentFile.version})</small>
                                </span>
                            `);
                    } else {
                        container.html('<span class="text-muted">Belum ada file</span>');
                    }
                } else {
                    container.html('<span class="text-muted">Belum ada file</span>');
                }
            },

            updateCatatanRevisi(questionId, requirementId) {
                const key = `${questionId}-${requirementId}`;
                const container = $(`#catatan-${questionId}-${requirementId}`);
                container.empty();

                const fileList = AppState.uploadedFiles[key];
                if (fileList?.length > 0) {
                    const currentFile = fileList.find(file => file.is_current);
                    if (currentFile?.catatan_revisi?.trim()) {
                        container.html(`
                            <span class="text-warning">
                                <i class="las la-exclamation-circle me-1"></i>
                                ${currentFile.catatan_revisi}
                            </span>
                        `);
                        return;
                    }
                }

                container.html('<span class="text-muted">Tidak ada catatan</span>');
            }
        };

        // Form handling functions
        const FormHandler = {
            checkAndSetRadioFromFiles(questionId) {
                const question = AppState.questionsData.find(q => q.id_pertanyaan == questionId);
                if (!question?.persyaratan) return;

                let hasFilesForAllRequired = true;
                let hasAnyFile = false;

                question.persyaratan.forEach(req => {
                    const key = `${questionId}-${req.id_persyaratan}`;
                    const fileInput = $(`#file-${key}`)[0];
                    const hasNewFile = fileInput && fileInput.files.length > 0;
                    const hasExistingFile = AppState.uploadedFiles[key]?.length > 0;
                    const hasFile = hasExistingFile || hasNewFile;

                    if (req.tipe === 'wajib' && !hasFile) {
                        hasFilesForAllRequired = false;
                    }

                    if (hasFile) {
                        hasAnyFile = true;
                    }
                });

                if (hasFilesForAllRequired) {
                    $(`input[name="jawaban[${questionId}]"][value="sudah"]`).prop('checked', true);
                } else {
                    $(`input[name="jawaban[${questionId}]"][value="belum"]`).prop('checked', true);
                }
            },

            prepareFormData(status) {
                const formData = new FormData();

                // Basic form data
                formData.append('_method', 'PUT');
                formData.append('laporan_id', AppState.currentLaporanId);
                formData.append('desa_id', $('#desa_id').val());
                formData.append('kegiatan_id', $('#kegiatan_id').val());
                formData.append('tahun', $('#tahun').val());
                formData.append('bulan', $('#bulan').val());
                formData.append('status', status);
                formData.append('_token', '{{ csrf_token() }}');

                // Jawaban status (radio buttons)
                $('.answer-radio:checked').each(function () {
                    const name = $(this).attr('name');
                    const value = $(this).val();
                    const questionId = name.match(/\[(\d+)\]/)[1];
                    formData.append(`jawaban[${questionId}]`, value);
                });

                // Files - hanya tambahkan file yang benar-benar dipilih
                let fileCount = 0;
                $('.file-input').each(function () {
                    const questionId = $(this).data('question-id');
                    const requirementId = $(this).data('requirement-id');
                    const file = this.files[0];

                    // Skip jika dokumen sudah approved
                    if (Utils.isDokumenReadonly(questionId, requirementId)) {
                        console.log('⏭️ Skipping approved dokumen:', {
                            questionId,
                            requirementId
                        });
                        return;
                    }

                    if (file?.size > 0) {
                        formData.append(`files[${questionId}][${requirementId}]`, file);
                        fileCount++;
                        console.log('📤 File added to FormData:', {
                            questionId,
                            requirementId,
                            fileName: file.name,
                            fileSize: file.size
                        });
                    }
                });

                console.log(`📊 Total files being sent: ${fileCount}`);

                // Existing files data untuk tracking
                formData.append('existing_files', JSON.stringify(AppState.uploadedFiles));

                return formData;
            },

            async submitForm(status) {
                const formData = this.prepareFormData(status);
                const url = CONFIG.ROUTES.UPDATE_LAPORAN.replace(':id', AppState.currentLaporanId);

                Utils.setButtonsLoading(true);

                try {
                    const response = await $.ajax({
                        url: url,
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false
                    });

                    if (response.status === 200) {
                        if (status === 'submitted') {
                            Utils.showSuccessAlert('Laporan berhasil disubmit!', function () {
                                window.location.href = CONFIG.ROUTES.LAPORAN_INDEX;
                            });
                        } else {
                            Utils.showSuccessAlert('Draft berhasil disimpan!', function () {
                                location.reload();
                            });
                        }
                    } else {
                        Utils.showErrorAlert('Gagal menyimpan laporan: ' + (response.message || ''));
                        Utils.setButtonsLoading(false);
                    }
                } catch (error) {
                    let errorMessage = 'Gagal menyimpan laporan';
                    if (error.responseJSON?.message) {
                        errorMessage = error.responseJSON.message;
                    }
                    Utils.showErrorAlert(errorMessage);
                    Utils.setButtonsLoading(false);
                }
            }
        };

        // Progress tracking
        const ProgressTracker = {
            updateProgress() {
                let completed = 0;
                let total = 0;

                AppState.questionsData.forEach(question => {
                    if (question.persyaratan) {
                        question.persyaratan.forEach(req => {
                            total += 1;
                            const key = `${question.id_pertanyaan}-${req.id_persyaratan}`;

                            // Skip jika dokumen sudah approved
                            if (Utils.isDokumenReadonly(question.id_pertanyaan, req.id_persyaratan)) {
                                completed += 1; // Consider approved documents as completed
                                return;
                            }

                            // Check if file exists (either existing or newly selected)
                            const fileInput = $(`#file-${key}`)[0];
                            const hasNewFile = fileInput && fileInput.files.length > 0;
                            const hasExistingFile = AppState.uploadedFiles[key]?.length > 0;

                            if (hasExistingFile || hasNewFile) {
                                completed += 1;
                            }
                        });
                    }
                });

                const percentage = total > 0 ? Math.round((completed / total) * 100) : 0;
                DOM.elements.formProgress.css('width', `${percentage}%`);
                DOM.elements.progressText.text(`${percentage}% selesai`);

                // Submit hanya bisa dilakukan jika semua file WAJIB terpenuhi
                const allRequiredFilled = this.checkAllRequiredFiles();
                DOM.elements.submitLaporanBtn.prop('disabled', !allRequiredFilled);

                if (percentage < 100) {
                    DOM.elements.progressText.addClass('text-warning').removeClass('text-success');
                } else {
                    DOM.elements.progressText.addClass('text-success').removeClass('text-warning');
                }
            },

            checkAllRequiredFiles() {
                let allRequiredFilled = true;

                AppState.questionsData.forEach(question => {
                    if (question.persyaratan) {
                        question.persyaratan.forEach(req => {
                            if (req.tipe === 'wajib') {
                                const key = `${question.id_pertanyaan}-${req.id_persyaratan}`;

                                // Skip jika dokumen sudah approved
                                if (Utils.isDokumenReadonly(question.id_pertanyaan, req
                                    .id_persyaratan)) {
                                    return;
                                }

                                const fileInput = $(`#file-${key}`)[0];
                                const hasNewFile = fileInput && fileInput.files.length > 0;
                                const hasExistingFile = AppState.uploadedFiles[key]?.length > 0;

                                if (!hasExistingFile && !hasNewFile) {
                                    allRequiredFilled = false;
                                }
                            }
                        });
                    }
                });

                return allRequiredFilled;
            }
        };

        // Event handlers
        const EventHandlers = {
            initialize() {
                // Save draft buttons
                DOM.elements.saveDraftBtn.on('click', () => {
                    DOM.elements.statusDraft.prop('checked', true);
                    FormHandler.submitForm('draft');
                });

                // Submit buttons
                DOM.elements.submitLaporanBtn.on('click', () => {
                    DOM.elements.statusSubmit.prop('checked', true);
                    FormHandler.submitForm('submitted');
                });

                // File input change handler
                $(document).on('change', '.file-input', function () {
                    const questionId = $(this).data('question-id');
                    FormHandler.checkAndSetRadioFromFiles(questionId);
                    ProgressTracker.updateProgress();
                });

                // Answer radio change handler
                $(document).on('change', '.answer-radio', function () {
                    ProgressTracker.updateProgress();
                });
            }
        };

        // Main initialization
        $(document).ready(function () {
            DOM.initialize();
            EventHandlers.initialize();
            DataLoader.initializeForm();
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