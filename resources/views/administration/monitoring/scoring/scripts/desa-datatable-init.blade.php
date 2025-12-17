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
                { data: 'jumlah_kegiatan', name: 'jumlah_kegiatan' },
                { data: 'jumlah_kegiatan_terlapor', name: 'jumlah_kegiatan_terlapor' },
                { data: 'persentase_kegiatan', name: 'persentase_kegiatan', orderable: false },
                { data: 'jumlah_kewajiban_dokumen', name: 'jumlah_kewajiban_dokumen' },
                { data: 'jumlah_dokumen_approve', name: 'jumlah_dokumen_approve' },
                { data: 'persentase_dokumen', name: 'persentase_dokumen', orderable: false },
                { data: 'total_skor', name: 'total_skor', orderable: false }
            ],
            order: [[3, 'asc']], // Default order by peringkat
            language: {
                emptyTable: "Tidak ada data scoring desa yang tercatat saat ini.",
                zeroRecords: "Tidak ada data yang cocok dengan filter yang dipilih."
            }
        });

        // Event untuk filter otomatis saat dropdown berubah
        $('#filter_tahun, #filter_periode, #filter_kecamatan').on('change', function () {

            // Ambil semua nilai filter
            let tahun = $('#filter_tahun').val();
            let periode = $('#filter_periode').val();
            let kecamatan = $('#filter_kecamatan').val();

            // Log semua value
            console.log("Filter Tahun:", tahun);
            console.log("Filter Periode:", periode);
            console.log("Filter Kecamatan:", kecamatan);

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
</script>