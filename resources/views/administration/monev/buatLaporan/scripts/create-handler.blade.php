@section('this-page-scripts')
    <script>
        // Global variables
        let currentLaporanId = {{ $id_laporan ?? 'null' }};
        let kegiatanData = null;
        let desaData = null;
        let questionsData = [];
        let uploadedFiles = {};

        $(document).ready(function() {
            initializeForm();
        });

        function initializeForm() {
            const urlParams = new URLSearchParams(window.location.search);
            const desaId = urlParams.get('desa_id');
            const kegiatanId = urlParams.get('kegiatan_id');

            if (desaId && kegiatanId) {
                loadKegiatanData(desaId, kegiatanId, function() {
                    if (currentLaporanId) {
                        loadExistingLaporan(currentLaporanId);
                    }
                });
            } else {
                showErrorAlert('Parameter tidak lengkap. Silakan pilih kegiatan dari halaman daftar.');
                $('#questionsContainer').html(`
                <div class="alert alert-danger">
                    <h6>Parameter Tidak Lengkap</h6>
                    <p>Silakan pilih kegiatan dari halaman daftar laporan.</p>
                    <a href="{{ route('administrator.monev.laporan.index') }}" class="btn btn-primary">
                        Kembali ke Daftar
                    </a>
                </div>
            `);
            }

            initializeEventHandlers();
        }

        function initializeEventHandlers() {
            // Save draft buttons
            $('#saveDraftBtn, #saveDraftBottom').on('click', function() {
                $('#statusDraft').prop('checked', true);
                saveLaporan();
            });

            // Submit buttons
            $('#submitLaporanBtn, #submitLaporanBottom').on('click', function() {
                $('#statusSubmit').prop('checked', true);
                saveLaporan();
            });

            // Radio button change handler
            $(document).on('change', '.answer-radio', function() {
                updateProgress();
            });

            // File input change handler
            $(document).on('change', '.file-input', function() {
                const questionId = $(this).data('question-id');
                checkAndSetRadioFromFiles(questionId);
                updateProgress();
            });
        }

        function loadExistingLaporan(laporanId) {
            const url = `{{ route('administrator.monev.laporan.buat-laporan.get-data', ':id') }}`.replace(':id', laporanId);

            AjaxHandler.sendGetRequest(url, function(response) {
                if (response && response.status === 200 && response.data) {
                    populateFormData(response.data);
                } else {
                    showErrorAlert('Gagal memuat data laporan: ' + (response ? response.message :
                        'Response tidak valid'));
                }
            });
        }

        function loadKegiatanData(desaId, kegiatanId, callback = null) {
            const url = "{{ route('administrator.monev.laporan.buat-laporan.get-kegiatan-data') }}" +
                `?desa_id=${desaId}&kegiatan_id=${kegiatanId}`;

            $.ajax({
                url: url,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 200 && response.data) {
                        kegiatanData = response.data.kegiatan;
                        desaData = response.data.desa;
                        questionsData = response.data.pertanyaan || [];

                        populateKegiatanInfo();
                        renderQuestions();
                        updateProgress();

                        showSuccessAlert('Data kegiatan berhasil dimuat');

                        if (typeof callback === 'function') {
                            callback();
                        }
                    } else {
                        showErrorAlert('Gagal memuat data kegiatan: ' + (response.message || 'Unknown error'));
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Terjadi kesalahan saat memuat data kegiatan';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    showErrorAlert(errorMessage);
                }
            });
        }

        function populateFormData(data) {
            if (data.laporan) {
                const laporan = data.laporan;

                $('#desa_id').val(laporan.desa_id);
                $('#kegiatan_id').val(laporan.kegiatan_id);
                $('#tahun').val(laporan.tahun);
                $('#bulan').val(laporan.bulan);

                // Populate jawaban status (radio buttons) dari database
                if (data.jawaban && data.jawaban.length > 0) {
                    data.jawaban.forEach(jawaban => {
                        // Set radio button berdasarkan status dari database
                        $(`input[name="jawaban[${jawaban.pertanyaan_id}]"][value="${jawaban.status}"]`).prop(
                            'checked', true);
                    });
                }

                // Populate dokumen
                if (data.dokumen && data.dokumen.length > 0) {
                    const jawabanToPertanyaanMap = {};
                    if (data.jawaban && data.jawaban.length > 0) {
                        data.jawaban.forEach(jawaban => {
                            jawabanToPertanyaanMap[jawaban.id_jawaban] = jawaban.pertanyaan_id;
                        });
                    }

                    data.dokumen.forEach(dokumen => {
                        const pertanyaanId = jawabanToPertanyaanMap[dokumen.jawaban_id];
                        if (pertanyaanId) {
                            const key = `${pertanyaanId}-${dokumen.persyaratan_id}`;
                            if (!uploadedFiles[key]) uploadedFiles[key] = [];

                            const fileExists = uploadedFiles[key].some(file => file.id === dokumen.id_dokumen);
                            if (!fileExists) {
                                uploadedFiles[key].push({
                                    id: dokumen.id_dokumen,
                                    name: dokumen.nama_file,
                                    size: dokumen.ukuran_file ? formatFileSize(dokumen.ukuran_file) :
                                        'Unknown',
                                    path: dokumen.path_file
                                });
                            }

                            updateFilePreview(pertanyaanId, dokumen.persyaratan_id);
                        }
                    });

                    // Setelah semua file dimuat, cek dan set radio button berdasarkan file
                    questionsData.forEach(question => {
                        checkAndSetRadioFromFiles(question.id_pertanyaan);
                    });
                }

                updateProgress();
            }
        }

        function checkAndSetRadioFromFiles(questionId) {
            const question = questionsData.find(q => q.id_pertanyaan == questionId);
            if (!question || !question.persyaratan) return;

            let hasFilesForAllRequired = true;
            let hasAnyFile = false;

            // Cek apakah semua persyaratan wajib memiliki file
            question.persyaratan.forEach(req => {
                const key = `${questionId}-${req.id_persyaratan}`;
                const fileInput = $(`#file-${key}`)[0];
                const hasNewFile = fileInput && fileInput.files.length > 0;
                const hasExistingFile = uploadedFiles[key] && uploadedFiles[key].length > 0;
                const hasFile = hasExistingFile || hasNewFile;

                if (req.tipe === 'wajib' && !hasFile) {
                    hasFilesForAllRequired = false;
                }

                if (hasFile) {
                    hasAnyFile = true;
                }
            });

            // Jika ada file untuk semua persyaratan wajib, set radio ke "sudah"
            if (hasFilesForAllRequired) {
                $(`input[name="jawaban[${questionId}]"][value="sudah"]`).prop('checked', true);
            } else if (hasAnyFile) {
                // Jika ada file tapi tidak semua wajib terpenuhi, tetap set ke "sudah"
                $(`input[name="jawaban[${questionId}]"][value="sudah"]`).prop('checked', true);
            }
            // Jika tidak ada file sama sekali, biarkan radio sesuai pilihan user atau default
        }

        function populateKegiatanInfo() {
            if (!kegiatanData || !desaData) return;

            const urlParams = new URLSearchParams(window.location.search);
            const desaId = urlParams.get('desa_id');

            $('#info_desa').text(`Desa : ${desaData.nama_desa}`);
            $('#info_kegiatan').text(kegiatanData.nama_kegiatan || '-');
            $('#info_periode').text(`${kegiatanData.tahun || '-'} - ${kegiatanData.nama_bulan || '-'}`);
            $('#info_batas_upload').text(kegiatanData.batas_akhir_upload ? `Tgl. ${kegiatanData.batas_akhir_upload}` : '-');
            $('#info_dasar_hukum').text(kegiatanData.dasar_hukum || 'Tidak ada dasar hukum');

            $('#desa_id').val(desaId);
            $('#kegiatan_id').val(kegiatanData.id_kegiatan);
            $('#tahun').val(kegiatanData.tahun);
            $('#bulan').val(kegiatanData.bulan);
        }

        function renderQuestions() {
            if (!questionsData || questionsData.length === 0) {
                $('#questionsContainer').html(`
                <div class="alert alert-warning">
                    <h6>Tidak Ada Pertanyaan</h6>
                    <p>Belum ada pertanyaan yang ditetapkan untuk kegiatan ini.</p>
                </div>
            `);
                return;
            }

            let questionsHtml = '';

            questionsData.forEach((question, index) => {
                // Default status untuk pertanyaan baru
                const sudahChecked = '';
                const belumChecked = 'checked';

                const questionHtml = `
                <div class="question-section mb-4" data-question-id="${question.id_pertanyaan}">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                        <div class="flex-grow-1">
                            <h6 class="mb-0">
                                <span class="badge bg-primary me-2">${index + 1}</span>
                                ${question.pertanyaan}
                            </h6>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="form-check mb-0">
                                <input class="form-check-input answer-radio" type="radio"
                                    name="jawaban[${question.id_pertanyaan}]"
                                    id="jawaban-sudah-${question.id_pertanyaan}"
                                    value="sudah"
                                    ${sudahChecked}>
                                <label class="form-check-label" for="jawaban-sudah-${question.id_pertanyaan}">
                                    Sudah
                                </label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input answer-radio" type="radio"
                                    name="jawaban[${question.id_pertanyaan}]"
                                    id="jawaban-belum-${question.id_pertanyaan}"
                                    value="belum"
                                    ${belumChecked}>
                                <label class="form-check-label" for="jawaban-belum-${question.id_pertanyaan}">
                                    Belum
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="requirements-section mt-3">
                        <label class="form-label mb-2">Dokumen Persyaratan:</label>
                        ${renderRequirements(question.persyaratan, question.id_pertanyaan)}
                    </div>
                </div>
            `;

                questionsHtml += questionHtml;
            });

            $('#questionsContainer').html(questionsHtml);
        }

        function renderRequirements(requirements, questionId) {
            if (!requirements || requirements.length === 0) {
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
                        <small class="form-text text-muted d-flex align-items-center gap-2">
                            File saat ini:
                            <div id="files-${questionId}-${req.id_persyaratan}" class="text-truncate" style="max-width: 250px;"></div>
                        </small>
                    </div>

                    <div class="d-flex align-items-center mt-1">
                        <input type="file" 
                            class="form-control file-input"
                            id="file-${key}"
                            name="file-${key}"
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.png"
                            data-question-id="${questionId}"
                            data-requirement-id="${req.id_persyaratan}"
                            ${isRequired} />

                        <button type="button"
                            class="btn btn-outline-primary ms-2 ${hasTemplate ? '' : 'd-none'}"
                            id="btn_template_${key}"
                            ${hasTemplate ? `onclick="window.open('${req.template_persyaratan}', '_blank')"` : ''}>
                            Template
                        </button>
                    </div>
                </div>
            `;
            }).join('');
        }

        function updateFilePreview(questionId, requirementId) {
            const key = `${questionId}-${requirementId}`;
            const container = $(`#files-${questionId}-${requirementId}`);
            container.empty();

            if (uploadedFiles[key] && uploadedFiles[key].length > 0) {
                uploadedFiles[key].forEach(file => {
                    container.append(`
                    <span class="text-success">
                        <i class="las la-check-circle me-1"></i>${file.name}
                    </span>
                `);
                });
            } else {
                container.html('<span class="text-muted">Belum ada file</span>');
            }
        }

        function saveLaporan() {
            const formData = new FormData();
            const status = $('input[name="status"]:checked').val();

            // Basic form data
            formData.append('laporan_id', currentLaporanId);
            formData.append('desa_id', $('#desa_id').val());
            formData.append('kegiatan_id', $('#kegiatan_id').val());
            formData.append('tahun', $('#tahun').val());
            formData.append('bulan', $('#bulan').val());
            formData.append('status', status);
            formData.append('catatan_laporan', $('#catatan_laporan').val());
            formData.append('_token', '{{ csrf_token() }}');

            // Jawaban status (radio buttons)
            $('.answer-radio:checked').each(function() {
                const name = $(this).attr('name');
                const value = $(this).val();
                const questionId = name.match(/\[(\d+)\]/)[1];
                formData.append(`jawaban[${questionId}]`, value);
            });

            // File inputs
            $('.file-input').each(function() {
                const file = this.files[0];
                const questionId = $(this).data('question-id');
                const requirementId = $(this).data('requirement-id');

                if (file) {
                    formData.append(`files[${questionId}][${requirementId}]`, file);
                }
            });

            // Uploaded files data (untuk file yang sudah ada)
            const filesData = {};
            for (let key in uploadedFiles) {
                const [questionId, requirementId] = key.split('-');
                if (!filesData[questionId]) filesData[questionId] = {};
                filesData[questionId][requirementId] = uploadedFiles[key].map(file => file.id);
            }
            formData.append('uploaded_files', JSON.stringify(filesData));

            const url = `{{ route('administrator.monev.laporan.buat-laporan.store') }}`;

            setButtonsLoading(true);

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status === 200) {
                        currentLaporanId = response.data.laporan_id;

                        if (status === 'submitted') {
                            showSuccessAlert('Laporan berhasil disubmit!', function() {
                                setTimeout(() => {
                                    window.location.href =
                                        `{{ route('administrator.monev.laporan.index') }}`;
                                }, 2000);
                            });
                        } else {
                            showSuccessAlert('Draft berhasil disimpan!');
                            setButtonsLoading(false);
                        }
                    } else {
                        showErrorAlert('Gagal menyimpan laporan: ' + (response.message || ''));
                        setButtonsLoading(false);
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Gagal menyimpan laporan';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    showErrorAlert(errorMessage);
                    setButtonsLoading(false);
                }
            });
        }

        function updateProgress() {
            let completed = 0;
            let total = 0;

            questionsData.forEach(question => {
                total += 1;

                // Check radio button status
                const status = $(`input[name="jawaban[${question.id_pertanyaan}]"]:checked`).val();
                if (status === 'sudah') {
                    completed += 0.5;
                }

                // Check requirements
                if (question.persyaratan) {
                    question.persyaratan.forEach(req => {
                        total += 0.5;
                        const key = `${question.id_pertanyaan}-${req.id_persyaratan}`;

                        // Check if file exists (either already uploaded or newly selected)
                        const fileInput = $(`#file-${key}`)[0];
                        const hasNewFile = fileInput && fileInput.files.length > 0;
                        const hasExistingFile = uploadedFiles[key] && uploadedFiles[key].length > 0;

                        if (hasExistingFile || hasNewFile) {
                            completed += 0.5;
                        }
                    });
                }
            });

            const percentage = total > 0 ? Math.round((completed / total) * 100) : 0;
            $('#formProgress').css('width', `${percentage}%`);
            $('#progressText').text(`${percentage}% selesai`);

            const canSubmit = percentage >= 80;
            $('#submitLaporanBtn, #submitLaporanBottom').prop('disabled', !canSubmit);

            if (percentage < 50) {
                $('#progressText').addClass('text-warning').removeClass('text-success');
            } else {
                $('#progressText').addClass('text-success').removeClass('text-warning');
            }
        }

        // Utility functions
        function formatFileSize(bytes) {
            if (!bytes || bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function setButtonsLoading(loading) {
            const buttons = $('.btn');
            buttons.prop('disabled', loading);

            if (loading) {
                $('#saveDraftBtn, #saveDraftBottom').html(
                    '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');
                $('#submitLaporanBtn, #submitLaporanBottom').html(
                    '<span class="spinner-border spinner-border-sm me-1"></span>Mengirim...');
            } else {
                $('#saveDraftBtn, #saveDraftBottom').html('<i class="las la-save me-1"></i>Simpan Draft');
                $('#submitLaporanBtn, #submitLaporanBottom').html('<i class="las la-paper-plane me-1"></i>Submit Laporan');
            }
        }

        function showSuccessAlert(message, callback = null) {
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
        }

        function showErrorAlert(message) {
            const alertHtml = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="las la-exclamation-circle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
            $('#formAlerts').html(alertHtml);
        }
    </script>
@endsection
