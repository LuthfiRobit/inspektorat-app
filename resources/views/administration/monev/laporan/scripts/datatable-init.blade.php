<script>
    const table = $('#example').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('administrator.monev.laporan-kegiatan.list') }}', // Sesuaikan dengan route laporan
            type: 'GET',
            data: function(d) {
                d.filter_status = $('#filter_status').val();
                d.filter_tahun = $('#filter_tahun').val();
                d.filter_bulan = $('#filter_bulan').val();
                d.filter_desa = $('#filter_desa').val();
                d.search = d.search.value; // search global
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
                data: 'nama_desa',
                name: 'd.nama_desa',
                orderable: false
            },
            {
                data: 'kode_kegiatan',
                name: 'k.kode_kegiatan',
                orderable: false
            },
            {
                data: 'nama_kegiatan',
                name: 'k.nama_kegiatan',
                orderable: false
            },
            {
                data: 'periode',
                orderable: false,
                searchable: false
            },
            {
                data: 'status',
                name: 'lk.status',
                orderable: false,
                searchable: false
            },
            {
                data: 'keterlambatan',
                orderable: false,
                searchable: false
            },
            {
                data: 'created_by_name',
                orderable: false,
                searchable: false
            },
            {
                data: 'created_at',
                orderable: false,
                searchable: false
            }
        ],
        language: {
            searchPlaceholder: "Cari (min 4 karakter)...",
            search: ''
        }
    });

    // Debounce pencarian global
    const optimizedSearch = _.debounce(query => {
        if (query.length >= 4 || query.length === 0) table.search(query).draw();
    }, 3000);

    $('#example_filter input').unbind().on('input', function() {
        optimizedSearch($(this).val());
    });

    // Reload otomatis saat filter berubah
    $('#filter_status, #filter_tahun, #filter_bulan, #filter_desa').on('change', function() {
        table.ajax.reload();
    });

    // Select all checkbox
    $('#selectAll').on('click', function() {
        $('.table-checkbox:not(:disabled)').prop('checked', this.checked);
    });
</script>
