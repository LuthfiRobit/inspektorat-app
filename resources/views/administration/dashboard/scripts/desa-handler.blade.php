<script>
    /* -------------------------------------------------
     *  DEPENDENT DROPDOWN (Tahun → Jenis)
     * ------------------------------------------------- */
    DropdownHelper.bindDependentDropdown(
        '#filter_tahun',
        '#filter_jenis',
        '{{ route('administrator.master.jenis-kegiatan.list-by-tahun', ':id') }}',
        (item, selectedValue) => {
            const selected = selectedValue == item.id_jenis_kegiatan ? 'selected' : '';
            return `<option value="${item.id_jenis_kegiatan}" ${selected}>
                    ${item.kode_jenis} - ${item.nama_jenis}
                </option>`;
        }
    );

    /* -------------------------------------------------
     *  DASHBOARD HANDLER UNTUK DESA
     * ------------------------------------------------- */
    class DashboardDesaHandler {
        constructor() {
            this.role = 'desa';
            this.initialize();
        }

        initialize() {
            this.bindEvents();
            this.loadDashboardData();
        }

        bindEvents() {
            // Bind filter changes
            $('#filter_tahun, #filter_bulan, #filter_jenis').on('change', () => {
                this.loadDashboardData();
            });
        }

        collectFilters() {
            return {
                tahun: $('#filter_tahun option:selected').data('tahun'),
                bulan: $('#filter_bulan').val(),
                jenis_kegiatan_id: $('#filter_jenis').val()
            };
        }

        loadDashboardData() {
            const filters = this.collectFilters();
            const url = '{{ route('administrator.dashboard.summary') }}?' + $.param(filters);

            showLoading();

            $.ajax({
                url: url,
                method: 'GET',
                success: (response) => {
                    hideLoading();
                    if (response.success) {
                        this.renderDashboard(response.data);
                    }
                },
                error: (xhr) => {
                    hideLoading();
                    ResponseHandler.handleHttpError(xhr);
                }
            });
        }

        renderDashboard(data) {
            const summary = data.summary || {};
            
            // Mapping untuk kartu statistik khusus desa
            const metrics = {
                'total_kegiatan': summary.total_kegiatan || 0,
                'total_laporan': summary.total_laporan || 0,
                'total_laporan_draft': summary.total_laporan_draft || 0,
                'laporan_disetujui': summary.laporan_disetujui || summary.total_laporan_approved || 0,
                'laporan_revisi': summary.laporan_revisi || summary.total_laporan_revisi || 0,
                'laporan_pending': summary.laporan_pending || summary.total_laporan_pending || 0
            };

            // Update semua elemen
            for (const [id, value] of Object.entries(metrics)) {
                const $el = $(`#${id}`);
                if ($el.length) $el.text(value);
            }
        }
    }

    /* -------------------------------------------------
     *  INISIALISASI DASHBOARD DESA
     * ------------------------------------------------- */
    document.addEventListener('DOMContentLoaded', () => {
        window.dashboardDesa = new DashboardDesaHandler();
    });

    /* -------------------------------------------------
     *  RESET FILTER
     * ------------------------------------------------- */
    $('#reset_filter').on('click', function() {
        // Reset semua select
        $('#filter_tahun, #filter_bulan, #filter_jenis')
            .val('')
            .selectpicker('refresh');

        // Reload data dashboard
        if (window.dashboardDesa) {
            window.dashboardDesa.loadDashboardData();
        }
    });

    /* -------------------------------------------------
     *  INITIALIZE TOOLTIPS
     * ------------------------------------------------- */
    $(function() {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>