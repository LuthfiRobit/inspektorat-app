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
     *  DASHBOARD HANDLER UNTUK KECAMATAN
     * ------------------------------------------------- */
    class DashboardKecamatanHandler {
        constructor() {
            this.role = 'kecamatan';
            this.initialize();
        }

        initialize() {
            this.bindEvents();
            this.loadDashboardData();
            this.loadTableData();
        }

        bindEvents() {
            // Bind filter changes
            $('#filter_tahun, #filter_bulan, #filter_jenis, #filter_desa').on('change', () => {
                this.loadDashboardData();
                this.loadTableData();
            });
        }

        collectFilters() {
            return {
                tahun: $('#filter_tahun option:selected').data('tahun'),
                bulan: $('#filter_bulan').val(),
                jenis_kegiatan_id: $('#filter_jenis').val(),
                desa_id: $('#filter_desa').val()
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

            // Mapping untuk kartu statistik khusus kecamatan
            const metrics = {
                'total_desa': summary.total_desa || 0,
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

        loadTableData() {
            const filters = this.collectFilters();

            // Load tabel "Kegiatan yang Harus Dilaporkan"
            this.loadKegiatanHarusDilaporkan(filters);

            // Load tabel "Laporan Perlu Revisi"
            this.loadTable(
                '{{ route('administrator.dashboard.recent-laporan', ['type' => 'revision']) }}',
                filters,
                '#table-laporan-revisi',
                '#count_laporan_perlu_revisi'
            );
        }

        // Metode khusus untuk "Kegiatan yang Harus Dilaporkan"
        loadKegiatanHarusDilaporkan(filters) {
            // Endpoint khusus untuk kegiatan yang harus dilaporkan
            // Catatan: Anda perlu membuat endpoint ini di controller
            const url = '{{ route('administrator.dashboard.upcomming-kegiatan') }}?' + $.param(filters);

            $.ajax({
                url: url,
                method: 'GET',
                success: (response) => {
                    if (response.success) {
                        this.renderKegiatanHarusDilaporkan(response.data, '#table-kegiatan-dilaporkan',
                            '#count_kegiatan_harus_dilaporkan');
                    }
                },
                error: () => {
                    // Fallback jika endpoint belum tersedia
                    this.renderKegiatanHarusDilaporkan([], '#table-kegiatan-dilaporkan',
                        '#count_kegiatan_harus_dilaporkan');
                }
            });
        }

        renderKegiatanHarusDilaporkan(data, tableSelector, countSelector) {
            const $tbody = $(`${tableSelector} tbody`);
            const $count = $(countSelector);

            if (!data || data.length === 0) {
                $tbody.html(`
                    <tr>
                        <td colspan="4" class="text-center text-muted">
                            Belum ada kegiatan yang perlu dilaporkan
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
                        ${item.bulan ? this.getNamaBulan(item.bulan) : '-'} ${item.tahun || ''}
                    </td>
                    <td>${item.jenis_kegiatan || '-'}</td>
                    <td>${item.nama_kegiatan || '-'}</td>
                </tr>
            `).join('');

            $tbody.html(rows);
            $count.text(data.length);
        }

        loadTable(url, filters, tableSelector, countSelector) {
            const fullUrl = url + '?' + $.param(filters);

            $.ajax({
                url: fullUrl,
                method: 'GET',
                success: (response) => {
                    if (response.success) {
                        this.renderTableLaporanRevisi(response.data, tableSelector, countSelector);
                    }
                },
                error: () => {
                    this.renderTableLaporanRevisi([], tableSelector, countSelector);
                }
            });
        }

        renderTableLaporanRevisi(data, tableSelector, countSelector) {
            const $tbody = $(`${tableSelector} tbody`);
            const $count = $(countSelector);

            if (!data || data.length === 0) {
                $tbody.html(`
                    <tr>
                        <td colspan="4" class="text-center text-muted">
                            Tidak ada laporan yang perlu revisi
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
                        <strong>${item.nama_desa || '-'}</strong><br>
                        <small>${item.nama_kecamatan || '-'}</small>
                    </td>
                    <td>
                        ${item.nama_kegiatan || '-'}<br>
                        <small class="text-muted">${item.nama_jenis || '-'}</small>
                    </td>
                    <td>
                        ${item.tanggal_submit ? new Date(item.tanggal_submit).toLocaleDateString('id-ID') : 
                          item.tanggal_target ? new Date(item.tanggal_target).toLocaleDateString('id-ID') : '-'}
                    </td>
                </tr>
            `).join('');

            $tbody.html(rows);
            $count.text(data.length);
        }

        // Helper untuk mendapatkan nama bulan
        getNamaBulan(bulanAngka) {
            const bulanList = {
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
            };
            return bulanList[bulanAngka] || bulanAngka;
        }
    }

    /* -------------------------------------------------
     *  INISIALISASI DASHBOARD KECAMATAN
     * ------------------------------------------------- */
    document.addEventListener('DOMContentLoaded', () => {
        window.dashboardKecamatan = new DashboardKecamatanHandler();
    });

    /* -------------------------------------------------
     *  RESET FILTER
     * ------------------------------------------------- */
    $('#reset_filter').on('click', function() {
        // Reset semua select
        $('#filter_tahun, #filter_bulan, #filter_jenis, #filter_desa')
            .val('')
            .selectpicker('refresh');

        // Reload data dashboard dan tabel
        if (window.dashboardKecamatan) {
            window.dashboardKecamatan.loadDashboardData();
            window.dashboardKecamatan.loadTableData();
        }
    });
</script>
