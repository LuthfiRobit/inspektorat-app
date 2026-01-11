<script>
    $(document).ready(function () {
        // Inisialisasi selectpicker
        $('.selectpicker').selectpicker();

        // Inisialisasi DataTable
        const table = $('#example').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('administrator.monitoring.scoring.kecamatan.list') }}',
                type: 'GET',
                data: function (d) {
                    d.filter_tahun = $('#filter_tahun').val();
                    d.filter_periode = $('#filter_periode').val();
                    d.search = d.search.value;
                }
            },
            columns: [
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false },
                { data: 'nama_kecamatan', name: 'nama_kecamatan' },
                { data: 'jumlah_desa', name: 'jumlah_desa' },
                { data: 'peringkat', name: 'peringkat', orderable: false },
                { data: 'total_skor', name: 'total_skor', orderable: false },
                { data: 'rata_kegiatan', name: 'rata_kegiatan' },
                { data: 'kegiatan_terlapor', name: 'kegiatan_terlapor', orderable: false },
                { data: 'kegiatan_belum_terlapor', name: 'kegiatan_belum_terlapor', orderable: false }
            ],
            order: [[3, 'asc']], // Default order by peringkat
            language: {
                emptyTable: "Tidak ada informasi scoring kecamatan yang tercatat saat ini.",
                zeroRecords: "Tidak ada data yang cocok dengan filter yang dipilih.",
                processing: "Memproses data..."
            }
        });

        // Listen for Ajax Complete to get User Ranking Metadata
        $('#example').on('xhr.dt', function (e, settings, json, xhr) {
            if (json && json.userRanking) {
                let rank = json.userRanking.rank;
                let total = json.userRanking.total_kecamatan;

                let message = '';
                if (rank !== '-') {
                    message = `Kecamatan Anda saat ini berada di peringkat ke-<strong>${rank}</strong> dari <strong>${total}</strong> kecamatan berdasarkan performa desa-desa di wilayah Anda.`;
                } else {
                    message = `Kecamatan Anda belum masuk dalam daftar peringkat (Total: ${total} kecamatan).`;
                }

                $('#kecamatanRankingText').html(message);
                $('#kecamatanRankingAlert').removeClass('d-none');
            } else {
                // Hide if no ranking info (e.g. admin or other roles)
                $('#kecamatanRankingAlert').addClass('d-none');
            }
        });

        // Optimized Search
        const optimizedSearch = _.debounce(query => {
            if (query.length >= 3 || query.length === 0) {
                table.search(query).draw();
            }
        }, 500);

        $('#example_filter input').unbind().on('input', function () {
            optimizedSearch($(this).val());
        });

        // Event handler untuk filter dropdown
        $('#filter_tahun, #filter_periode').change(function () {
            table.ajax.reload();
        });

        // Tombol reset filter
        $('#btnResetFilter').click(function () {
            $('#filter_tahun').val('').selectpicker('refresh');
            $('#filter_periode').val('').selectpicker('refresh');
            table.ajax.reload();
        });

        // Tooltip Bootstrap
        $('[data-bs-toggle="tooltip"]').tooltip();
    });

    function showKecamatanDetail(idKecamatan) {
        // Show modal and loading state
        $('#modalDetailKecamatan').modal('show');
        $('#modalDetailKecamatanContent').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Memuat data...</p>
            </div>
        `);

        // Prepare filters
        let tahun = $('#filter_tahun').val();
        let periode = $('#filter_periode').val();

        let url = '{{ route("administrator.monitoring.scoring.kecamatan.detail", ":id") }}';
        url = url.replace(':id', idKecamatan);

        // Fetch data
        $.ajax({
            url: url,
            type: 'GET',
            data: {
                tahun: tahun,
                periode: periode
            },
            success: function (response) {
                $('#modalDetailKecamatanContent').html(response);
            },
            error: function (xhr) {
                $('#modalDetailKecamatanContent').html(`
                    <div class="alert alert-danger">
                        Terjadi kesalahan saat memuat data. Silakan coba lagi.
                    </div>
                `);
            }
        });
    }

    // Reuse Desa Detail Function (Copied from desa-datatable-init)
    // Needs to be available for the drill-down button
    function showDesaDetail(idDesa) {
        // Show modal and loading state
        $('#modalDetailScoring').modal('show');
        $('#modalDetailContent').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Memuat data...</p>
            </div>
        `);

        // Prepare filters
        let tahun = $('#filter_tahun').val();
        let periode = $('#filter_periode').val();

        let url = '{{ route("administrator.monitoring.scoring.desa.detail", ":id") }}';
        url = url.replace(':id', idDesa);

        // Fetch data
        $.ajax({
            url: url,
            type: 'GET',
            data: {
                tahun: tahun,
                periode: periode
            },
            success: function (response) {
                $('#modalDetailContent').html(response);
            },
            error: function (xhr) {
                $('#modalDetailContent').html(`
                    <div class="alert alert-danger">
                        Terjadi kesalahan saat memuat data. Silakan coba lagi.
                    </div>
                `);
            }
        });
    }
</script>