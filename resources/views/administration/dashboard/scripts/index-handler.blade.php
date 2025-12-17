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
     *  DASHBOARD HANDLER
     * ------------------------------------------------- */
    class DashboardHandler {
        constructor() {
            this.role = 'admin';
            this.initialize();
        }

        initialize() {
            this.bindEvents();
            this.loadDashboardData();
            this.loadTableData();
        }

        bindEvents() {
            // Bind filter changes
            $('#filter_tahun, #filter_bulan, #filter_jenis, #filter_kecamatan').on('change', () => {
                this.loadDashboardData();
                this.loadTableData();
            });
        }

        collectFilters() {
            return {
                tahun: $('#filter_tahun option:selected').data('tahun'),
                bulan: $('#filter_bulan').val(),
                jenis_kegiatan_id: $('#filter_jenis').val(),
                kecamatan_id: $('#filter_kecamatan').val()
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
            const summary = data.summary;
            const metrics = {
                'total_kecamatan': summary.total_kecamatan,
                'total_desa': summary.total_desa,
                'total_kegiatan': summary.total_kegiatan,
                'total_laporan_approved': summary.total_laporan_approved,
                'total_laporan_revisi': summary.total_laporan_revisi,
                'total_laporan_pending': summary.total_laporan_pending
            };

            for (const [id, value] of Object.entries(metrics)) {
                const $el = $(`#${id}`);
                if ($el.length) $el.text(value || 0);
            }
        }

        loadTableData() {
            const filters = this.collectFilters();

            // Load butuh approval table
            this.loadTable('{{ route('administrator.dashboard.recent-laporan', ['type' => 'pending']) }}', filters,
                '#table-butuh-approval', '#count_butuh_approval');

            // Load direvisi table
            this.loadTable('{{ route('administrator.dashboard.recent-laporan', ['type' => 'revision']) }}', filters,
                '#table-direvisi', '#count_direvisi');
        }

        loadTable(url, filters, tableSelector, countSelector) {
            const fullUrl = url + '?' + $.param(filters);

            $.ajax({
                url: fullUrl,
                method: 'GET',
                success: (response) => {
                    if (response.success) {
                        this.renderTable(response.data, tableSelector, countSelector);
                    }
                }
            });
        }

        renderTable(data, tableSelector, countSelector) {
            const $tbody = $(`${tableSelector} tbody`);
            const $count = $(countSelector);

            if (data.length === 0) {
                $tbody.html(`
                <tr>
                    <td colspan="4" class="text-center text-muted">
                        Tidak ada data yang ditemukan
                    </td>
                </tr>
            `);
                $count.text('0');
                return;
            }

            const rows = data.map((item, index) => `
            <tr>
                <td>${index + 1}</td>
                <td>
                    <strong>${item.nama_desa}</strong><br>
                    <small>${item.nama_kecamatan}</small>
                </td>
                <td>
                    ${item.nama_kegiatan}<br>
                    <small class="text-muted">${item.nama_jenis}</small>
                </td>
                <td>${item.tanggal_submit ? new Date(item.tanggal_submit).toLocaleDateString('id-ID') : '-'}</td>
            </tr>
        `).join('');

            $tbody.html(rows);
            $count.text(data.length);
        }
    }

    /* -------------------------------------------------
     *  INISIALISASI DASHBOARD SEKALI
     * ------------------------------------------------- */
    document.addEventListener('DOMContentLoaded', () => {
        window.dashboard = new DashboardHandler();
    });

    /* -------------------------------------------------
     *  RESET FILTER
     * ------------------------------------------------- */
    $('#reset_filter').on('click', function() {
        // Reset semua select
        $('#filter_tahun, #filter_bulan, #filter_jenis, #filter_kecamatan')
            .val('')
            .selectpicker('refresh');

        // Reload data dashboard dan tabel
        if (window.dashboard) {
            window.dashboard.loadDashboardData();
            window.dashboard.loadTableData();
        }
    });
</script>
