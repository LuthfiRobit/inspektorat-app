<script>
    const table = $('#example').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('administrator.master.tahun-anggaran.list') }}',
            type: 'GET',
            data: function(d) {
                d.filter_status = $('#filter_status').val();
                d.tahun_from = $('#filter_tahun_from').val();
                d.tahun_to = $('#filter_tahun_to').val();
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
                orderable: false,
                searchable: true
            },
            {
                data: 'jumlah_jenis_kegiatan',
                orderable: false,
                searchable: false
            },
            {
                data: 'jumlah_kegiatan',
                orderable: false,
                searchable: false
            },
            {
                data: 'status',
                orderable: false,
                searchable: false
            }
        ],
        language: {
            searchPlaceholder: "Cari (min 4)...",
            search: ''
        }
    });

    // Debounce pencarian
    const performOptimizedSearch = _.debounce(query => {
        if (query.length >= 4 || query.length === 0) table.search(query).draw();
    }, 3000);

    $('#example_filter input').unbind().on('input', function() {
        performOptimizedSearch($(this).val());
    });

    // Semua filter trigger reload
    $('#filter_status, #filter_tahun_from, #filter_tahun_to').change(() => {
        table.ajax.reload();
        $('.selectpicker').selectpicker('refresh');
    });

    // Tombol reset filter
    $('#btnResetFilters').click(() => {
        $('#filter_status').val('').selectpicker('refresh');
        $('#filter_tahun_from').val('').selectpicker('refresh');
        $('#filter_tahun_to').val('').selectpicker('refresh');
        table.ajax.reload();
    });

    // Select All
    $('#selectAll').click(function() {
        $('.table-checkbox:not(:disabled)').prop('checked', this.checked);
    });
</script>
