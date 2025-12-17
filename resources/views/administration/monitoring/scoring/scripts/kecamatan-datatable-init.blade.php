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
                { data: 'jumlah_kegiatan', name: 'jumlah_kegiatan' },
                { data: 'jumlah_kegiatan_terlapor', name: 'jumlah_kegiatan_terlapor' },
                { data: 'persentase_kegiatan', name: 'persentase_kegiatan', orderable: false },
                { data: 'jumlah_kewajiban_dokumen', name: 'jumlah_kewajiban_dokumen' },
                { data: 'jumlah_dokumen_approve', name: 'jumlah_dokumen_approve' },
                { data: 'persentase_dokumen', name: 'persentase_dokumen', orderable: false },
                { data: 'rata_kegiatan', name: 'rata_kegiatan' },
                { data: 'total_skor', name: 'total_skor', orderable: false }
            ],
            order: [[3, 'asc']], // Default order by peringkat
            language: {
                emptyTable: "Tidak ada informasi scoring kecamatan yang tercatat saat ini.",
                zeroRecords: "Tidak ada data yang cocok dengan filter yang dipilih.",
                processing: "Memproses data..."
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
</script>