<script>
    const table = $('#example').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('administrator.master.kegiatan.list') }}', // Sesuaikan dengan route yang digunakan
            type: 'GET',
            data: function(d) {
                d.filter_status = $('#filter_status').val();
                d.filter_tahun = $('#filter_tahun').val();
                d.filter_jenis = $('#filter_jenis').val();
                d.filter_bulan = $('#filter_bulan').val();
                d.search = d.search.value;
            }
        },
        columns: [{
                data: 'checkbox',
                orderable: false,
                searchable: false
            },
            {
                data: 'aksi',
                orderable: false,
                searchable: false
            },
            {
                data: 'tahun',
                name: 'tahun',
                orderable: false,
                searchable: true
            },

            {
                data: 'nama_bulan',
                name: 'nama_bulan',
                orderable: false,
                searchable: false
            },
            {
                data: 'kode_kegiatan',
                name: 'kode_kegiatan',
                orderable: true,
                searchable: true
            },
            {
                data: 'nama_kegiatan',
                name: 'nama_kegiatan',
                orderable: true,
                searchable: true
            },
            // {
            //     data: 'jenis_kegiatan',
            //     name: 'jenis_kegiatan',
            //     orderable: false,
            //     searchable: false
            // },
            {
                data: 'jumlah_pertanyaan',
                name: 'jumlah_pertanyaan',
                orderable: true,
                searchable: false
            },
            {
                data: 'status',
                name: 'status',
                orderable: false,
                searchable: false
            },
        ],
        language: {
            searchPlaceholder: "Cari (min 4 karakter)...",
            search: ''
        }
    });

    // Debounce search
    const optimizedSearch = _.debounce(query => {
        if (query.length >= 4 || query.length === 0) {
            table.search(query).draw();
        }
    }, 3000);

    $('#example_filter input').unbind().on('input', function() {
        optimizedSearch($(this).val());
    });

    // Reload saat filter berubah
    $('#filter_status, #filter_tahun, #filter_jenis, #filter_bulan').change(() => table.ajax.reload());

    // Select all checkbox
    $('#selectAll').on('click', function() {
        $('.table-checkbox:not(:disabled)').prop('checked', this.checked);
    });
</script>
