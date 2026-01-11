<script>
    $(document).ready(function () {
        // Inisialisasi selectpicker
        $('.selectpicker').selectpicker();

        // DataTable untuk daftar scoring desa
        const table = $('#example').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("administrator.monitoring.scoring.desa.list") }}',
                data: function (d) {
                    d.filter_tahun = $('#filter_tahun').val();
                    d.filter_periode = $('#filter_periode').val();
                    d.filter_kecamatan = $('#filter_kecamatan').val();
                }
            },
            columns: [
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false },
                { data: 'nama_desa', name: 'nama_desa' },
                { data: 'nama_kecamatan', name: 'nama_kecamatan' },
                { data: 'peringkat', name: 'peringkat', orderable: false },
                { data: 'total_skor', name: 'total_skor', orderable: false },
                { data: 'waktu_submit', name: 'waktu_submit', orderable: false },
                { data: 'kegiatan_terlapor', name: 'kegiatan_terlapor', orderable: false },
                { data: 'kegiatan_belum_terlapor', name: 'kegiatan_belum_terlapor', orderable: false },
                { data: 'jumlah_dokumen_wajib', name: 'jumlah_dokumen_wajib', orderable: false },
                { data: 'jumlah_dokumen_tambahan', name: 'jumlah_dokumen_tambahan', orderable: false }
            ],
            order: [[3, 'asc']], // Default order by peringkat
            language: {
                emptyTable: "Tidak ada data scoring desa yang tercatat saat ini.",
                zeroRecords: "Tidak ada data yang cocok dengan filter yang dipilih."
            }
        });

        // Listen for Ajax Complete to get User Ranking Metadata
        $('#example').on('xhr.dt', function (e, settings, json, xhr) {
            if (json && json.userRanking) {
                let rank = json.userRanking.rank;
                let total = json.userRanking.total_desa;
                
                let message = '';
                if (rank !== '-') {
                     message = `Desa Anda saat ini berada di peringkat ke-<strong>${rank}</strong> dari <strong>${total}</strong> desa berdasarkan filter yang dipilih.`;
                } else {
                     message = `Desa Anda belum masuk dalam daftar peringkat berdasarkan filter yang dipilih (Total: ${total} desa).`;
                }
                
                $('#userRankingText').html(message);
                $('#userRankingAlert').removeClass('d-none');
            } else {
                // Hide if no ranking info (e.g. admin or other roles)
                $('#userRankingAlert').addClass('d-none');
            }
        });

        // Event untuk filter otomatis saat dropdown berubah
        $('#filter_tahun, #filter_periode, #filter_kecamatan').on('change', function () {
            // Reload datatable
            table.ajax.reload();
        });

        // Perbaiki ID tombol reset - sesuaikan dengan blade file
        $('#btnResetFilter').click(function () {
            // Reset nilai filter
            $('#filter_tahun').val('').selectpicker('refresh');
            $('#filter_periode').val('').selectpicker('refresh');
            $('#filter_kecamatan').val('').selectpicker('refresh');

            // Reload datatable
            table.ajax.reload();
        });
    });

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

        // Fetch data
        let url = '{{ route("administrator.monitoring.scoring.desa.detail", ":id") }}';
        url = url.replace(':id', idDesa);

        $.ajax({
            url: url,
            type: 'GET',
            data: {
                tahun: tahun,
                periode: periode
            },
            success: function(response) {
                $('#modalDetailContent').html(response);
            },
            error: function(xhr) {
                $('#modalDetailContent').html(`
                    <div class="alert alert-danger">
                        Terjadi kesalahan saat memuat data. Silakan coba lagi.
                    </div>
                `);
            }
        });
    }
</script>