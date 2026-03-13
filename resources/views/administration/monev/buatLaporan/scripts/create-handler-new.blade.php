@section('this-page-scripts')
    <script>
        // Configuration constants
        const CONFIG = {
            ROUTES: {
                GET_KEGIATAN_DATA: '{{ route('administrator.monev.laporan.get-kegiatan-data') }}',
                STORE_LAPORAN: '{{ route('administrator.monev.laporan.store') }}',
                LAPORAN_INDEX: '{{ route('administrator.monev.laporan.index') }}',
                LAPORAN_EDIT: '{{ route('administrator.monev.laporan.edit') }}'
            },
            STATUS_DISPLAY: {
                'draft': 'Draft',
                'submitted': 'Terkirim'
            }
        };

        // Application state
        const AppState = {
            kegiatanData: null,
            desaData: null,
            questionsData: [],
            uploadedFiles: {} // Untuk tracking file yang sudah diupload
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
                    </div>
                `;
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
                    ButtonStateManager.updateButtonStates();
                }
            },

            initializeTooltips() {
                // Initialize Bootstrap tooltips
                $('[data-bs-toggle="tooltip"]').tooltip();
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
            }
        };

        // Data loading functions
        const DataLoader = {
            async initializeForm() {
                const urlParams = new URLSearchParams(window.location.search);
                const desaId = urlParams.get('desa_id');
                const kegiatanId = urlParams.get('kegiatan_id');
                const bulan = urlParams.get('bulan'); // Capture bulan
                const tahun = urlParams.get('tahun'); // Capture tahun

                if (desaId && kegiatanId) {
                    await this.loadKegiatanData(desaId, kegiatanId, bulan, tahun);
                } else {
                    this.showParameterError();
                }
            },

            async loadKegiatanData(desaId, kegiatanId, bulan, tahun) {
                let url = `${CONFIG.ROUTES.GET_KEGIATAN_DATA}?desa_id=${desaId}&kegiatan_id=${kegiatanId}`;
                if (bulan) url += `&bulan=${bulan}`;
                if (tahun) url += `&tahun=${tahun}`;

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
                        AppState.context = response.data.context || {}; // Store Context

                        this.populateKegiatanInfo();
                        Renderer.renderQuestions();
                        ProgressTracker.updateProgress();
                        ButtonStateManager.updateButtonStates();

                        Utils.showSuccessAlert('Data kegiatan berhasil dimuat');
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

                const urlParams = new URLSearchParams(window.location.search);
                const desaId = urlParams.get('desa_id');
                const kegiatan = AppState.kegiatanData;
                const desa = AppState.desaData;
                const context = AppState.context || {};

                // 1. Info Header
                DOM.elements.detailDesa.text(desa.nama_desa || '-');
                DOM.elements.detailKecamatan.text(desa.nama_kecamatan || '-');

                // 2. Info Kegiatan
                DOM.elements.detailNamaKegiatan.text(kegiatan.nama_kegiatan || '-');
                DOM.elements.detailTahun.text(context.tahun || kegiatan.tahun_anggaran || '-');
                DOM.elements.detailKodeKegiatan.text(kegiatan.kode_kegiatan || '-');
                DOM.elements.detailJenisKegiatan.text(kegiatan.nama_jenis || '-'); // From model join

                // Logic Display Periode
                let periodeText = kegiatan.bulan_master || 'N/A';
                if (context.bulan_nama) {
                    periodeText = context.bulan_nama;
                }
                if (kegiatan.frekuensi_pelaporan) {
                    periodeText += ` (Rutin: Setiap ${kegiatan.frekuensi_pelaporan} Bulan)`;
                }
                DOM.elements.detailBulan.text(periodeText);
                DOM.elements.detailDasarHukum.text(kegiatan.dasar_hukum || 'Tidak ada dasar hukum');

                // 3. Timeline Logic (Reused)
                const tahun = context.tahun || kegiatan.tahun_anggaran;
                let startBulan, endBulan;

                if (kegiatan.frekuensi_pelaporan) {
                    // Rutin
                    startBulan = kegiatan.bulan_mulai;
                    endBulan = kegiatan.bulan_selesai;
                } else {
                    // Insidentil
                    // Prefer context month if available (from URL), else fallback to kegiatan master month
                    startBulan = context.bulan || kegiatan.bulan;
                    endBulan = context.bulan || kegiatan.bulan;
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

                // Hidden Fields
                $('#desa_id').val(desaId);
                $('#kegiatan_id').val(kegiatan.id_kegiatan);
                $('#tahun').val(tahun);
                $('#bulan').val(startBulan); // Use specific month for reporting
            },

            showParameterError() {
                Utils.showErrorAlert('Parameter tidak lengkap. Silakan pilih kegiatan dari halaman daftar.');
                DOM.elements.questionsContainer.html(`
                    <div class="alert alert-danger">
                        <h6>Parameter Tidak Lengkap</h6>
                        <p>Silakan pilih kegiatan dari halaman daftar laporan.</p>
                        <a href="{{ route('administrator.monev.laporan.index') }}" class="btn btn-primary">
                            Kembali ke Daftar
                        </a>
                    </div>
                `);
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
            },

            renderQuestion(question, index) {
                return `
                    <div class="question-section mb-4" data-question-id="${question.id_pertanyaan}">
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
                                        value="belum" checked>
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
                    </div>
                `;
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

                    return `
                    <div class="mb-3 position-relative requirement-item" data-requirement-id="${req.id_persyaratan}">
                        <div class="d-flex justify-content-between align-items-center">
                            <label for="file-${key}" class="form-label mb-0">
                                ${req.nama_persyaratan}
                                <span class="badge ${req.tipe === 'wajib' ? 'bg-danger' : 'bg-secondary'} ms-2">
                                    ${req.tipe === 'wajib' ? 'WAJIB' : 'TAMBAHAN'}
                                </span>
                                ${req.deskripsi ? `<br><small class="text-muted">${req.deskripsi}</small>` : ''}
                            </label>
                        </div>

                        <div class="d-flex align-items-center mt-1">
                            <input type="file" 
                                class="form-control file-input"
                                id="file-${key}"
                                name="files[${questionId}][${req.id_persyaratan}]"
                                accept=".pdf,.doc,.docx"
                                data-question-id="${questionId}"
                                data-requirement-id="${req.id_persyaratan}"
                                data-requirement-type="${req.tipe}"
                                ${isRequired} />

                            <button type="button"
                                class="btn btn-outline-primary ms-2 ${hasTemplate ? '' : 'd-none'}"
                                id="btn_template_${key}"
                                ${hasTemplate ? `onclick="window.open('/uploads/${req.template_persyaratan}', '_blank')"` : ''}>
                                Template
                            </button>
                        </div>
                        <div class="text-danger small mt-1 d-none file-error fw-bold" id="error-${key}"></div>
                        <small class="text-muted mt-1 d-block"><i class="las la-info-circle me-1"></i>Maks 2MB. Format: PDF, DOC, DOCX</small>
                    </div>
                `;
                }).join('');
            }
        };

        // Form handling functions
        const FormHandler = {
            updateRadioBasedOnFiles(questionId) {
                const question = AppState.questionsData.find(q => q.id_pertanyaan == questionId);
                if (!question?.persyaratan) return;

                let allRequiredFilled = true;

                // Cek apakah SEMUA persyaratan wajib memiliki file
                question.persyaratan.forEach(req => {
                    if (req.tipe === 'wajib') {
                        const key = `${questionId}-${req.id_persyaratan}`;
                        const fileInput = $(`#file-${key}`)[0];
                        const hasFile = fileInput && fileInput.files.length > 0;

                        if (!hasFile) {
                            allRequiredFilled = false;
                        }
                    }
                });

                // Set radio button ke "SUDAH" jika semua file wajib terpenuhi
                if (allRequiredFilled) {
                    $(`input[name="jawaban[${questionId}]"][value="sudah"]`).prop('checked', true);
                } else {
                    $(`input[name="jawaban[${questionId}]"][value="belum"]`).prop('checked', true);
                }
            },

            prepareFormData(status) {
                const formData = new FormData();

                // Basic form data
                formData.append('desa_id', $('#desa_id').val());
                formData.append('kegiatan_id', $('#kegiatan_id').val());
                formData.append('tahun', $('#tahun').val());
                formData.append('bulan', $('#bulan').val());
                formData.append('status', status);
                formData.append('catatan_laporan', DOM.elements.catatanLaporan.val());
                formData.append('_token', '{{ csrf_token() }}');

                // Jawaban status (radio buttons)
                $('.answer-radio:checked').each(function () {
                    const name = $(this).attr('name');
                    const value = $(this).val();
                    const questionId = name.match(/\[(\d+)\]/)[1];
                    formData.append(`jawaban[${questionId}]`, value);
                });

                // Append files
                let fileCount = 0;
                $('.file-input').each(function () {
                    const questionId = $(this).data('question-id');
                    const requirementId = $(this).data('requirement-id');
                    const hasFile = this.files.length > 0;

                    if (hasFile) {
                        const file = this.files[0];
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
                return formData;
            },

            async submitForm(status) {
                // Validasi sebelum submit
                if (status === 'submitted' && !ButtonStateManager.validateSubmitConditions()) {
                    Utils.showErrorAlert(
                        'Tidak bisa submit. Pastikan semua file wajib untuk pertanyaan yang dijawab "SUDAH" telah diupload.'
                    );
                    return;
                }

                if (status === 'draft' && !ButtonStateManager.validateDraftConditions()) {
                    Utils.showErrorAlert(
                        'Tidak bisa menyimpan draft. Pastikan minimal ada satu file yang diupload.');
                    return;
                }

                const formData = this.prepareFormData(status);
                Utils.setButtonsLoading(true);

                try {
                    const response = await $.ajax({
                        url: CONFIG.ROUTES.STORE_LAPORAN,
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false
                    });

                    if (response.status === 200) {
                        this.handleSuccessResponse(status, response.data);
                    } else {
                        Utils.showErrorAlert('Gagal menyimpan laporan: ' + (response.message || ''));
                        Utils.setButtonsLoading(false);
                    }
                } catch (error) {
                    let errorMessage = 'Gagal menyimpan laporan';
                    
                    // Bersihkan error sebelumnya
                    $('.file-input').removeClass('is-invalid');
                    $('.file-error').text('').addClass('d-none');

                    if (error.responseJSON?.errors) {
                        const errors = error.responseJSON.errors;
                        let firstErrorMsg = '';
                        Object.keys(errors).forEach(key => {
                            if (key.startsWith('files.')) {
                                const parts = key.split('.');
                                const qId = parts[1];
                                const rId = parts[2];
                                const inputId = `#file-${qId}-${rId}`;
                                const errorDivId = `#error-${qId}-${rId}`;
                                
                                $(inputId).addClass('is-invalid');
                                $(errorDivId).text(errors[key][0]).removeClass('d-none');
                                if(!firstErrorMsg) firstErrorMsg = errors[key][0];
                            } else if (!firstErrorMsg) {
                                firstErrorMsg = errors[key][0];
                            }
                        });
                        errorMessage = firstErrorMsg || 'Terdapat kesalahan validasi pada formulir Anda. Silakan periksa pesan error di bawah input file.';
                    } else if (error.responseJSON?.message) {
                        errorMessage = error.responseJSON.message;
                    }
                    
                    Utils.showErrorAlert(errorMessage);
                    Utils.setButtonsLoading(false);
                }
            },

            handleSuccessResponse(status, responseData) {
                if (status === 'submitted') {
                    Utils.showSuccessAlert('Laporan berhasil disubmit!', () => {
                        setTimeout(() => {
                            window.location.href = CONFIG.ROUTES.LAPORAN_INDEX;
                        }, 2000);
                    });
                } else {
                    Utils.showSuccessAlert('Draft berhasil disimpan!', () => {
                        setTimeout(() => {
                            // Redirect ke halaman edit setelah simpan draft
                            const desaId = $('#desa_id').val();
                            const kegiatanId = $('#kegiatan_id').val();
                            const laporanId = responseData.laporan_id;

                            const editUrl =
                                `${CONFIG.ROUTES.LAPORAN_EDIT}?desa_id=${desaId}&kegiatan_id=${kegiatanId}&id_laporan=${laporanId}`;
                            window.location.href = editUrl;
                        }, 2000);
                    });
                }
            }
        };

        // Button state management
        const ButtonStateManager = {
            validateDraftConditions() {
                // Cek apakah ada minimal satu file apapun (wajib atau tambahan) yang diinput
                let hasAnyFile = false;

                $('.file-input').each(function () {
                    if (this.files.length > 0) {
                        hasAnyFile = true;
                        return false; // break loop
                    }
                });

                return hasAnyFile;
            },

            validateSubmitConditions() {
                let allRequiredFilled = true;

                AppState.questionsData.forEach(question => {
                    if (Array.isArray(question.persyaratan)) {
                        question.persyaratan.forEach(req => {
                            if (req.tipe === 'wajib') {
                                const key = `${question.id_pertanyaan}-${req.id_persyaratan}`;
                                const fileInput = document.getElementById(`file-${key}`);
                                const hasFile = fileInput && fileInput.files && fileInput.files.length >
                                    0;

                                if (!hasFile) {
                                    allRequiredFilled = false;
                                }
                            }
                        });
                    }
                });

                return allRequiredFilled;
            },

            updateButtonStates() {
                const canSubmit = this.validateSubmitConditions();
                const canDraft = this.validateDraftConditions();

                console.log('Status Tombol:', {
                    canDraft,
                    canSubmit
                });

                // Update tombol draft
                DOM.elements.saveDraftBtn.prop('disabled', !canDraft);
                this.updateButtonAppearance(DOM.elements.saveDraftBtn, canDraft, 'btn-primary');

                // Update tombol submit
                DOM.elements.submitLaporanBtn.prop('disabled', !canSubmit);
                this.updateButtonAppearance(DOM.elements.submitLaporanBtn, canSubmit, 'btn-success');

                // Update tooltips
                this.updateButtonTooltips(canDraft, canSubmit);
            },

            updateButtonAppearance(button, isEnabled, baseClass) {
                if (!isEnabled) {
                    // button.addClass('btn-disabled').removeClass(baseClass);
                } else {
                    // button.removeClass('btn-disabled').addClass(baseClass);
                }
            },

            updateButtonTooltips(canDraft, canSubmit) {
                // Hapus tooltip sebelumnya
                DOM.elements.saveDraftBtn.add(DOM.elements.submitLaporanBtn).tooltip('dispose');

                // Tooltip untuk tombol draft yang disabled
                if (!canDraft) {
                    DOM.elements.saveDraftBtn
                        .attr('data-bs-toggle', 'tooltip')
                        .attr('data-bs-placement', 'top')
                        .attr('title', 'Simpan draft: Upload minimal satu file (wajib atau tambahan)')
                        .tooltip();
                }

                // Tooltip untuk tombol submit yang disabled
                if (!canSubmit) {
                    DOM.elements.submitLaporanBtn
                        .attr('data-bs-toggle', 'tooltip')
                        .attr('data-bs-placement', 'top')
                        .attr('title', 'Submit: Pastikan semua file wajib untuk pertanyaan "SUDAH" telah diupload')
                        .tooltip();
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

                            const fileInput = $(`#file-${key}`)[0];
                            const hasFile = fileInput && fileInput.files.length > 0;

                            if (hasFile) {
                                completed += 1;
                            }
                        });
                    }
                });

                const percentage = total > 0 ? Math.round((completed / total) * 100) : 0;
                DOM.elements.formProgress.css('width', `${percentage}%`);
                DOM.elements.progressText.text(`${percentage}% selesai`);

                // Update state tombol berdasarkan file
                ButtonStateManager.updateButtonStates();

                if (percentage < 100) {
                    DOM.elements.progressText.addClass('text-warning').removeClass('text-success');
                } else {
                    DOM.elements.progressText.addClass('text-success').removeClass('text-warning');
                }
            }
        };

        // Event handlers
        const EventHandlers = {
            initialize() {
                // Nonaktifkan tombol saat inisialisasi
                DOM.elements.saveDraftBtn.add(DOM.elements.submitLaporanBtn).prop('disabled', true);

                // Save draft buttons
                DOM.elements.saveDraftBtn.on('click', (e) => {
                    e.preventDefault();
                    DOM.elements.statusDraft.prop('checked', true);
                    FormHandler.submitForm('draft');
                });

                // Submit buttons
                DOM.elements.submitLaporanBtn.on('click', (e) => {
                    e.preventDefault();
                    DOM.elements.statusSubmit.prop('checked', true);
                    FormHandler.submitForm('submitted');
                });

                // File input change handler
                $(document).on('change', '.file-input', function () {
                    const questionId = $(this).data('question-id');
                    const reqId = $(this).data('requirement-id');
                    const fileInput = this;
                    const errorContainer = $(`#error-${questionId}-${reqId}`);
                    
                    $(this).removeClass('is-invalid');
                    errorContainer.text('').addClass('d-none');

                    if (fileInput.files.length > 0) {
                        const file = fileInput.files[0];
                        const fileSizeMB = file.size / 1024 / 1024;
                        const allowedExts = ['pdf', 'doc', 'docx'];
                        const fileExt = file.name.split('.').pop().toLowerCase();
                        
                        let errorMessage = '';
                        if (fileSizeMB > 2) {
                            errorMessage = 'Ukuran file lebih dari 2MB. Harap perkecil file Anda.';
                        } else if (!allowedExts.includes(fileExt)) {
                            errorMessage = 'Format file tidak valid. Dokumen harus berformat PDF, DOC, atau DOCX.';
                        }

                        if (errorMessage) {
                            $(this).addClass('is-invalid');
                            errorContainer.text(errorMessage).removeClass('d-none');
                            fileInput.value = ''; // Reset input value so they must pick again
                            Utils.showErrorAlert('Gagal menambahkan dokumen: ' + errorMessage);
                        }
                    }

                    FormHandler.updateRadioBasedOnFiles(questionId);
                    ProgressTracker.updateProgress();
                });

                // Answer radio change handler
                $(document).on('change', '.answer-radio', function () {
                    ProgressTracker.updateProgress();
                });

                // Initialize tooltips
                Utils.initializeTooltips();
            }
        };

        // Main initialization
        $(document).ready(function () {
            DOM.initialize();
            EventHandlers.initialize();
            DataLoader.initializeForm();
        });
    </script>

    <style>
        .btn-disabled {
            cursor: not-allowed;
            opacity: 0.6;
        }

        .tooltip {
            font-size: 0.875rem;
        }
    </style>
@endsection